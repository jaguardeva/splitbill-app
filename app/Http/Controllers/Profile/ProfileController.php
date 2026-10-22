<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Fortify;

class ProfileController extends Controller
{
    /**
     * Display the user's profile and security settings form.
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $qrCodeSvg = null;
        $setupKey = null;

        if (! $user->hasEnabledTwoFactorAuthentication() && ! is_null($user->two_factor_secret)) {
            $qrCodeSvg = $user->twoFactorQrCodeSvg();
            $setupKey = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'isEmailVerified' => $user->hasVerifiedEmail(),
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'twoFactorConfirmed' => ! is_null($user->two_factor_confirmed_at),
            'qrCodeSvg' => $qrCodeSvg,
            'setupKey' => $setupKey,
            'recoveryCodes' => $request->session()->get('two_factor_recovery_codes'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);

        $user->name = $name;
        $user->save();

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }
}
