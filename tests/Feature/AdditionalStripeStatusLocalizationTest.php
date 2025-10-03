<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows Japanese labels for additional statuses', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();

    $statuses = [
        UserSubscription::STATUS_INCOMPLETE => '未完了',
        UserSubscription::STATUS_INCOMPLETE_EXPIRED => '未完了（期限切れ）',
        UserSubscription::STATUS_UNPAID => '未払い',
        UserSubscription::STATUS_PAUSED => '一時停止',
        UserSubscription::STATUS_UNKNOWN => '不明',
    ];

    foreach ($statuses as $status => $label) {
        $sub = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => 'sub_'.uniqid(),
            'status' => $status,
            'payment_status' => UserSubscription::PAYMENT_STATUS_PAID,
            'current_period_start' => now(),
            'current_period_end' => now()->addDay(),
        ]);

        expect($sub->status_label)->toBe($label);
    }
});
