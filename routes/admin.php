<?php

use App\Http\Controllers\Web\Admin\Auth\AdminProfileController;
use App\Http\Controllers\Web\Admin\Dashboard\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminDashboardController::class, 'index'])->name('show.admin.dashboard'); // show admin dashboard

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::prefix('profile')->name('admin.profile.')->group(function () {

    // Show profile page
    Route::get('/', [AdminProfileController::class, 'index'])->name('index');

    // Update general info (name, bio, address, phone)
    Route::post('/general', [AdminProfileController::class, 'updateGeneral'])->name('general.update');

    // Upload new avatar
    Route::post('/avatar', [AdminProfileController::class, 'updateAvatar'])->name('avatar.update');

    // Remove avatar
    Route::delete('/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('avatar.delete');

    // Change password
    Route::post('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');

    // Upload cover photo
    Route::post('/cover', [AdminProfileController::class, 'updateCover'])->name('cover.update');
});
