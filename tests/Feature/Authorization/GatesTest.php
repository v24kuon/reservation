<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('allows admin to access admin and instructor areas and manage plans', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin);

    expect(Gate::allows('access-admin'))->toBeTrue()
        ->and(Gate::allows('access-instructor'))->toBeTrue()
        ->and(Gate::allows('manage-subscription-plans'))->toBeTrue();
});

it('allows instructor to access instructor but not admin or manage plans', function () {
    $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
    $this->actingAs($instructor);

    expect(Gate::allows('access-instructor'))->toBeTrue()
        ->and(Gate::denies('access-admin'))->toBeTrue()
        ->and(Gate::denies('manage-subscription-plans'))->toBeTrue();
});

it('denies normal user for admin and instructor and manage plans', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $this->actingAs($user);

    expect(Gate::denies('access-admin'))->toBeTrue()
        ->and(Gate::denies('access-instructor'))->toBeTrue()
        ->and(Gate::denies('manage-subscription-plans'))->toBeTrue();
});
