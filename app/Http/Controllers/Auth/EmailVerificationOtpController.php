<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationOtpController extends Controller
{
    /**
     * Show the OTP verification page.
     */
    public function notice(Request $request, EmailOtpService $otpService): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('calculations.index');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => $user->email,
            'cooldown_seconds' => $otpService->secondsUntilResend($user),
        ]);
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        /** @var User $user */
        $user = $request->user();

        $otpService->verifyOtp($user, $validated['otp']);

        return redirect()->intended(route('calculations.index'))
            ->with('success', 'Email Anda berhasil diverifikasi!');
    }

    /**
     * Resend the OTP code.
     */
    public function resend(Request $request, EmailOtpService $otpService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('calculations.index');
        }

        $otpService->sendOtp($user);

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
