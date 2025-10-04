<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('disables apply button if user already subscribed to the plan', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create([
        'stripe_price_id' => 'price_abc',
        'is_active' => true,
    ]);

    UserSubscription::factory()
        ->for($user)
        ->for($plan, 'plan')
        ->active()
        ->create([
            'current_period_end' => now()->addDay(),
        ]);

    $this->actingAs($user)
        ->get(route('plans.index'))
        ->assertOk()
        ->assertSeeText('申し込み済み')
        ->assertDontSeeText('申し込む');
});

it('shows apply link when user not subscribed to the plan', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create([
        'stripe_price_id' => 'price_xyz',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('plans.index'))
        ->assertOk()
        ->assertSeeText('申し込む');
});
