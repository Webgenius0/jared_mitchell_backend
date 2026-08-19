<?php

use Illuminate\Support\Facades\Route;
Route::post('/webhooks/aws-ivs', [App\Http\Controllers\Api\AwsIvsWebhookController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

