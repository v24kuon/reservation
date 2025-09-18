<?php

use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('computes remaining lessons via accessor and methods', function () {
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $user = User::factory()->create();

    $plan = SubscriptionPlan::create([
        'name' => 'Count Plan',
        'price' => 2000,
        'lesson_count' => 4,
        'allowed_category_ids' => [$category->id],
        'stripe_product_id' => 'prod_cnt',
        'stripe_price_id' => 'price_cnt',
        'is_active' => true,
    ]);

    $sub = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 1,
    ]);

    expect($sub->getTotalAvailableLessons())->toBe(4)
        ->and($sub->remaining_lessons)->toBe(3)
        ->and($sub->getRemainingLessons())->toBe(3)
        ->and($sub->hasRemainingLessons())->toBeTrue();
});

it('denies when remaining lessons reach zero', function () {
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'Count Plan',
        'price' => 2000,
        'lesson_count' => 4,
        'allowed_category_ids' => [$category->id],
        'stripe_product_id' => 'prod_cnt',
        'stripe_price_id' => 'price_cnt',
        'is_active' => true,
    ]);
    $sub = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 4,
    ]);
    expect($sub->getRemainingLessons())->toBe(0)
        ->and($sub->hasRemainingLessons())->toBeFalse();
});
