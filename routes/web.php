<?php

use App\Http\Controllers\Auth\EmailVerificationOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\SavedCalculationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator.index');
Route::post('/calculator/preview', [CalculatorController::class, 'preview'])->name('calculator.preview');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetController::class, 'requestView'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetView'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Email OTP Verification
    Route::get('/email/verify', [EmailVerificationOtpController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verify-otp', [EmailVerificationOtpController::class, 'verify'])->name('verification.verify.otp');
    Route::post('/email/resend-otp', [EmailVerificationOtpController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend.otp');

    // Protected calculation management (requires verified email)
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('calculations.index');
        })->name('dashboard');

        Route::get('/calculations', [SavedCalculationController::class, 'index'])->name('calculations.index');
        Route::get('/calculations/create', [SavedCalculationController::class, 'create'])->name('calculations.create');
        Route::post('/calculations', [SavedCalculationController::class, 'store'])->name('calculations.store');
        Route::get('/calculations/{calculation}', [SavedCalculationController::class, 'show'])->name('calculations.show');
        Route::get('/calculations/{calculation}/edit', [SavedCalculationController::class, 'edit'])->name('calculations.edit');
        Route::put('/calculations/{calculation}', [SavedCalculationController::class, 'update'])->name('calculations.update');
        Route::delete('/calculations/{calculation}', [SavedCalculationController::class, 'destroy'])->name('calculations.destroy');
        Route::post('/calculations/{calculation}/duplicate', [SavedCalculationController::class, 'duplicate'])->name('calculations.duplicate');
    });
});
