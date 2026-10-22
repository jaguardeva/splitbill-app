<?php

use App\Models\User;

test('profile page is displayed for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Profile/Edit')
        ->has('twoFactorEnabled')
        ->has('twoFactorConfirmed')
    );
});

test('guest cannot access profile page', function () {
    $response = $this->get('/profile');

    $response->assertRedirect('/login');
});

test('user can update profile information', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Updated Name',
        'email' => 'original@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('original@example.com');
});

test('user cannot change their email address', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'email' => 'new@example.com',
    ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->email)->toBe('old@example.com');
    expect($user->email_verified_at)->not->toBeNull();
});

test('profile update ignores an email change attempt', function () {
    $otherUser = User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'User Name',
        'email' => 'taken@example.com',
    ]);

    $response->assertSessionHasNoErrors();
    $user->refresh();
    expect($user->email)->toBe('user@example.com');
});
