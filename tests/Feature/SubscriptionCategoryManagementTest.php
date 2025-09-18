<?php

use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedCategories(): array
{
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $a = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $b = LessonCategory::factory()->create(['parent_id' => $root->id]);

    return [$root, $a, $b];
}

it('scopeForCategory filters subscriptions by plan allowed_category_ids', function () {
    [$root, $a, $b] = seedCategories();
    $user = User::factory()->create();

    $planA = SubscriptionPlan::create([
        'name' => 'Plan A',
        'price' => 1000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$a->id],
        'stripe_product_id' => 'prod_aaa',
        'stripe_price_id' => 'price_aaa',
        'is_active' => true,
    ]);
    $planB = SubscriptionPlan::create([
        'name' => 'Plan B',
        'price' => 1500,
        'lesson_count' => 3,
        'allowed_category_ids' => [$b->id],
        'stripe_product_id' => 'prod_bbb',
        'stripe_price_id' => 'price_bbb',
        'is_active' => true,
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $planA->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 0,
    ]);
    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $planB->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 0,
    ]);

    $subForA = $user->userSubscriptions()->forCategory($a->id)->sole();
    expect($subForA->plan_id)->toBe($planA->id);
});

it('User::getActiveSubscriptionForCategory returns latest active within period', function () {
    [$root, $a] = seedCategories();
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'Plan',
        'price' => 1000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$a->id],
        'stripe_product_id' => 'prod_x',
        'stripe_price_id' => 'price_x',
        'is_active' => true,
    ]);

    // Out of period (future start)
    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->addDay(),
        'current_period_end' => now()->addDays(31),
        'current_month_used_count' => 0,
    ]);

    // In period (latest)
    $latest = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 0,
    ]);

    $found = $user->getActiveSubscriptionForCategory($a->id);
    expect($found?->id)->toBe($latest->id);
});
