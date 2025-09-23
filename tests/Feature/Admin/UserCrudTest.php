<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\patch;
use function Pest\Laravel\delete;

beforeEach(function () {
    // Ensure roles exist on factory states if needed
});

it('requires admin to access users index', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    actingAs($user);

    get(route('admin.users.index'))->assertForbidden();
});

it('shows users index for admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    actingAs($admin);

    get(route('admin.users.index'))->assertOk();
});

it('can create user as admin', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    actingAs($admin);

    $payload = [
        'name' => 'テストユーザー',
        'email' => 'new-user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    post(route('admin.users.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $created = User::query()->where('email', $payload['email'])->first();
    expect($created)->not->toBeNull();
    expect($created->role)->toBe(User::ROLE_USER);
});

it('can update general user as admin (role unchanged)', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $target = User::factory()->create(['role' => User::ROLE_USER]);
    actingAs($admin);

    $payload = [
        'name' => '更新後ユーザー',
        'email' => 'updated-user@example.com',
    ];

    patch(route('admin.users.update', $target), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $target->refresh();
    expect($target->name)->toBe('更新後ユーザー')
        ->and($target->email)->toBe('updated-user@example.com')
        ->and($target->role)->toBe(User::ROLE_USER);
});

it('can delete user as admin (not self, not last admin)', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $anotherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $target = User::factory()->create(['role' => User::ROLE_USER]);
    actingAs($admin);

    delete(route('admin.users.destroy', $target))
        ->assertRedirect();

    expect(User::query()->whereKey($target->getKey())->exists())->toBeFalse();

    // admin deletion is out of scope on this screen (should not be found)
    delete(route('admin.users.destroy', $admin))->assertNotFound();
    delete(route('admin.users.destroy', $anotherAdmin))->assertNotFound();
});

it('lists only general users on index (role=user)', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $u1 = User::factory()->create(['role' => User::ROLE_USER, 'name' => '一般ユーザーA']);
    $u2 = User::factory()->create(['role' => User::ROLE_USER, 'name' => '一般ユーザーB']);
    $inst = User::factory()->create(['role' => User::ROLE_INSTRUCTOR, 'name' => 'インストラクターX']);

    actingAs($admin);

    get(route('admin.users.index'))
        ->assertOk()
        ->assertSee($u1->name)
        ->assertSee($u2->name)
        ->assertDontSee($inst->name);
});

it('forbids non-admin to create/update/delete users', function () {
    $nonAdmin = User::factory()->create(['role' => User::ROLE_USER]);
    $target = User::factory()->create(['role' => User::ROLE_USER]);
    actingAs($nonAdmin);

    post(route('admin.users.store'), [
        'name' => 'x', 'email' => 'x@example.com', 'password' => 'password123', 'role' => User::ROLE_USER,
    ])->assertForbidden();

    patch(route('admin.users.update', $target), [
        'name' => 'y', 'email' => 'y@example.com', 'role' => User::ROLE_USER,
    ])->assertForbidden();

    delete(route('admin.users.destroy', $target))->assertForbidden();
});
