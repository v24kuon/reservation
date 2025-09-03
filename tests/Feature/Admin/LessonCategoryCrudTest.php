<?php

use App\Models\LessonCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view lesson categories index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.lesson-categories.index'))
        ->assertOk();
});

it('admin can create a lesson category under fixed root', function () {
    $admin = adminUser();
    $root = App\Models\LessonCategory::factory()->create(['parent_id' => null]);
    $payload = [
        'name' => 'Yoga',
        'sort_order' => 1,
        'is_active' => true,
        'parent_id' => $root->id,
    ];

    $this->actingAs($admin)
        ->post(route('admin.lesson-categories.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('lesson_categories', ['name' => 'Yoga']);
});

it('validation fails without required fields for lesson categories and parent required', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->post(route('admin.lesson-categories.store'), [])
        ->assertSessionHasErrors(['name','is_active','sort_order','parent_id']);
});

it('admin can update a lesson category', function () {
    $admin = adminUser();
    $root = App\Models\LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $this->actingAs($admin)
        ->patch(route('admin.lesson-categories.update', $category), [
            'name' => 'Renamed',
            'sort_order' => $category->sort_order,
            'is_active' => true,
            'parent_id' => $root->id,
        ])
        ->assertRedirect();
    $this->assertDatabaseHas('lesson_categories', ['id' => $category->id, 'name' => 'Renamed']);
});

it('admin can delete a lesson category', function () {
    $admin = adminUser();
    $category = LessonCategory::factory()->create();
    $this->actingAs($admin)
        ->delete(route('admin.lesson-categories.destroy', $category))
        ->assertRedirect();
    $this->assertDatabaseMissing('lesson_categories', ['id' => $category->id]);
});

it('admin sees edit link on lesson categories index', function () {
    $admin = adminUser();
    $category = LessonCategory::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.lesson-categories.index'))
        ->assertOk()
        ->assertSee('編集');
});


