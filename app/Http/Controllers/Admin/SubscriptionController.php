<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subscriptions\IndexSubscriptionsRequest;
use App\Http\Requests\Admin\Subscriptions\StoreSubscriptionRequest;
use App\Http\Requests\Admin\Subscriptions\UpdateSubscriptionRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function index(IndexSubscriptionsRequest $request): View
    {
        $filters = $request->validated();

        $query = UserSubscription::query()
            ->with(['user', 'plan']);

        if (! empty($filters['user'])) {
            $term = $filters['user'];
            $query->whereHas('user', function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['plan_id'])) {
            $query->where('plan_id', (int) $filters['plan_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['period_from'])) {
            $query->whereDate('current_period_start', '>=', $filters['period_from']);
        }

        if (! empty($filters['period_to'])) {
            $query->whereDate('current_period_end', '<=', $filters['period_to']);
        }

        $subscriptions = $query->orderByDesc('id')->paginate(50)->withQueryString();

        $plans = SubscriptionPlan::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.subscriptions.index', compact('subscriptions', 'filters', 'plans'));
    }

    public function create(): View
    {
        $plans = SubscriptionPlan::query()->orderBy('name')->get(['id', 'name']);
        return view('admin.subscriptions.create', compact('plans'));
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $subscription = new UserSubscription();
        $subscription->user_id = (int) $data['user_id'];
        $subscription->plan_id = (int) $data['plan_id'];
        $subscription->stripe_subscription_id = $data['stripe_subscription_id'];
        $subscription->status = $data['status'];
        $subscription->payment_status = $data['payment_status'];
        $subscription->failure_reason = $data['failure_reason'] ?? null;
        $subscription->current_period_start = $data['current_period_start'];
        $subscription->current_period_end = $data['current_period_end'];
        $subscription->current_month_used_count = (int) ($data['current_month_used_count'] ?? 0);
        $subscription->remaining_lessons = (int) ($data['remaining_lessons'] ?? 0);
        $subscription->save();

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('status', 'サブスクリプションを作成しました');
    }

    public function show(UserSubscription $subscription): View
    {
        $subscription->load(['user', 'plan']);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function edit(UserSubscription $subscription): View
    {
        $plans = SubscriptionPlan::query()->orderBy('name')->get(['id', 'name']);
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(UpdateSubscriptionRequest $request, UserSubscription $subscription): RedirectResponse
    {
        $data = $request->validated();

        // Do not allow changing user_id or plan_id via this screen
        $subscription->status = $data['status'];
        $subscription->payment_status = $data['payment_status'];
        $subscription->failure_reason = $data['failure_reason'] ?? null;
        $subscription->current_period_start = $data['current_period_start'];
        $subscription->current_period_end = $data['current_period_end'];
        if (array_key_exists('remaining_lessons', $data)) {
            $subscription->remaining_lessons = (int) $data['remaining_lessons'];
        }
        if (array_key_exists('current_month_used_count', $data)) {
            $subscription->current_month_used_count = (int) $data['current_month_used_count'];
        }
        $subscription->save();

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('status', 'サブスクリプションを更新しました');
    }

    public function destroy(UserSubscription $subscription): RedirectResponse
    {
        // Allow delete only if canceled to prevent accidental loss
        if ($subscription->status !== UserSubscription::STATUS_CANCELED) {
            return back()->withErrors(['subscription' => 'キャンセル済みのみ削除可能です']);
        }

        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('status', 'サブスクリプションを削除しました');
    }
}
