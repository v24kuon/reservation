<?php

use App\Models\Reservation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders reservation history section before current subscriptions on mypage', function (): void {
    $user = User::factory()->create();

    // Seed minimal data for sections to render
    $plan = SubscriptionPlan::factory()->create();
    UserSubscription::factory()->for($user)->for($plan, 'plan')->active()->create();

    // create one reservation through factory relations
    Reservation::factory()->create(['user_id' => $user->id]);

    $html = $this->actingAs($user)->get(route('profile.index'))
        ->assertOk()
        ->getContent();

    $posHistory = mb_strpos($html, '予約履歴');
    $posSubs = mb_strpos($html, '契約中のプラン');

    expect($posHistory)->toBeLessThan($posSubs);
});
