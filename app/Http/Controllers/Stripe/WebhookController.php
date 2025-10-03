<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
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
        } catch (\Throwable $e) {
            // Unique violation or other error -> if duplicate, skip; else log and skip
            if (str_contains(strtolower((string) $e->getMessage()), 'unique')) {
                return response('ok', 200);
            }
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
                    $subscriptionId = (string) ($event->data->object->id ?? '');
                    $customerId = (string) ($event->data->object->customer ?? '');
                    if ($subscriptionId !== '' && $customerId !== '') {
                        $this->syncSubscriptionById($subscriptionId, $customerId);
                    }
                    break;

                case 'invoice.paid':
                case 'invoice.payment_succeeded':
                    $subscriptionId = (string) ($event->data->object->subscription ?? '');
                    $customerId = (string) ($event->data->object->customer ?? '');
                    if ($subscriptionId !== '' && $customerId !== '') {
                        $this->syncSubscriptionById($subscriptionId, $customerId, forcePaid: true);
                    }
                    break;

                case 'invoice_payment.paid':
                    // Newer API: object is invoice_payment; fetch invoice to resolve customer/subscription
                    $invoiceId = (string) ($event->data->object->invoice ?? '');
                    if ($invoiceId !== '') {
                        $this->syncSubscriptionByInvoiceId($invoiceId);
                    }
                    break;

                default:
                    // Ignore other events
                    break;
            }
        } catch (\Throwable $e) {
            report($e);
            // still 200 per Stripe recommendations (avoid retries on persistent app errors)
        }

        // mark processed
        DB::table('stripe_webhook_events')->where('event_id', $eventId)->update(['processed_at' => now()]);

        return response('ok', 200);
    }

    private function syncSubscriptionById(string $stripeSubscriptionId, string $stripeCustomerId, bool $forcePaid = false): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient(['api_key' => $secret]);

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
            'stripe_subscription_id' => (string) $subscription->id,
        ];

        $values = [
            'user_id' => $user->id,
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

        UserSubscription::query()->updateOrCreate($attributes, $values);
    }

    private function syncSubscriptionByInvoiceId(string $invoiceId): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient(['api_key' => $secret]);
        try {
            $invoice = $client->invoices->retrieve($invoiceId, []);
        } catch (\Throwable $e) {
            report($e);

            return; // skip when invoice retrieval fails
        }
        $subscriptionId = (string) ($invoice->subscription ?? '');
        $customerId = (string) ($invoice->customer ?? '');
        if ($subscriptionId !== '' && $customerId !== '') {
            $this->syncSubscriptionById($subscriptionId, $customerId, forcePaid: true);
        }
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
            'paused' => UserSubscription::STATUS_PAUSED,
            default => UserSubscription::STATUS_UNKNOWN,
        };
    }
}
