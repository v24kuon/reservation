<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\CancelRequest;
use App\Http\Requests\Subscription\SwitchRequest;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $now = now();

        $active = $user->userSubscriptions()
            ->with('plan')
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->where('payment_status', UserSubscription::PAYMENT_STATUS_PAID)
            ->where(function ($q) use ($now) {
                $q->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>=', $now);
            })
            ->orderBy('current_period_end')
            ->paginate(config('pagination.profile_subscriptions', 10), ['*'], 'active_page');

        $past = $user->userSubscriptions()
            ->with('plan')
            ->where(function ($q) use ($now) {
                $q->where('status', UserSubscription::STATUS_CANCELED)
                    ->orWhere(function ($q2) use ($now) {
                        $q2->where('status', UserSubscription::STATUS_ACTIVE)
                            ->whereNotNull('current_period_end')
                            ->where('current_period_end', '<', $now);
                    });
            })
            ->orderByDesc('current_period_end')
            ->paginate(config('pagination.profile_subscriptions', 10), ['*'], 'past_page');

        // Cancel flags are now persisted on user_subscriptions via webhooks.

        return view('subscriptions.manage', [
            'active' => $active,
            'past' => $past,
        ]);
    }

    public function switch(SwitchRequest $request, SubscriptionService $service): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $from = SubscriptionPlan::query()->findOrFail((int) $data['from_plan_id']);
        $to = SubscriptionPlan::query()->findOrFail((int) $data['to_plan_id']);

        $session = $service->switchPlan($user, $from, $to);

        return redirect()->away($session->url);
    }

    public function cancel(CancelRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        /** @var UserSubscription $subscription */
        $subscription = $user->userSubscriptions()->with('plan')->findOrFail((int) $data['subscription_id']);

        // 既にキャンセル済みなら何もしない
        if ($subscription->status === UserSubscription::STATUS_CANCELED) {
            return back()->with('status', 'すでにキャンセル済みのサブスクリプションです');
        }

        // Stripe の購読を期末解約に設定
        $stripeId = (string) $subscription->stripe_subscription_id;
        if ($stripeId === '') {
            return back()->withErrors(['subscription' => 'Stripe購読IDが見つかりません。']);
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return back()->withErrors(['subscription' => 'StripeのAPIキーが未設定です。']);
        }

        try {
            $client = new \Stripe\StripeClient($secret);
            // 期末解約（即時キャンセルではない）
            $client->subscriptions->update($stripeId, [
                'cancel_at_period_end' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['subscription' => '解約処理に失敗しました。しばらくしてから再度お試しください。']);
        }

        // Webhook (customer.subscription.updated) で同期される想定
        return back()->with('status', '期末での解約を受け付けました。');
    }

    /**
     * Append Stripe cancel flags (cancel_at_period_end/cancel_at) to subscription models for display.
     */
    // appendStripeCancelFlags removed: no longer needed
}
