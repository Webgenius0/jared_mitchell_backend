<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
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
| User Authentication Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'guest:api', 'prefix' => 'v1'], function ($router) {
    //register
    Route::post('/register', [RegisterController::class, 'register']); // DONE: user registraion
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']); // DONE: email verification
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']); // done

    //login
    Route::post('/login', [LoginController::class, 'login']); // done

    //forgot password
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendOtp']); // done
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']); // done
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']); // done
});

