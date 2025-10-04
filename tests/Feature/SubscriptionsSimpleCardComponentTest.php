<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders next payment date or none when cancel_at_period_end is set', function (): void {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create(['lesson_count' => 3]);
    $sub = UserSubscription::factory()->for($user)->for($plan, 'plan')->active()->create([
        'current_period_end' => now()->addDays(10),
    ]);

    // Component render without cancel flags
    $html1 = view('components.subscription.simple-card', ['subscription' => $sub])->render();
    expect($html1)->toContain('次回支払日');
    expect($html1)->toContain(now()->addDays(10)->format('Y年m月d日'));

    // With cancel flags (simulate property enrichment)
    $sub->cancel_at_period_end = true;
    $sub->cancel_at = now()->addDays(10);

    $html2 = view('components.subscription.simple-card', ['subscription' => $sub])->render();
    expect($html2)->toContain('解約のため無し');
});
