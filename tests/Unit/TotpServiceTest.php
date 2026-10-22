<?php

use App\Services\Auth\TotpService;

test('totp service generates valid 32 character base32 secret', function () {
    $service = new TotpService;
    $secret = $service->generateSecret(32);

    expect(strlen($secret))->toBe(32);
    expect($secret)->toMatch('/^[A-Z2-7]+$/');
});

test('totp service calculates 6 digit numeric code and verifies current time', function () {
    $service = new TotpService;
    $secret = $service->generateSecret(32);

    $code = $service->calculateCode($secret);

    expect(strlen($code))->toBe(6);
    expect(ctype_digit($code))->toBeTrue();
    expect($service->verify($secret, $code))->toBeTrue();
});

test('totp service verifies within time drift window', function () {
    $service = new TotpService;
    $secret = $service->generateSecret(32);

    $currentTime = time();
    $pastCode = $service->calculateCode($secret, $currentTime - 30);
    $futureCode = $service->calculateCode($secret, $currentTime + 30);
    $farPastCode = $service->calculateCode($secret, $currentTime - 90);

    // Standard window = 1 allows -30s and +30s
    expect($service->verify($secret, $pastCode, 1))->toBeTrue();
    expect($service->verify($secret, $futureCode, 1))->toBeTrue();

    // 90s skew should fail with window = 1
    expect($service->verify($secret, $farPastCode, 1))->toBeFalse();
});

test('totp service generates 8 recovery codes in format XXXXX-XXXXX', function () {
    $service = new TotpService;
    $codes = $service->generateRecoveryCodes(8);

    expect($codes)->toHaveCount(8);
    foreach ($codes as $code) {
        expect($code)->toMatch('/^[A-Z0-9]{5}-[A-Z0-9]{5}$/');
    }
});
