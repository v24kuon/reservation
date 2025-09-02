<?php

it('redirects guest to login for home', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

it('redirects unverified user to verification notice for home', function () {
    $user = \App\Models\User::factory()->create(['email_verified_at' => null]);
    $this->actingAs($user);
    $this->get('/')
        ->assertRedirect(route('verification.notice'));
});
