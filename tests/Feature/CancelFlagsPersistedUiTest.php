<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reflects cancel flags from DB on manage page and simple card', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();
    $sub = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_cancel_flag',
        'status' => UserSubscription::STATUS_ACTIVE,
        'payment_status' => UserSubscription::PAYMENT_STATUS_PAID,
        'current_period_start' => now(),
        'current_period_end' => now()->addDays(7),
        'cancel_at_period_end' => true,
        'cancel_at' => now()->addDays(7),
    ]);

    // manage page shows 解約(有効期限:...)
    $this->actingAs($user)
        ->get(route('subscriptions.manage'))
        ->assertOk()
        ->assertSeeText('解約(有効期限:'.now()->addDays(7)->format('Y/m/d').')')
        ->assertSeeText('解約のため無し');

    // simple card shows 解約のため無し
    $html = view('components.subscription.simple-card', ['subscription' => $sub])->render();
    expect($html)->toContain('解約のため無し');
});
