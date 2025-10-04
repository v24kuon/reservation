<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;
use Stripe\Webhook as StripeWebhook;

class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');
        $secret = (string) (config('services.stripe.webhook.secret') ?? '');
        if ($secret === '') {
            Log::error('Stripe webhook secret not configured');

            return response('server not configured', 500);
        }

        try {
            $event = StripeWebhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            report($e);

            return response('invalid signature', 400);
        }

        $type = (string) ($event->type ?? '');
        $eventId = (string) ($event->id ?? '');
        if ($eventId === '') {
            return response('ok', 200);
        }

        // Idempotency guard using DB unique event store
        try {
            DB::table('stripe_webhook_events')->insert([
                'event_id' => $eventId,
                'type' => $type,
                'payload' => $payload,
                'received_at' => now(),
            ]);
        } catch (QueryException $e) {
            $state = $e->errorInfo[0] ?? $e->getCode();
            if (in_array($state, ['23000', '23505'], true)) {
                // Duplicate event: continue only if not processed yet; otherwise ack
                $existing = DB::table('stripe_webhook_events')->where('event_id', $eventId)->first();
                if (! $existing || $existing->processed_at !== null) {
                    return response('ok', 200);
                }
                // fall-through to processing when processed_at is null
            } else {
                report($e);

                return response('ok', 200);
            }
        } catch (\Throwable $e) {
            report($e);

            return response('ok', 200);
        }

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    $subscriptionId = (string) ($event->data->object->subscription ?? '');
                    $customerId = (string) ($event->data->object->customer ?? '');
                    if ($subscriptionId !== '' && $customerId !== '') {
                        $this->syncSubscriptionById($subscriptionId, $customerId);
                    }
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $subscriptionId = (string) ($event->data->object->id ?? '');
                    $customerId = (string) ($event->data->object->customer ?? '');
                    if ($subscriptionId !== '' && $customerId !== '') {
                        $this->syncSubscriptionById($subscriptionId, $customerId);
                    }
                    break;

                case 'invoice.payment_succeeded':
                    $subscriptionId = (string) ($event->data->object->subscription ?? '');
                    $customerId = (string) ($event->data->object->customer ?? '');
                    if ($subscriptionId !== '' && $customerId !== '') {
                        $this->syncSubscriptionById($subscriptionId, $customerId, forcePaid: true);
                    }
                    break;

                default:
                    // Ignore other events
                    break;
            }
        } catch (\Throwable $e) {
            report($e);

            return response('failed to process', 500);
        }

        // mark processed on success only
        DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->update(['processed_at' => now()]);

        return response('ok', 200);
    }

    private function getStripeClient(): StripeClient
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        return new StripeClient(['api_key' => $secret]);
    }

    private function syncSubscriptionById(string $stripeSubscriptionId, string $stripeCustomerId, bool $forcePaid = false): void
    {
        $client = $this->getStripeClient();

        $subscription = $client->subscriptions->retrieve($stripeSubscriptionId, [
            'expand' => ['items.data.price.product'],
        ]);

        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            Log::warning('Webhook subscription sync: user not found for customer', [
                'customer' => $stripeCustomerId,
                'subscription' => $stripeSubscriptionId,
            ]);

            return;
        }

        $priceId = null;
        if (isset($subscription->items) && isset($subscription->items->data) && ! empty($subscription->items->data)) {
            $first = $subscription->items->data[0] ?? null;
            if ($first && isset($first->price) && isset($first->price->id)) {
                $priceId = $first->price->id;
            }
        }
        $plan = $priceId ? SubscriptionPlan::query()->where('stripe_price_id', $priceId)->first() : null;

        $status = $this->mapStripeStatusToLocal((string) ($subscription->status ?? ''));
        $paymentStatus = $forcePaid || in_array($status, [UserSubscription::STATUS_ACTIVE, UserSubscription::STATUS_TRIALING], true)
            ? UserSubscription::PAYMENT_STATUS_PAID
            : UserSubscription::PAYMENT_STATUS_UNPAID;

        $periodStart = isset($subscription->current_period_start)
            ? \Carbon\Carbon::createFromTimestamp((int) $subscription->current_period_start)
            : null;
        $periodEnd = isset($subscription->current_period_end)
            ? \Carbon\Carbon::createFromTimestamp((int) $subscription->current_period_end)
            : null;

        // Upsert using unique key on stripe_subscription_id to avoid UNIQUE violations
        $attributes = [
            'user_id' => $user->id,
            'stripe_subscription_id' => (string) $subscription->id,
        ];

        $values = [
            'status' => $status,
            'payment_status' => $paymentStatus,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            // Persist cancel flags to avoid N+1 fetches on UI
            'cancel_at_period_end' => (bool) ($subscription->cancel_at_period_end ?? false),
            'cancel_at' => isset($subscription->cancel_at)
                ? \Carbon\Carbon::createFromTimestamp((int) $subscription->cancel_at)
                : null,
        ];

        if ($plan) {
            $values['plan_id'] = $plan->id;
        }

        $row = array_merge($attributes, $values, [
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        // align with DB unique constraint on stripe_subscription_id
        UserSubscription::query()->upsert(
            [$row],
            ['stripe_subscription_id'],
            array_merge(array_keys($values), ['updated_at'])
        );
    }

    private function mapStripeStatusToLocal(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'active' => UserSubscription::STATUS_ACTIVE,
            'trialing' => UserSubscription::STATUS_TRIALING,
            'past_due' => UserSubscription::STATUS_PAST_DUE,
            'canceled' => UserSubscription::STATUS_CANCELED,
            'incomplete' => UserSubscription::STATUS_INCOMPLETE,
            'incomplete_expired' => UserSubscription::STATUS_INCOMPLETE_EXPIRED,
            'unpaid' => UserSubscription::STATUS_UNPAID,
            default => UserSubscription::STATUS_UNKNOWN,
        };
    }
}
