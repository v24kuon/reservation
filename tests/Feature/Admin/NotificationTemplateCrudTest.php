<?php

use App\Models\NotificationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can list notification templates', function () {
    $admin = adminUser();
    NotificationTemplate::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.notification-templates.index'))
        ->assertOk();
});

it('admin can create a notification template', function () {
    $admin = adminUser();
    $payload = [
        'name' => '予約確認',
        'type' => 'reservation_confirmation',
        'subject' => '【予約確認】{{user_name}}様',
        'body_text' => 'ご予約ありがとうございます。',
        'variables' => ['user_name', 'lesson_name'],
        'is_active' => true,
    ];

    $this->actingAs($admin)
        ->post(route('admin.notification-templates.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('notification_templates', [
        'name' => '予約確認',
        'type' => 'reservation_confirmation',
        'subject' => '【予約確認】{{user_name}}様',
        'is_active' => 1,
    ]);
});

it('validation fails for invalid payload', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->post(route('admin.notification-templates.store'), [])
        ->assertSessionHasErrors(['name', 'type', 'subject']);
});

it('admin can update a notification template', function () {
    $admin = adminUser();
    $template = NotificationTemplate::factory()->create([
        'type' => 'reminder',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.notification-templates.update', $template), [
            'subject' => '変更後の件名',
            'is_active' => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notification_templates', [
        'id' => $template->id,
        'subject' => '変更後の件名',
        'is_active' => 0,
    ]);
});

it('admin can delete a notification template', function () {
    $admin = adminUser();
    $template = NotificationTemplate::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.notification-templates.destroy', $template))
        ->assertRedirect();

    $this->assertDatabaseMissing('notification_templates', [
        'id' => $template->id,
    ]);
});
