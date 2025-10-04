<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

it('validates cancel request requires auth and subscription_id', function (): void {
    $this->post(route('subscriptions.cancel'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('subscriptions.cancel'), [])
        ->assertSessionHasErrors('subscription_id');
});

it('returns error when stripe secret not set on cancel', function (): void {
    Config::set('services.stripe.secret', null);

    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();
    $sub = UserSubscription::factory()->for($user)->for($plan, 'plan')->active()->create();

    $this->actingAs($user)
        ->post(route('subscriptions.cancel'), ['subscription_id' => $sub->id])
        ->assertSessionHasErrors('subscription');
});
