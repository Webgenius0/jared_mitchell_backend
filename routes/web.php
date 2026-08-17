<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin_auth.php';

Route::post('/webhooks/aws-ivs', [App\Http\Controllers\Api\AwsIvsWebhookController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

