<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\V2\ForgotPasswordController as V2ForgotPasswordController;
use App\Http\Controllers\Api\Auth\V2\RegisterController as V2RegisterController;
use Illuminate\Support\Facades\Route;

// health check
Route::get('/health-check', function () {
    return response()->json([
        'status' => "OK",
        'Message' => "Project is ready to serve",
    ], 200);
});

/*
|--------------------------------------------------------------------------
| V1 Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1'], function ($router) {
    /*
    |--------------------------------------------------------------------------
    | User Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'guest:api'], function () {
        //register
        Route::post('/register', [RegisterController::class, 'register']); // DONE: user registraion
        Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']); // DONE: email verification
        Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']); // DONE: resend otp

        //login
        Route::post('/login', [LoginController::class, 'login']); // DONE: user login

        //forgot password
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']); // DONE: send forgot password otp
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']); // DONE: verify forgot password otp
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']); // DONE: Reset password
    });

    /*
    |--------------------------------------------------------------------------
    | User Profile and After Authentication
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'auth:api'], function ($router) {
        Route::post('/refresh-token', [LoginController::class, 'refreshToken']); // DONE: refresh token
        Route::post('/logout', [LoginController::class, 'logout']); // DONE: logout

        Route::get('/profile', [UserProfileController::class, 'profile']); // DONE: user profile
        Route::post('/update-profile', [UserProfileController::class, 'updateProfile']); // DONE: update profile
        Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']); // DONE: update avatar
        Route::delete('/delete-profile', [UserProfileController::class, 'destroy']); // DONE: delete profile
        Route::post('/change-password', [UserProfileController::class, 'changePassword']); // DONE: change password
    });
});


/*
|--------------------------------------------------------------------------
| API V2 — Authentication Routes (link-based, no OTP)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v2'], function () {
    Route::group(['middleware' => 'guest:api'], function () {

        // ── Registration ──────────────────────────────────────────────────────
        Route::post('/register', [V2RegisterController::class, 'register']); // DONE: user registration
        Route::get('/verify-email', [V2RegisterController::class, 'verifyEmail']); // DONE: otp verification
        Route::post('/resend-verification', [V2RegisterController::class, 'resendVerification']); // DONE: resend verification token

        // ── Login (reuse v1 LoginController) ─────────────────────────────────
        Route::post('/login', [LoginController::class, 'login']); // DONE: user login

        // ── Forgot Password ───────────────────────────────────────────────────
        Route::post('/forgot-password', [V2ForgotPasswordController::class, 'sendResetLink']); // DONE: forgot password
        Route::get('/verify-reset-token', [V2ForgotPasswordController::class, 'verifyResetToken']); // DONE: verify password reset token
        Route::post('/reset-password', [V2ForgotPasswordController::class, 'resetPassword']); // DONE: Set new password
    });

    Route::group(['middleware' => 'auth:api'], function () {
        // Reuse v1 protected routes as-is or add v2-specific ones here
        Route::post('/refresh-token',[LoginController::class, 'refreshToken']); // DONE: refresh token
        Route::post('/logout',[LoginController::class, 'logout']); // DONE: logout
    });
});
