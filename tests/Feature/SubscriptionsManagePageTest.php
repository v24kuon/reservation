<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows active and past subscriptions in two columns with Japanese status', function (): void {
    $user = User::factory()->create();
    $activePlan = SubscriptionPlan::factory()->create();
    $pastPlan = SubscriptionPlan::factory()->create();

    // Active (paid, period in future)
    UserSubscription::factory()
        ->for($user)
        ->for($activePlan, 'plan')
        ->active()
        ->create([
            'current_period_start' => now()->subDay(),
            'current_period_end' => now()->addDays(10),
        ]);

    // Past (canceled)
    UserSubscription::factory()
        ->for($user)
        ->for($pastPlan, 'plan')
        ->create([
            'status' => UserSubscription::STATUS_CANCELED,
            'payment_status' => UserSubscription::PAYMENT_STATUS_PAID,
            'current_period_start' => now()->subMonths(2),
            'current_period_end' => now()->subMonth(),
        ]);

    $this->actingAs($user)
        ->get(route('subscriptions.manage'))
        ->assertOk()
        ->assertSeeText('契約中のプラン')
        ->assertSeeText('過去のプラン')
        ->assertSeeText('ステータス: 有効')
        ->assertSeeText('ステータス: キャンセル済み');
});

it('disables cancel button when cancel_at_period_end is true (UI hint)', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();

    $sub = UserSubscription::factory()->for($user)->for($plan, 'plan')->active()->create([
        'current_period_end' => now()->addDays(5),
    ]);
    // Enrich flags at runtime (not persisted)
    $sub->cancel_at_period_end = true;
    $sub->cancel_at = now()->addDays(5);

    // Render view directly to test blade logic with injected flags
    $active = new \Illuminate\Pagination\LengthAwarePaginator(collect([$sub]), 1, 10);
    $past = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 10);

    $html = view('subscriptions.manage', compact('active', 'past'))
        ->render();

    expect($html)->toContain('解約（期末）');
    expect($html)->toContain('解約(有効期限:'.now()->addDays(5)->format('Y/m/d').')');
});
