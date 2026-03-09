<?php

use App\Http\Controllers\Web\Admin\Settings\ContactSettingsController;
use App\Http\Controllers\Web\Admin\Settings\EnvSettingsController;
use App\Http\Controllers\Web\Admin\Settings\GeneralSettingsController;
use App\Http\Controllers\Web\Admin\Settings\LogoSettingsController;
use App\Http\Controllers\Web\Admin\Settings\MailSettingsController;
use App\Http\Controllers\Web\Admin\Settings\MaintenanceSettingsController;
use App\Http\Controllers\Web\Admin\Settings\SeoSettingsController;
use App\Http\Controllers\Web\Admin\Settings\SocialSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

Route::prefix('settings')->name('admin.settings.')->group(function () {
    // General
    Route::get('/general', [GeneralSettingsController::class, 'general'])->name('general');
    Route::patch('/general', [GeneralSettingsController::class, 'updateGeneral'])->name('general.update');

    // Contact
    Route::get('/contact', [ContactSettingsController::class, 'contact'])->name('contact');
    Route::patch('/contact', [ContactSettingsController::class, 'updateContact'])->name('contact.update');

    // // Logo & Branding
    Route::get('/logo', [LogoSettingsController::class, 'logo'])->name('logo');
    Route::post('/logo', [LogoSettingsController::class, 'updateLogo'])->name('logo.update');

    // // Social Media
    Route::get('/social', [SocialSettingsController::class, 'social'])->name('social');
    Route::patch('/social', [SocialSettingsController::class, 'updateSocial'])->name('social.update');

    // // Stripe
    Route::get('/seo', [SeoSettingsController::class, 'seo'])->name('seo');
    Route::patch('/seo', [SeoSettingsController::class, 'updateSeo'])->name('seo.update');

    // // Mail
    Route::get('/mail', [MailSettingsController::class, 'mail'])->name('mail');
    Route::patch('/mail', [MailSettingsController::class, 'updateMail'])->name('mail.update');

    // // Maintenance
    Route::get('/maintenance', [MaintenanceSettingsController::class, 'maintenance'])->name('maintenance');
    Route::patch('/maintenance', [MaintenanceSettingsController::class, 'updateMaintenance'])->name('maintenance.update');

    // Stripe — writes to .env
    Route::get('/stripe',    [EnvSettingsController::class, 'stripe'])->name('stripe');
    Route::patch('/stripe',  [EnvSettingsController::class, 'updateStripe'])->name('stripe.update');

    // Artisan command runner (from maintenance page)
    Route::post('/artisan/run',  [EnvSettingsController::class, 'runArtisan'])->name('artisan.run');

    // Reverb WebSocket — writes to .env
    Route::get('/reverb',    [EnvSettingsController::class, 'reverb'])->name('reverb');
    Route::patch('/reverb',  [EnvSettingsController::class, 'updateReverb'])->name('reverb.update');

    // AWS S3 — writes to .env
    Route::get('/aws',       [EnvSettingsController::class, 'aws'])->name('aws');
    Route::patch('/aws',     [EnvSettingsController::class, 'updateAws'])->name('aws.update');

    // IMAP — writes to .env
    Route::get('/imap',      [EnvSettingsController::class, 'imap'])->name('imap');
    Route::patch('/imap',    [EnvSettingsController::class, 'updateImap'])->name('imap.update');
});
