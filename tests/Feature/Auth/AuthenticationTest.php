<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('admins are redirected to dashboard after login', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('authenticated general user visiting login is redirected to home', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('home', absolute: false));
});

test('authenticated admin visiting login is redirected to dashboard', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('authenticated instructor visiting login is redirected to dashboard', function () {
    $user = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
