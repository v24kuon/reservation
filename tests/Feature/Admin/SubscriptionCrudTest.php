<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\patch;
use function Pest\Laravel\delete;

it('requires admin to access subscriptions index', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    actingAs($user);

    get(route('admin.subscriptions.index'))->assertForbidden();
});

it('shows subscriptions index for admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    actingAs($admin);

    get(route('admin.subscriptions.index'))->assertOk();
});

it('can create subscription as admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $plan = SubscriptionPlan::factory()->create();
    actingAs($admin);

    $payload = [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_12345',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDay()->format('Y-m-d H:i:s'),
        'current_period_end' => now()->addMonth()->format('Y-m-d H:i:s'),
        'remaining_lessons' => 10,
        'current_month_used_count' => 0,
    ];

    post(route('admin.subscriptions.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(UserSubscription::query()->where('stripe_subscription_id', 'sub_test_12345')->exists())->toBeTrue();
});

it('can update subscription as admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $subscription = UserSubscription::factory()->create();
    actingAs($admin);

    $payload = [
        'status' => 'active',
        'payment_status' => 'paid',
        'current_period_start' => now()->subDays(2)->format('Y-m-d H:i:s'),
        'current_period_end' => now()->addDays(27)->format('Y-m-d H:i:s'),
        'remaining_lessons' => 8,
        'current_month_used_count' => 2,
    ];

    patch(route('admin.subscriptions.update', $subscription), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $subscription->refresh();
    expect($subscription->remaining_lessons)->toBe(8)
        ->and($subscription->current_month_used_count)->toBe(2);
});

it('can delete canceled subscription as admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $subscription = UserSubscription::factory()->create(['status' => 'canceled']);
    actingAs($admin);

    delete(route('admin.subscriptions.destroy', $subscription))
        ->assertRedirect();

    expect(UserSubscription::query()->whereKey($subscription->getKey())->exists())->toBeFalse();
});

it('rejects delete for non-canceled subscription', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $subscription = UserSubscription::factory()->create(['status' => 'active']);
    actingAs($admin);

    delete(route('admin.subscriptions.destroy', $subscription))
        ->assertSessionHasErrors();
});
