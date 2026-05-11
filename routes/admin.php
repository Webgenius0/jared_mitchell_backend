<?php

use App\Http\Controllers\Web\Admin\Auth\AdminProfileController;
use App\Http\Controllers\Web\Admin\BusinessSpotlight\AdminBusinessSpotlightController;
use App\Http\Controllers\Web\Admin\Cms\CmsContentController;
use App\Http\Controllers\Web\Admin\Cms\AboutCmsController;
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

/*
|--------------------------------------------------------------------------
| CMS Content (New System)
|--------------------------------------------------------------------------
*/
Route::prefix('cms/content')->name('admin.cms.content.')->group(function () {
    Route::get('/', [CmsContentController::class, 'index'])->name('index');
    Route::post('/hero', [CmsContentController::class, 'updateHero'])->name('update.hero');
    Route::post('/partners', [CmsContentController::class, 'updatePartners'])->name('update.partners');
    Route::post('/features', [CmsContentController::class, 'updateFeatures'])->name('update.features');
    Route::post('/why-choose', [CmsContentController::class, 'updateWhyChoose'])->name('update.why_choose');
    Route::post('/core-values', [CmsContentController::class, 'updateCoreValues'])->name('update.core_values');
    Route::post('/what-you-get', [CmsContentController::class, 'updateWhatYouGet'])->name('update.what_you_get');
    Route::post('/boss-beginnings', [CmsContentController::class, 'updateBossBeginnings'])->name('update.boss_beginnings');
    Route::post('/spotlight', [CmsContentController::class, 'updateSpotlight'])->name('update.spotlight');
    Route::post('/highlights', [CmsContentController::class, 'updateHighlights'])->name('update.highlights');
    Route::post('/events', [CmsContentController::class, 'updateEvents'])->name('update.events');
    Route::post('/shop', [CmsContentController::class, 'updateShop'])->name('update.shop');
    Route::post('/cta', [CmsContentController::class, 'updateCta'])->name('update.cta');
    Route::post('/newsletter', [CmsContentController::class, 'updateNewsletter'])->name('update.newsletter');
});

// About CMS Routes
Route::prefix('cms/about')->name('admin.cms.about.')->group(function () {
    Route::get('/', [AboutCmsController::class, 'index'])->name('index');
    Route::post('/hero', [AboutCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/society', [AboutCmsController::class, 'updateSociety'])->name('update.society');
    Route::post('/origin', [AboutCmsController::class, 'updateOrigin'])->name('update.origin');
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

/*
|--------------------------------------------------------------------------
| Business Spotlights
|--------------------------------------------------------------------------
*/
Route::prefix('business-spotlights')->name('admin.business-spotlights.')->group(function () {
    Route::get('/',                     [AdminBusinessSpotlightController::class, 'index'])->name('index');
    Route::get('/data',                 [AdminBusinessSpotlightController::class, 'getData'])->name('data');
    Route::get('/statistics',           [AdminBusinessSpotlightController::class, 'statistics'])->name('statistics');
    Route::post('/bulk-status',         [AdminBusinessSpotlightController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::get('/{id}',                 [AdminBusinessSpotlightController::class, 'show'])->name('show');
    Route::patch('/{id}/status',        [AdminBusinessSpotlightController::class, 'updateStatus'])->name('status.update');
    Route::post('/{id}/approve',        [AdminBusinessSpotlightController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject',         [AdminBusinessSpotlightController::class, 'reject'])->name('reject');
    Route::post('/{id}/under-review',   [AdminBusinessSpotlightController::class, 'markUnderReview'])->name('under-review');
    Route::delete('/{id}',              [AdminBusinessSpotlightController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore',        [AdminBusinessSpotlightController::class, 'restore'])->name('restore');
});

/*
|--------------------------------------------------------------------------
| Artist Spotlights
|--------------------------------------------------------------------------
*/
Route::prefix('artist-spotlights')->name('admin.artist-spotlights.')->group(function () {
    Route::get('/',                     [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'index'])->name('index');
    Route::get('/data',                 [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'getData'])->name('data');
    Route::get('/statistics',           [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'statistics'])->name('statistics');
    Route::post('/bulk-status',         [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::get('/{id}',                 [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'show'])->name('show');
    Route::patch('/{id}/status',        [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'updateStatus'])->name('status.update');
    Route::post('/{id}/approve',        [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject',         [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'reject'])->name('reject');
    Route::delete('/{id}',              [\App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Artist Categories (CMS)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/artist-categories')->name('admin.artist-categories.')->group(function () {
    Route::get('/',                     [\App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController::class, 'index'])->name('index');
    Route::get('/data',                 [\App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController::class, 'getData'])->name('data');
    Route::post('/',                    [\App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController::class, 'store'])->name('store');
    Route::put('/{category}',           [\App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}',        [\App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Events Management
|--------------------------------------------------------------------------
*/
Route::prefix('events')->name('admin.events.')->group(function () {
    Route::get('/',                     [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'index'])->name('index');
    Route::get('/data',                 [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'getData'])->name('data');
    Route::get('/create',               [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'create'])->name('create');
    Route::post('/',                    [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'store'])->name('store');
    Route::get('/{event}',              [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'show'])->name('show');
    Route::get('/{event}/edit',         [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'edit'])->name('edit');
    Route::put('/{event}',              [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'update'])->name('update');
    Route::delete('/{event}',           [\App\Http\Controllers\Web\Admin\Event\EventController::class, 'destroy'])->name('destroy');
});
