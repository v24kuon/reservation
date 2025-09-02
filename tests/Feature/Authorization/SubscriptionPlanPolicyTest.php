<?php

use App\Models\SubscriptionPlan;
use App\Models\User;

it('allows admin to manage subscription plans', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin);

    expect($admin->can('create', SubscriptionPlan::class))->toBeTrue();
});

it('denies user to manage subscription plans', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $this->actingAs($user);

    expect($user->can('create', SubscriptionPlan::class))->toBeFalse();
});
