<?php

use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Carbon\Carbon::setTestNow('2025-01-01 12:00:00');
});

afterEach(function () {
    \Carbon\Carbon::setTestNow();
});

it('admin can view bulk create page', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('admin.lesson-schedules.bulk.create'))
        ->assertOk()
        ->assertSee('レッスンスケジュール一括作成');
});

it('admin can bulk store schedules', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();

    $payload = [
        'lesson_id' => $lesson->id,
        'items' => [
            [
                'start_datetime' => now()->addDays(1)->format('Y-m-d H:i:s'),
                'end_datetime' => now()->addDays(1)->addHour()->format('Y-m-d H:i:s'),
                'is_active' => true,
            ],
            [
                'start_datetime' => now()->addDays(8)->format('Y-m-d H:i:s'),
                'end_datetime' => now()->addDays(8)->addHour()->format('Y-m-d H:i:s'),
                'is_active' => false,
            ],
        ],
    ];

    $this->actingAs($admin)
        ->post(route('admin.lesson-schedules.bulk.store'), $payload)
        ->assertRedirect(route('admin.lesson-schedules.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('lesson_schedules', [
        'lesson_id' => $lesson->id,
        'start_datetime' => $payload['items'][0]['start_datetime'],
        'end_datetime' => $payload['items'][0]['end_datetime'],
        'is_active' => 1,
    ]);
    $this->assertDatabaseHas('lesson_schedules', [
        'lesson_id' => $lesson->id,
        'start_datetime' => $payload['items'][1]['start_datetime'],
        'end_datetime' => $payload['items'][1]['end_datetime'],
        'is_active' => 0,
    ]);
});

it('admin can bulk store schedules with datetime-local format', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();

    $start1 = now()->addDays(2)->format('Y-m-d\TH:i');
    $end1 = now()->addDays(2)->addHour()->format('Y-m-d\TH:i');
    $start2 = now()->addDays(9)->format('Y-m-d\TH:i');
    $end2 = now()->addDays(9)->addHour()->format('Y-m-d\TH:i');

    $payload = [
        'lesson_id' => $lesson->id,
        'items' => [
            [
                'start_datetime' => $start1,
                'end_datetime' => $end1,
                'is_active' => true,
            ],
            [
                'start_datetime' => $start2,
                'end_datetime' => $end2,
                'is_active' => false,
            ],
        ],
    ];

    $this->actingAs($admin)
        ->post(route('admin.lesson-schedules.bulk.store'), $payload)
        ->assertRedirect(route('admin.lesson-schedules.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('lesson_schedules', [
        'lesson_id' => $lesson->id,
        'start_datetime' => \Carbon\Carbon::parse(str_replace('T', ' ', $start1))->format('Y-m-d H:i:s'),
        'end_datetime' => \Carbon\Carbon::parse(str_replace('T', ' ', $end1))->format('Y-m-d H:i:s'),
        'is_active' => 1,
    ]);
    $this->assertDatabaseHas('lesson_schedules', [
        'lesson_id' => $lesson->id,
        'start_datetime' => \Carbon\Carbon::parse(str_replace('T', ' ', $start2))->format('Y-m-d H:i:s'),
        'end_datetime' => \Carbon\Carbon::parse(str_replace('T', ' ', $end2))->format('Y-m-d H:i:s'),
        'is_active' => 0,
    ]);
});

it('bulk store requires valid payload', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->post(route('admin.lesson-schedules.bulk.store'), [])
        ->assertSessionHasErrors(['lesson_id', 'items']);
});
