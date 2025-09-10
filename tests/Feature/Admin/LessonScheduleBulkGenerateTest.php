<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Carbon\Carbon::setTestNow('2025-01-01 12:00:00');
});

afterEach(function () {
    \Carbon\Carbon::setTestNow();
});

it('validates generator payload', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->postJson(route('admin.lesson-schedules.bulk.generate'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['start_date', 'end_date', 'start_time', 'end_time', 'weekdays']);
});

it('generates weekly tuesdays within range', function () {
    $admin = adminUser();

    $payload = [
        'start_date' => '2025-01-01', // Wed
        'end_date' => '2025-01-31',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'interval_weeks' => 1,
        'weekdays' => [2], // Tue
    ];

    $res = $this->actingAs($admin)
        ->postJson(route('admin.lesson-schedules.bulk.generate'), $payload)
        ->assertOk()
        ->json();

    expect($res['count'])->toBeGreaterThan(0);
    // ensure ascending order by start_datetime
    $startTimes = array_column($res['items'], 'start_datetime');
    $sortedStartTimes = $startTimes;
    sort($sortedStartTimes, SORT_STRING);
    expect($startTimes)->toBe($sortedStartTimes);

    // ensure no duplicate start_datetime
    expect(count($startTimes))->toBe(count(array_unique($startTimes)));
    foreach ($res['items'] as $item) {
        expect($item['start_datetime'])->toContain('10:00:00');
        expect($item['end_datetime'])->toContain('11:00:00');
    }
});
