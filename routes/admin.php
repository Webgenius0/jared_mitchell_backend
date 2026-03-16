<?php

use App\Http\Controllers\Web\Admin\Auth\AdminProfileController;
use App\Http\Controllers\Web\Admin\Cms\PageSectionController;
use App\Http\Controllers\Web\Admin\Cms\PricingController;
use App\Http\Controllers\Web\Admin\Contact\AdminChattingController;
use App\Http\Controllers\Web\Admin\Contact\AdminMailingController;
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
Route::prefix('chat')->name('admin.chat.')->group(function () {
    Route::get('/', [AdminChattingController::class, 'index'])->name('index');
});

/*
|--------------------------------------------------------------------------
| Mainling
|--------------------------------------------------------------------------
*/
Route::prefix('mail')->name('admin.mail.')->group(function () {
    Route::get('/', [AdminMailingController::class, 'index'])->name('index');
});

/*
|--------------------------------------------------------------------------
| CMS Pages
|--------------------------------------------------------------------------
*/
Route::prefix('cms/pages')->name('admin.cms.pages.')->group(function () {
    Route::get('/', [PageSectionController::class, 'index'])->name('index');
    Route::post('/', [PageSectionController::class, 'storePage'])->name('store');
    Route::patch('/{page}', [PageSectionController::class, 'updatePage'])->name('update');
    Route::delete('/{page}', [PageSectionController::class, 'destroyPage'])->name('destroy');
    Route::post('/{page}/sections', [PageSectionController::class, 'storeSection'])->name('sections.store');
    Route::patch('/{page}/sections/reorder', [PageSectionController::class, 'reorderSections'])->name('sections.reorder');
    Route::delete('/sections/{section}', [PageSectionController::class, 'destroySection'])->name('sections.destroy');
    Route::patch('/sections/{section}', [PageSectionController::class, 'updateSection'])->name('sections.update');
    Route::post('/sections/{section}/contents', [PageSectionController::class, 'storeContentField'])->name('sections.contents.store');
    Route::patch('/sections/{section}/contents', [PageSectionController::class, 'updateContents'])->name('sections.contents.update');
    Route::delete('/sections/{section}/contents/{content}', [PageSectionController::class, 'destroyContentField'])->name('sections.contents.destroy');
    Route::post('/sections/{section}/media', [PageSectionController::class, 'uploadMedia'])->name('sections.media.upload');
    Route::put('/sections/{section}/items', [PageSectionController::class, 'updateItems'])->name('sections.items.update');
});

// routes/web.php
Route::prefix('cms/pricing')->name('admin.cms.pricing.')->group(function () {
    Route::get('/',                     [PricingController::class, 'index'])->name('index');
    Route::get('/create',               [PricingController::class, 'create'])->name('create');
    Route::post('/',                    [PricingController::class, 'store'])->name('store');
    Route::get('/{plan}/edit',          [PricingController::class, 'edit'])->name('edit');
    Route::put('/{plan}',               [PricingController::class, 'update'])->name('update');
    Route::delete('/{plan}',            [PricingController::class, 'destroy'])->name('destroy');
    Route::post('/reorder',             [PricingController::class, 'reorder'])->name('reorder');
    Route::patch('/{plan}/toggle',      [PricingController::class, 'toggle'])->name('toggle');
});
