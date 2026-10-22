<?php

use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

function encryptedLoginTwoFactorState(string $secret, array $recoveryCodes): array
{
    return [
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ];
}

test('user with 2fa enabled is redirected to challenge page on login', function () {
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password123'),
    ], encryptedLoginTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['EMERG-CODE1'])));

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/two-factor-challenge');
    expect(session('login.id'))->toBe($user->id);
});

test('user without 2fa logs in directly without challenge', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/calculations');
});

test('user can authenticate using valid 6 digit totp code on challenge page', function () {
    $totp = app(TotpService::class);
    $secret = $totp->generateSecret(32);
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password123'),
    ], encryptedLoginTwoFactorState($secret, ['EMERG-CODE1'])));

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', [
            'code' => $totp->calculateCode($secret),
        ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/calculations');
});

test('user cannot authenticate using invalid totp code', function () {
    $totp = app(TotpService::class);
    $secret = $totp->generateSecret(32);
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password123'),
    ], encryptedLoginTwoFactorState($secret, ['EMERG-CODE1'])));

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', ['code' => '999999']);

    $this->assertGuest();
    $response->assertSessionHasErrors(['code']);
});

test('user can authenticate using emergency recovery code and code is replaced', function () {
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password123'),
    ], encryptedLoginTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['EMERG-CODE1', 'EMERG-CODE2'])));

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', ['recovery_code' => 'EMERG-CODE1']);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/calculations');
    $user->refresh();
    expect($user->recoveryCodes())->not->toContain('EMERG-CODE1');
    expect($user->recoveryCodes())->toContain('EMERG-CODE2');
});

test('user cannot authenticate using an invalid recovery code', function () {
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password123'),
    ], encryptedLoginTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['EMERG-CODE2'])));

    $response = $this->withSession(['login.id' => $user->id])
        ->post('/two-factor-challenge', ['recovery_code' => 'EMERG-CODE1']);

    $this->assertGuest();
    $response->assertSessionHasErrors(['recovery_code']);
});
