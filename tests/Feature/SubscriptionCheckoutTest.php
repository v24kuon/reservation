<?php

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires auth for checkout routes', function () {
    $plan = SubscriptionPlan::factory()->create();
    $this->get(route('subscription.checkout', $plan))->assertRedirect(route('login'));
    $this->get(route('subscription.success'))->assertRedirect(route('login'));
    $this->get(route('subscription.cancel'))->assertRedirect(route('login'));
});

it('shows success page after redirect back', function () {
    $user = adminUser();
    $this->actingAs($user)
        ->get(route('subscription.success', ['session_id' => 'cs_test_123']))
        ->assertOk()
        ->assertSee('サブスクリプション申込み完了');
});
