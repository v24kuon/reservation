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
     */
    public function switchPlan(User $user, SubscriptionPlan $from, SubscriptionPlan $to): StripeCheckoutSession
    {
        // Validate same-category-only rule: intersection of allowed categories must be non-empty
        $fromCategories = $from->allowed_category_ids ?? [];
        $toCategories = $to->allowed_category_ids ?? [];
        $shared = array_values(array_intersect($fromCategories, $toCategories));
        if (empty($shared)) {
            throw ValidationException::withMessages([
                'plan' => '同じカテゴリー内のプランのみ切り替えできます。',
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

        // Calculate remaining lessons for transfer
        $remaining = max(0, (int) $from->lesson_count - (int) $current->current_month_used_count);

        // Ensure Stripe customer exists
        $user->createOrGetStripeCustomer();

        // Build URLs
        $successUrl = route('subscription.success', ['session_id' => '{CHECKOUT_SESSION_ID}'], true);
        $cancelUrl = route('subscription.cancel', [], true);

        // Create Checkout Session for the TO plan with remaining lessons metadata
        try {
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
                    'remaining_lessons' => (string) $remaining,
                    // Optional: assist webhook with category context
                    'switch_shared_category_id' => (string) Arr::first($shared),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'subscription' => 'プラン切替用の決済セッション作成に失敗しました。時間をおいて再度お試しください。',
            ]);
        }

        return $session;
    }
}
