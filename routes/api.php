<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use Illuminate\Http\Request;
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
        Route::post('/forgot-password', [ResetPasswordController::class, 'sendOtp']);
        Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']);
        Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
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
        Route::post('/update-profile', [UserProfileController::class, 'updateProfile']); // done
        Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']); // done
        Route::delete('/delete-profile', [UserProfileController::class, 'destroy']); // done
        Route::post('/change-password', [UserProfileController::class, 'changePassword']); // done
    });
});
