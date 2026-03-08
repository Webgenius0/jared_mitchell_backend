<?php

use App\Http\Controllers\Web\Admin\Auth\AdminProfileController;
use App\Http\Controllers\Web\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Web\Admin\Settings\ContactSettingsController;
use App\Http\Controllers\Web\Admin\Settings\GeneralSettingsController;
use App\Http\Controllers\Web\Admin\Settings\LogoSettingsController;
use App\Http\Controllers\Web\Admin\Settings\MailSettingsController;
use App\Http\Controllers\Web\Admin\Settings\MaintenanceSettingsController;
use App\Http\Controllers\Web\Admin\Settings\SocialSettingsController;
use App\Http\Controllers\Web\Admin\Settings\StripeSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminDashboardController::class, 'index'])->name('show.admin.dashboard'); // show admin dashboard

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::prefix('profile')->name('admin.profile.')->group(function () {
    Route::get('/', [AdminProfileController::class, 'index'])->name('index'); // Show profile page
    Route::post('/general', [AdminProfileController::class, 'updateGeneral'])->name('general.update'); // Update general info (name, bio, address, phone)
    Route::post('/avatar', [AdminProfileController::class, 'updateAvatar'])->name('avatar.update'); // Upload new avatar
    Route::delete('/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('avatar.delete');  // Remove avatar
    Route::post('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update'); // Change password
    Route::post('/cover', [AdminProfileController::class, 'updateCover'])->name('cover.update');  // Upload cover photo
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
Route::prefix('settings')->name('admin.settings.')->group(function () {
    // General
    Route::get('/general',[GeneralSettingsController::class, 'general'])->name('general');
    Route::patch('/general',[GeneralSettingsController::class, 'updateGeneral'])->name('general.update');

    // Contact
    Route::get('/contact',    [ContactSettingsController::class, 'contact'])->name('contact');
    Route::patch('/contact',  [ContactSettingsController::class, 'updateContact'])->name('contact.update');

    // // Logo & Branding
    Route::get('/logo',       [LogoSettingsController::class, 'logo'])->name('logo');
    Route::post('/logo',      [LogoSettingsController::class, 'updateLogo'])->name('logo.update');

    // // Social Media
    Route::get('/social',     [SocialSettingsController::class, 'social'])->name('social');
    Route::patch('/social',   [SocialSettingsController::class, 'updateSocial'])->name('social.update');

    // // Stripe
    Route::get('/stripe',        [StripeSettingsController::class, 'seo'])->name('seo');
    Route::patch('/stripe',      [StripeSettingsController::class, 'updateSeo'])->name('seo.update');

    // // Mail
    Route::get('/mail',       [MailSettingsController::class, 'mail'])->name('mail');
    Route::patch('/mail',     [MailSettingsController::class, 'updateMail'])->name('mail.update');

    // // Maintenance
    Route::get('/maintenance',   [MaintenanceSettingsController::class, 'maintenance'])->name('maintenance');
    Route::patch('/maintenance', [MaintenanceSettingsController::class, 'updateMaintenance'])->name('maintenance.update');
});
