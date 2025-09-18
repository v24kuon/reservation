<?php

namespace App\Jobs;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ProcessCheckoutSessionCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $payload;

    /** @param array<string, mixed> $payload */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $session = (array) Arr::get($this->payload, 'data.object', []);
        $metadata = (array) ($session['metadata'] ?? []);

        $stripeSubscriptionId = (string) ($session['subscription'] ?? '');
        if ($stripeSubscriptionId === '') {
            return; // Not a subscription checkout
        }

        $userId = isset($metadata['app_user_id'])
            ? (int) $metadata['app_user_id']
            : (is_numeric($session['client_reference_id'] ?? null) ? (int) $session['client_reference_id'] : 0);
        $planId = (int) ($metadata['app_plan_id'] ?? 0);
        if ($userId <= 0 || $planId <= 0) {
            return; // Missing required identifiers
        }

        /** @var User|null $user */
        $user = User::query()->find($userId);
        /** @var SubscriptionPlan|null $plan */
        $plan = SubscriptionPlan::query()->find($planId);
        if (! $user || ! $plan) {
            return;
        }

        // Fetch subscription from Stripe for accurate period bounds when possible
        $periodStart = CarbonImmutable::now();
        $periodEnd = CarbonImmutable::now()->addMonth();
        $status = 'active';

        try {
            $secret = (string) config('services.stripe.secret');
            if ($secret !== '') {
                $client = new StripeClient(['api_key' => $secret]);
                $sub = $client->subscriptions->retrieve($stripeSubscriptionId, []);
                $status = (string) ($sub->status ?? $status);

                $startTs = (int) ($sub->current_period_start ?? 0);
                $endTs = (int) ($sub->current_period_end ?? 0);
                if ($startTs > 0) {
                    $periodStart = CarbonImmutable::createFromTimestampUTC($startTs);
                }
                if ($endTs > 0) {
                    $periodEnd = CarbonImmutable::createFromTimestampUTC($endTs);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe subscription fetch failed; using fallback period', [
                'subscription' => $stripeSubscriptionId,
                'message' => $e->getMessage(),
            ]);
        }

        // Compute initial remaining lessons
        $remainingHint = (int) ($metadata['remaining_lessons_hint'] ?? 0);
        $isSwitch = isset($metadata['switch_to_app_plan_id']) || isset($metadata['switch_from_app_plan_id']);
        $initialRemaining = $isSwitch
            ? max(0, (int) $plan->lesson_count + max(0, $remainingHint))
            : (int) $plan->lesson_count;

        // Upsert by Stripe subscription id (unique)
        UserSubscription::query()->updateOrCreate(
            [
                'stripe_subscription_id' => $stripeSubscriptionId,
            ],
            [
                'user_id' => $user->getKey(),
                'plan_id' => $plan->getKey(),
                'status' => $status,
                // Session が 'paid' のときのみ即時反映。そうでなければ invoice.* で最終確定
                'payment_status' => (($session['payment_status'] ?? null) === 'paid') ? 'paid' : ($this->payload['_noop_payment_status'] ?? null),
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'current_month_used_count' => 0,
                'remaining_lessons' => $initialRemaining,
            ]
        );
    }
}
