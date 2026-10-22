<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class TotpService
{
    /**
     * Standard RFC 4648 Base32 alphabet.
     */
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Time-step period in seconds (RFC 6238 standard is 30 seconds).
     */
    public const TIME_STEP = 30;

    /**
     * Number of digits in generated TOTP code.
     */
    public const CODE_DIGITS = 6;

    /**
     * Generate a cryptographically secure Base32 secret key.
     */
    public function generateSecret(int $length = 32): string
    {
        $secret = '';
        $alphabetLength = strlen(self::BASE32_ALPHABET);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Calculate the 6-digit TOTP code for a secret and timestamp.
     */
    public function calculateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / self::TIME_STEP);

        $binarySecret = $this->base32Decode($secret);

        // Pack 64-bit integer into big-endian binary string
        $binaryTime = pack('J', $timeSlice);

        // Compute HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryTime, $binarySecret, true);

        // Dynamic truncation according to RFC 4226 / RFC 6238
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        $modulo = 10 ** self::CODE_DIGITS;
        $code = (string) ($truncatedHash % $modulo);

        return str_pad($code, self::CODE_DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code against a secret with window drift tolerance.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== self::CODE_DIGITS || ! ctype_digit($code)) {
            return false;
        }

        $currentTime = time();

        for ($i = -$window; $i <= $window; $i++) {
            $sliceTime = $currentTime + ($i * self::TIME_STEP);
            $calculatedCode = $this->calculateCode($secret, $sliceTime);

            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate emergency recovery codes (e.g. 8 codes formatted as XXXXX-XXXXX).
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(5));
            $part2 = strtoupper(Str::random(5));
            $codes[] = "{$part1}-{$part2}";
        }

        return $codes;
    }

    /**
     * Generate the standard otpauth:// URL for authenticator apps.
     */
    public function getProvisioningUri(User $user, string $secret, string $issuer = 'SplitBill'): string
    {
        $cleanEmail = rawurlencode($user->email);
        $cleanIssuer = rawurlencode($issuer);

        return "otpauth://totp/{$cleanIssuer}:{$cleanEmail}?secret={$secret}&issuer={$cleanIssuer}";
    }

    /**
     * Decode a Base32 string into binary bytes.
     */
    public function base32Decode(string $secret): string
    {
        $secret = strtoupper(str_replace([' ', '=', '-'], '', $secret));
        $alphabet = self::BASE32_ALPHABET;

        $buffer = 0;
        $bufferSize = 0;
        $binary = '';

        for ($i = 0, $len = strlen($secret); $i < $len; $i++) {
            $char = $secret[$i];
            $position = strpos($alphabet, $char);

            if ($position === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $position;
            $bufferSize += 5;

            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $binary .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }

        return $binary;
    }

    /**
     * Encode binary bytes into a Base32 string.
     */
    public function base32Encode(string $binary): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $buffer = 0;
        $bufferSize = 0;
        $encoded = '';

        for ($i = 0, $len = strlen($binary); $i < $len; $i++) {
            $buffer = ($buffer << 8) | ord($binary[$i]);
            $bufferSize += 8;

            while ($bufferSize >= 5) {
                $bufferSize -= 5;
                $encoded .= $alphabet[($buffer >> $bufferSize) & 0x1F];
            }
        }

        if ($bufferSize > 0) {
            $encoded .= $alphabet[($buffer << (5 - $bufferSize)) & 0x1F];
        }

        return $encoded;
    }
}
