<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents duplicate user-subscription pairs by unique constraint', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();
    $stripeId = 'sub_unique_1';

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => $stripeId,
        'status' => UserSubscription::STATUS_ACTIVE,
        'payment_status' => UserSubscription::PAYMENT_STATUS_PAID,
        'current_period_start' => now(),
        'current_period_end' => now()->addDay(),
    ]);

    expect(fn () => UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => $stripeId,
        'status' => UserSubscription::STATUS_ACTIVE,
        'payment_status' => UserSubscription::PAYMENT_STATUS_PAID,
        'current_period_start' => now(),
        'current_period_end' => now()->addDay(),
    ]))->toThrow(\Illuminate\Database\QueryException::class);

    expect(UserSubscription::query()
        ->where('user_id', $user->id)
        ->where('stripe_subscription_id', $stripeId)
        ->count()
    )->toBe(1);
});
