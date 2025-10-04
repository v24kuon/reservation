<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\StripeClient;

class SubscriptionService
{
    public function __construct(private readonly StripeClient $stripe) {}

    /**
     * Initiate a plan switch by creating a Stripe Checkout Session.
     * - Validates same-category rule
     * - Calculates remaining lessons (old_plan.lesson_count - current_month_used_count)
     * - Adds remaining lessons metadata so webhook can apply new_plan.lesson_count + remaining
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function switchPlan(User $user, SubscriptionPlan $from, SubscriptionPlan $to): StripeCheckoutSession
    {
        // Validate same-category-only rule: intersection of allowed categories must be non-empty
        $fromCategories = array_map('intval', Arr::wrap($from->allowed_category_ids));
        $toCategories = array_map('intval', Arr::wrap($to->allowed_category_ids));
        $shared = array_values(array_intersect($fromCategories, $toCategories));
        if (empty($shared)) {
            throw ValidationException::withMessages([
                'subscription' => trans('subscription.errors.plan_switch_invalid'),
            ]);
        }

        // Find the user's active paid subscription for the FROM plan
        /** @var UserSubscription|null $current */
        $current = $user->userSubscriptions()
            ->where('plan_id', $from->getKey())
            ->active()
            ->paid()
            ->orderByDesc('current_period_start')
            ->orderByDesc('id')
            ->first();

        if (! $current) {
            throw ValidationException::withMessages([
                'subscription' => '切替元の有効なサブスクリプションが見つかりません。',
            ]);
        }

        // Guard: prevent switching to a plan the user already has active and paid (not expired)
        $alreadyHasTarget = $user->userSubscriptions()
            ->where('plan_id', $to->getKey())
            ->active()
            ->paid()
            ->notExpired()
            ->exists();
        if ($alreadyHasTarget) {
            throw ValidationException::withMessages([
                'subscription' => trans('subscription.errors.switch_target_exists'),
            ]);
        }

        // Calculate remaining lessons (hint only). Final value is recalculated at webhook time.
        $remaining = max(0, (int) $from->lesson_count - (int) $current->current_month_used_count);

        // Guard: ensure target price exists
        if (empty($to->stripe_price_id)) {
            throw ValidationException::withMessages([
                'plan' => '切替先プランの価格設定が不正です。',
            ]);
        }

        // Build URLs
        $successUrl = route('subscription.success', ['session_id' => '{CHECKOUT_SESSION_ID}'], true);
        $cancelUrl = route('subscription.cancel', [], true);

        // Idempotency key (minute-granularity to mitigate double-submit)
        $idempotencyKey = sprintf(
            'switch:%d:%d:%d:%s',
            $user->getKey(),
            $from->getKey(),
            $to->getKey(),
            now()->startOfMinute()->timestamp
        );

        // Create Checkout Session for the TO plan with remaining lessons metadata
        try {
            // Ensure Stripe customer exists
            $user->createOrGetStripeCustomer();

            $session = $this->stripe->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $user->stripe_id,
                'line_items' => [
                    [
                        'price' => $to->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'locale' => 'ja',
                'client_reference_id' => (string) $user->getKey(),
                'metadata' => [
                    'switch_from_app_plan_id' => (string) $from->getKey(),
                    'switch_to_app_plan_id' => (string) $to->getKey(),
                    // Hint only: webhook recalculates using latest usage at completion time
                    'remaining_lessons_hint' => (string) $remaining,
                    'remaining_calculated_at' => now()->toIso8601String(),
                    // Optional: assist webhook with category context
                    'switch_shared_category_id' => (string) Arr::first($shared),
                ],
            ], ['idempotency_key' => $idempotencyKey]);
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'subscription' => 'プラン切替用の決済セッション作成に失敗しました。時間をおいて再度お試しください。',
            ]);
        }

        return $session;
    }
}
