<?php

use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

function confirmTwoFactorManagementPassword(User $user): void
{
    test()->actingAs($user)->post('/user/confirm-password', [
        'password' => 'password',
    ]);
}

function encryptedTwoFactorState(string $secret, array $recoveryCodes, mixed $confirmedAt = null): array
{
    return [
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => $confirmedAt,
    ];
}

test('user can initiate two factor authentication setup with Fortify', function () {
    $user = User::factory()->create();
    confirmTwoFactorManagementPassword($user);

    $response = $this->actingAs($user)->post('/user/two-factor-authentication');

    $response->assertSessionHas('status', 'two-factor-authentication-enabled');
    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
    expect($user->recoveryCodes())->toHaveCount(8);
});

test('user cannot confirm two factor authentication with invalid code', function () {
    $user = User::factory()->create();
    confirmTwoFactorManagementPassword($user);
    $this->actingAs($user)->post('/user/two-factor-authentication');

    $response = $this->actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => '000000',
    ]);

    $response->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', ['code']);
    $user->refresh();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user can confirm two factor authentication with valid totp code', function () {
    $user = User::factory()->create();
    $totp = app(TotpService::class);
    confirmTwoFactorManagementPassword($user);
    $this->actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
    $validCode = $totp->calculateCode($secret);
    $response = $this->actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $response->assertSessionHas('status', 'two-factor-authentication-confirmed');
    $user->refresh();
    expect($user->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('user can regenerate recovery codes when 2fa is confirmed', function () {
    $user = User::factory()->create();
    $totp = app(TotpService::class);
    confirmTwoFactorManagementPassword($user);
    $this->actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();
    $code = $totp->calculateCode(Fortify::currentEncrypter()->decrypt($user->two_factor_secret));
    $this->actingAs($user)->post('/user/confirmed-two-factor-authentication', ['code' => $code]);
    $user->refresh();
    $originalCodes = $user->recoveryCodes();

    $response = $this->actingAs($user)->post('/user/two-factor-recovery-codes', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertOk();
    $user->refresh();
    expect($user->recoveryCodes())->toHaveCount(8);
    expect($user->recoveryCodes())->not->toBe($originalCodes);
});

test('user can disable two factor authentication after password confirmation', function () {
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password'),
    ], encryptedTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['CODE1-AAAAA'], now())));
    confirmTwoFactorManagementPassword($user);

    $response = $this->actingAs($user)->delete('/user/two-factor-authentication');

    $response->assertSessionHas('status', 'two-factor-authentication-disabled');
    $user->refresh();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user cannot manage two factor authentication with wrong password', function () {
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password'),
    ], encryptedTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['CODE1-AAAAA'], now())));

    $response = $this->actingAs($user)->post('/user/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['password']);
    $user->refresh();
    expect($user->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('confirm password returns localized indonesian error message when password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->postJson('/user/confirm-password', [
        'password' => 'wrong-password',
    ], [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'password' => 'Kata sandi yang Anda masukkan salah.',
        ]);
});

test('user can confirm password through the JSON request used by 2FA', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->postJson('/user/confirm-password', [
        'password' => 'password',
        '_token' => 'test-csrf-token',
    ], [
        'X-Requested-With' => 'XMLHttpRequest',
        'X-CSRF-TOKEN' => 'test-csrf-token',
    ]);

    $response->assertOk()
        ->assertJson(['confirmed' => true]);
    expect(session()->has('auth.password_confirmed_at'))->toBeTrue();
});

test('two factor setup qr code and key persist on profile reload', function () {
    $user = User::factory()->create(encryptedTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', [], null));

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Profile/Edit')
        ->where('twoFactorEnabled', false)
        ->where('twoFactorConfirmed', false)
        ->whereNot('qrCodeSvg', null)
        ->where('setupKey', 'SECRETBASE32KEYEXAMPLE1234567890')
    );
});

test('user can cancel unconfirmed two factor setup by disabling it', function () {
    $user = User::factory()->create(encryptedTwoFactorState('SECRETBASE32KEYEXAMPLE1234567890', ['CODE1-AAAAA'], null));
    confirmTwoFactorManagementPassword($user);

    $response = $this->actingAs($user)->delete('/user/two-factor-authentication');

    $response->assertSessionHasNoErrors();
    $user->refresh();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('confirm password returns back when called via Inertia request', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->from('/profile')
        ->post('/user/confirm-password', [
            'password' => 'password',
        ], [
            'X-Inertia' => 'true',
        ]);

    $response->assertRedirect('/profile');
    expect(session()->has('auth.password_confirmed_at'))->toBeTrue();
});
