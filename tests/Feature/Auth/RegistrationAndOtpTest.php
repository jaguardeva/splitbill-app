<?php

use App\Models\EmailOtpChallenge;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use App\Services\Auth\EmailOtpService;
use Illuminate\Support\Facades\Notification;

test('guest can register, receives OTP notification and is redirected to OTP notice', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    $response->assertRedirect('/email/verify');

    $this->assertAuthenticated();

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and(strlen($user->id))->toBe(36); // UUID check

    $challenge = EmailOtpChallenge::where('user_id', $user->id)->first();
    expect($challenge)->not->toBeNull()
        ->and($challenge->purpose)->toBe('email_verification')
        ->and($challenge->consumed_at)->toBeNull();

    Notification::assertSentTo($user, EmailVerificationOtpNotification::class);
});

test('registration rejects a password shorter than eight characters', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'short-password@example.com',
        'password' => 'Ab1!',
        'password_confirmation' => 'Ab1!',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::where('email', 'short-password@example.com')->exists())->toBeFalse();
});

test('registration rejects a password containing spaces', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'space-password@example.com',
        'password' => 'Secure Pass1',
        'password_confirmation' => 'Secure Pass1',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::where('email', 'space-password@example.com')->exists())->toBeFalse();
});

test('registration rejects a password without mixed case', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'mixed-case-password@example.com',
        'password' => 'securepass1',
        'password_confirmation' => 'securepass1',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::where('email', 'mixed-case-password@example.com')->exists())->toBeFalse();
});

test('registration rejects a password without a number or symbol', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'no-number-symbol@example.com',
        'password' => 'SecurePass',
        'password_confirmation' => 'SecurePass',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::where('email', 'no-number-symbol@example.com')->exists())->toBeFalse();
});

test('registration validation messages use Indonesian field names', function () {
    $response = $this->post('/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password']);

    $errors = session('errors');

    expect($errors->get('name')[0])->toBe('Nama wajib diisi.')
        ->and($errors->get('email')[0])->toBe('Alamat email harus berupa alamat email yang valid.')
        ->and($errors->get('password')[0])->toBe('Kata sandi wajib diisi.');
});

test('user can verify email with correct OTP', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $otpService = app(EmailOtpService::class);
    $challenge = $otpService->sendOtp($user);

    // Get the generated OTP code from notification or challenge
    // We can simulate OTP verification directly using the service
    // Let's create a known challenge with known code
    $code = '123456';
    $challenge->code_hash = bcrypt($code);
    $challenge->save();

    $response = $this->post('/email/verify-otp', [
        'otp' => '123456',
    ]);

    $response->assertRedirect('/calculations');

    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeTrue();

    $challenge->refresh();
    expect($challenge->consumed_at)->not->toBeNull();
});

test('submitting wrong OTP increments attempts and fails', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $otpService = app(EmailOtpService::class);
    $challenge = $otpService->sendOtp($user);

    $response = $this->post('/email/verify-otp', [
        'otp' => '000000',
    ]);

    $response->assertSessionHasErrors('otp');

    $challenge->refresh();
    expect($challenge->attempts)->toBe(1)
        ->and($challenge->consumed_at)->toBeNull();

    $user->refresh();
    expect($user->hasVerifiedEmail())->toBeFalse();
});

test('unverified user cannot access protected calculations routes', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/calculations');
    $response->assertRedirect('/email/verify');
});

test('resend OTP enforces 60-second cooldown', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    $otpService = app(EmailOtpService::class);
    $otpService->sendOtp($user);

    $response = $this->post('/email/resend-otp');
    $response->assertSessionHasErrors('otp');
});
