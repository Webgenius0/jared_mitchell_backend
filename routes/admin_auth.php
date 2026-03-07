<?php

use App\Http\Controllers\Web\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Admin\Auth\SignInController;
use App\Http\Controllers\Web\Admin\Dashboard\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('show.admin.dashboard'); // show admin dashboard

Route::get('/login', [SignInController::class, 'index'])->name('show.admin.login'); // show admin login
Route::post('/login', [SignInController::class, 'login'])->name('admin.login'); // admin login


Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('show.forgot-password'); // show forgot password
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('forgot-password'); // show otp to admin mail


Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyOtp'])->name('show.otp.verification');  // Show OTP Verification
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('otp.verification');  // Show OTP Verification


Route::get('/set-new-password', [ForgotPasswordController::class, 'showSetNewPassword'])->name('show.set.new.password'); // show set new password
Route::post('/set-new-password', [ForgotPasswordController::class, 'setNewPassword'])->name('set.new.password'); // set new password
