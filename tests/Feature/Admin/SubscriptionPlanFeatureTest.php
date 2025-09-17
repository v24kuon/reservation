<?php

use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCategories(): array
{
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $childA = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $childB = LessonCategory::factory()->create(['parent_id' => $root->id]);

    return [$root, $childA, $childB];
}

it('admin can view subscription plan index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.subscription-plans.index'))
        ->assertOk();
});

it('store validation fails for invalid stripe ids', function () {
    config(['services.stripe.secret' => 'sk_test_xxx']);
    [$root, $a, $b] = makeCategories();

    $admin = adminUser();
    $payload = [
        'name' => 'Basic Plan',
        'price' => 1000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$root->id],
        'stripe_product_id' => 'invalid_prod',
        'stripe_price_id' => 'invalid_price',
        'is_active' => true,
    ];

    $this->actingAs($admin)
        ->post(route('admin.subscription-plans.store'), $payload)
        ->assertSessionHasErrors(['stripe_product_id', 'stripe_price_id']);
});

it('cannot delete plan that has active subscribers', function () {
    $admin = adminUser();
    [$root, $a] = makeCategories();

    $plan = SubscriptionPlan::create([
        'name' => 'Protected',
        'price' => 2000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$a->id],
        'stripe_product_id' => 'prod_123',
        'stripe_price_id' => 'price_123',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_'.uniqid(),
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay(),
        'current_period_end' => now()->addMonth(),
        'current_month_used_count' => 0,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.subscription-plans.destroy', $plan))
        ->assertRedirect()
        ->assertSessionHasErrors(['subscription_plan']);
});

it('price lookup rejects invalid id by validation', function () {
    config(['services.stripe.secret' => 'sk_test_xxx']);
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.subscription-plans.price-lookup'), ['price_id' => 'bad'])
        ->assertSessionHasErrors(['price_id']);
});
