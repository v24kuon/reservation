<?php

use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view lessons index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.lessons.index'))
        ->assertOk();
});

it('admin can create a lesson', function () {
    $admin = adminUser();
    $store = Store::factory()->create();
    $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);

    $payload = [
        'store_id' => $store->id,
        'name' => 'Test Lesson',
        'category_id' => $category->id,
        'instructor_user_id' => $instructor->id,
        'duration' => 60,
        'capacity' => 10,
        'booking_deadline_hours' => 24,
        'cancel_deadline_hours' => 24,
        'is_active' => true,
    ];

    $this->actingAs($admin)
        ->post(route('admin.lessons.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('lessons', ['name' => 'Test Lesson']);
});

it('admin can update a lesson', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();
    $this->actingAs($admin)
        ->patch(route('admin.lessons.update', $lesson), array_merge($lesson->toArray(), [
            'name' => 'Renamed',
            'is_active' => true,
        ]))
        ->assertRedirect();
    $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'name' => 'Renamed']);
});

it('admin can delete a lesson', function () {
    $admin = adminUser();
    $lesson = Lesson::factory()->create();
    $this->actingAs($admin)
        ->delete(route('admin.lessons.destroy', $lesson))
        ->assertRedirect();
    $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
});
