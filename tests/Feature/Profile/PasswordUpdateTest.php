<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can update password with correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Old-Password123'),
    ]);

    $response = $this->actingAs($user)->put('/profile/password', [
        'current_password' => 'Old-Password123',
        'password' => 'New-Password456',
        'password_confirmation' => 'New-Password456',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $user->refresh();
    expect(Hash::check('New-Password456', $user->password))->toBeTrue();
});

test('user cannot update password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Old-Password123'),
    ]);

    $response = $this->actingAs($user)->put('/profile/password', [
        'current_password' => 'Wrong-Current-Password',
        'password' => 'New-Password456',
        'password_confirmation' => 'New-Password456',
    ]);

    $response->assertSessionHasErrors(['current_password']);

    $user->refresh();
    expect(Hash::check('Old-Password123', $user->password))->toBeTrue();
});

test('password update requires confirmation matching', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Old-Password123'),
    ]);

    $response = $this->actingAs($user)->put('/profile/password', [
        'current_password' => 'Old-Password123',
        'password' => 'New-Password456',
        'password_confirmation' => 'Mismatching-Password789',
    ]);

    $response->assertSessionHasErrors(['password']);
});
