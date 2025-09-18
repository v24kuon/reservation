<?php

use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects switching across different categories', function () {
    $user = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $a = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $b = LessonCategory::factory()->create(['parent_id' => $root->id]);

    $from = SubscriptionPlan::create([
        'name' => 'From',
        'price' => 1000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$a->id],
        'stripe_product_id' => 'prod_from',
        'stripe_price_id' => 'price_from',
        'is_active' => true,
    ]);
    $to = SubscriptionPlan::create([
        'name' => 'To',
        'price' => 1500,
        'lesson_count' => 3,
        'allowed_category_ids' => [$b->id],
        'stripe_product_id' => 'prod_to',
        'stripe_price_id' => 'price_to',
        'is_active' => true,
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $from->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 0,
    ]);

    $service = app(SubscriptionService::class);

    try {
        $service->switchPlan($user, $from, $to);
        $this->fail('ValidationException expected');
    } catch (\Illuminate\Validation\ValidationException $e) {
        expect($e->errors())->toHaveKey('subscription');
        expect($e->errors()['subscription'][0] ?? null)
            ->toBe(__('subscription.errors.plan_switch_invalid'));
    }
});
