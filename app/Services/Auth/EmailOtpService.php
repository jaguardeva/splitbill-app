<?php

namespace App\Services\Auth;

use App\Models\EmailOtpChallenge;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    public const COOLDOWN_SECONDS = 60;

    public const EXPIRY_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    /**
     * Send or resend an email OTP challenge.
     */
    public function sendOtp(User $user, string $purpose = 'email_verification'): EmailOtpChallenge
    {
        $active = EmailOtpChallenge::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->active()
            ->latest()
            ->first();

        if ($active && $active->last_sent_at->addSeconds(self::COOLDOWN_SECONDS)->isFuture()) {
            $secondsRemaining = (int) now()->diffInSeconds($active->last_sent_at->addSeconds(self::COOLDOWN_SECONDS));
            throw ValidationException::withMessages([
                'otp' => "Mohon tunggu {$secondsRemaining} detik lagi sebelum meminta kode baru.",
            ]);
        }

        return DB::transaction(function () use ($user, $purpose): EmailOtpChallenge {
            // Invalidate any existing unconsumed challenges for this purpose
            EmailOtpChallenge::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            // Generate cryptographically secure 6-digit numeric OTP
            $code = (string) random_int(100000, 999999);

            $challenge = new EmailOtpChallenge;
            $challenge->id = (string) Str::uuid();
            $challenge->user_id = $user->id;
            $challenge->purpose = $purpose;
            $challenge->code_hash = Hash::make($code);
            $challenge->expires_at = now()->addMinutes(self::EXPIRY_MINUTES);
            $challenge->last_sent_at = now();
            $challenge->attempts = 0;
            $challenge->max_attempts = self::MAX_ATTEMPTS;
            $challenge->save();

            $user->notify(new EmailVerificationOtpNotification($code, self::EXPIRY_MINUTES));

            return $challenge;
        });
    }

    /**
     * Verify an entered OTP challenge.
     */
    public function verifyOtp(User $user, string $code, string $purpose = 'email_verification'): bool
    {
        $code = trim($code);

        /** @var EmailOtpChallenge|null $challenge */
        $challenge = EmailOtpChallenge::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $challenge) {
            throw ValidationException::withMessages([
                'otp' => 'Tidak ada kode verifikasi aktif. Silakan minta kode baru.',
            ]);
        }

        if ($challenge->isExpired()) {
            throw ValidationException::withMessages([
                'otp' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.',
            ]);
        }

        if ($challenge->hasExceededAttempts()) {
            throw ValidationException::withMessages([
                'otp' => 'Anda telah melebihi batas percobaan. Silakan minta kode verifikasi baru.',
            ]);
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');
            $remaining = max(0, $challenge->max_attempts - $challenge->attempts);

            if ($remaining === 0) {
                throw ValidationException::withMessages([
                    'otp' => 'Kode salah dan batas percobaan telah habis. Silakan minta kode baru.',
                ]);
            }

            throw ValidationException::withMessages([
                'otp' => "Kode OTP salah. Sisa kesempatan mencoba: {$remaining} kali.",
            ]);
        }

        // Successfully verified
        DB::transaction(function () use ($user, $challenge) {
            $challenge->consumed_at = now();
            $challenge->save();

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                event(new Verified($user));
            }
        });

        return true;
    }

    /**
     * Get remaining cooldown seconds for resending OTP.
     */
    public function secondsUntilResend(User $user, string $purpose = 'email_verification'): int
    {
        $challenge = EmailOtpChallenge::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        if (! $challenge) {
            return 0;
        }

        $cooldownEnds = $challenge->last_sent_at->addSeconds(self::COOLDOWN_SECONDS);

        return $cooldownEnds->isFuture() ? (int) now()->diffInSeconds($cooldownEnds) : 0;
    }
}
