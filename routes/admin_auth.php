<?php

use App\Http\Controllers\Web\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Admin\Auth\SignInController;
use Illuminate\Support\Facades\Route;


// ── Show login ─────────────────────────────────────────────────────────────
Route::get('/login', [SignInController::class, 'index'])->name('show.admin.login');

// ── Handle login (Axios → JSON) ────────────────────────────────────────────
Route::post('/login', [SignInController::class, 'login'])->name('admin.login');

// ── Logout (Axios → JSON) — no auth middleware needed (session may be stale)
Route::post('/logout', [SignInController::class, 'logout'])->name('admin.logout');

// ── Forgot password ────────────────────────────────────────────────────────
Route::get('/forgot-password',  [ForgotPasswordController::class, 'index'])->name('show.forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('forgot-password');

// ── OTP verification ───────────────────────────────────────────────────────
Route::get('/verify-otp',  [ForgotPasswordController::class, 'showVerifyOtp'])->name('show.otp.verification');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('otp.verification');

// ── Set new password ───────────────────────────────────────────────────────
Route::get('/set-new-password',  [ForgotPasswordController::class, 'showSetNewPassword'])->name('show.set.new.password');
Route::post('/set-new-password', [ForgotPasswordController::class, 'setNewPassword'])->name('set.new.password');
