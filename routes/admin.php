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
    Route::get('/', [AdminProfileController::class, 'index'])->name('index'); // Show profile page
    Route::post('/general', [AdminProfileController::class, 'updateGeneral'])->name('general.update'); // Update general info (name, bio, address, phone)
    Route::post('/avatar', [AdminProfileController::class, 'updateAvatar'])->name('avatar.update'); // Upload new avatar
    Route::delete('/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('avatar.delete');  // Remove avatar
    Route::post('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update'); // Change password
    Route::post('/cover', [AdminProfileController::class, 'updateCover'])->name('cover.update');  // Upload cover photo
});

/*
|--------------------------------------------------------------------------
| Chatting
|--------------------------------------------------------------------------
*/
