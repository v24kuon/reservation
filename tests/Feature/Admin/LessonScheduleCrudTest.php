<?php

use App\Models\Lesson;
use App\Models\LessonSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view lesson schedules index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.lesson-schedules.index'))
        ->assertOk();
});

it('admin can create a lesson schedule', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();
    $payload = [
        'lesson_id' => $lesson->id,
        'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
        'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        'current_bookings' => 0,
        'is_active' => true,
    ];

    $this->actingAs($admin)
        ->post(route('admin.lesson-schedules.store'), $payload)
        ->assertRedirect(route('admin.lesson-schedules.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('lesson_schedules', ['lesson_id' => $lesson->id]);
});

it('validation fails for invalid schedule payload', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->post(route('admin.lesson-schedules.store'), [])
        ->assertSessionHasErrors(['lesson_id', 'start_datetime', 'end_datetime', 'current_bookings', 'is_active']);
});

it('admin can update a lesson schedule', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();
    $schedule = LessonSchedule::factory()->create([
        'lesson_id' => $lesson->id,
        'start_datetime' => now()->addDay(),
        'end_datetime' => now()->addDay()->addHour(),
        'current_bookings' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.lesson-schedules.update', $schedule), [
            'current_bookings' => 2,
        ])
        ->assertRedirect(route('admin.lesson-schedules.index'))
        ->assertSessionHasNoErrors();

    $schedule->refresh();
    expect($schedule->current_bookings)->toBe(2);
});

it('admin can delete a lesson schedule', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();
    $schedule = LessonSchedule::factory()->create([
        'lesson_id' => $lesson->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.lesson-schedules.destroy', $schedule))
        ->assertRedirect(route('admin.lesson-schedules.index'));

    $this->assertModelMissing($schedule);
});
