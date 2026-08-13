<?php

use App\Http\Controllers\Web\Admin\ArtistSpotlight\AdminArtistSpotlightController;
use App\Http\Controllers\Web\Admin\Auth\AdminProfileController;
use App\Http\Controllers\Web\Admin\Business\AdminBusinessController;
use App\Http\Controllers\Web\Admin\BusinessSpotlight\AdminBusinessSpotlightController;
use App\Http\Controllers\Web\Admin\Cms\AboutCmsController;
use App\Http\Controllers\Web\Admin\Cms\AdminArtistCategoryController;
use App\Http\Controllers\Web\Admin\Cms\ArtistSpotlightCmsController;
use App\Http\Controllers\Web\Admin\Cms\BossBeginningsCmsController;
use App\Http\Controllers\Web\Admin\Cms\BossBeginningWinnerChossenCMSController;
use App\Http\Controllers\Web\Admin\Cms\BusinessSpotlightCmsController;
use App\Http\Controllers\Web\Admin\Cms\CmsHomePageController;
use App\Http\Controllers\Web\Admin\Cms\EventCmsController;
use App\Http\Controllers\Web\Admin\Cms\FAQController;
use App\Http\Controllers\Web\Admin\Cms\PricingController;
use App\Http\Controllers\Web\Admin\Cms\RoundCmsController;
use App\Http\Controllers\Web\Admin\Cms\ServiceCmsController;
use App\Http\Controllers\Web\Admin\Cms\ShopCmsController;
use App\Http\Controllers\Web\Admin\Cms\SponsorshipCmsController;
use App\Http\Controllers\Web\Admin\Cms\SpotlightLadderCmsController;
use App\Http\Controllers\Web\Admin\Contact\AdminChattingController;
use App\Http\Controllers\Web\Admin\Contact\AdminMailingController;
use App\Http\Controllers\Web\Admin\ContactController as WebContactController;
use App\Http\Controllers\Web\Admin\ContestApplication\AdminContestApplicationController;
use App\Http\Controllers\Web\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Web\Admin\Event\EventController;
use App\Http\Controllers\Web\Admin\Event\EventRegistrationController;
use App\Http\Controllers\Web\Admin\NewsletterController as WebNewsletterController;
use App\Http\Controllers\Web\Admin\Spotlight\SpotlightApplicationController as WebSpotlightApplicationController;
use App\Http\Controllers\Web\Admin\Spotlight\SpotlightWeekController as WebSpotlightWeekController;
use App\Http\Controllers\Web\Admin\Spotlight\SpotlightVotePurchaseController as WebSpotlightVotePurchaseController;
use App\Http\Controllers\Web\Admin\Order\AdminOrderController;
use App\Http\Controllers\Web\Admin\Product\AdminProductCategoryController;
use App\Http\Controllers\Web\Admin\Product\AdminProductController;
use App\Http\Controllers\Web\Admin\Contest\WinnerController;
use App\Http\Controllers\Web\Admin\Round\RoundSeasonController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\SponsorController;
use App\Http\Controllers\Web\Admin\SponsorApplicationController;
use App\Http\Controllers\Web\Admin\Spotlight\SpotlightVotePackageController;
use App\Http\Controllers\Web\Admin\LiveStreamController;


Route::get('/', [AdminDashboardController::class, 'index'])->name('show.admin.dashboard'); // show admin dashboard

/*
|--------------------------------------------------------------------------
| Profile
|-------------------------------------------------------------------------
*/
Route::prefix('profile')->name('admin.profile.')->group(function () {
    Route::get('/', [AdminProfileController::class, 'index'])->name('index'); // Show profile page
    Route::post('/general', [AdminProfileController::class, 'updateGeneral'])->name('general.update'); // Update general info (name, bio, address, phone)
    Route::post('/avatar', [AdminProfileController::class, 'updateAvatar'])->name('avatar.update'); // Upload new avatar
    Route::delete('/avatar', [AdminProfileController::class, 'deleteAvatar'])->name('avatar.delete');  // Remove avatar
    Route::post('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update'); // Change password
    Route::post('/cover', [AdminProfileController::class, 'updateCover'])->name('cover.update');  // Upload cover photo
});


// Route::prefix('admin')->name('admin.')->middleware(['auth:web'])->group(function () {
Route::resource('live-streams', LiveStreamController::class);
Route::get('live-streams/{id}/broadcast', [LiveStreamController::class, 'broadcast'])->name('live-streams.broadcast');
Route::get('live-streams/{id}/stats', [LiveStreamController::class, 'stats'])->name('live-streams.stats');
Route::post('live-streams/{id}/start-live', [LiveStreamController::class, 'startLive'])->name('live-streams.start-live');
Route::post('live-streams/{id}/end', [LiveStreamController::class, 'endStream'])->name('live-streams.end');
// });


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
| CMS Content (New System)
|--------------------------------------------------------------------------
*/
Route::prefix('cms/content')->name('admin.cms.content.')->group(function () {
    Route::get('/', [CmsHomePageController::class, 'index'])->name('index');
    Route::post('/hero', [CmsHomePageController::class, 'updateHero'])->name('update.hero');
    Route::post('/partners', [CmsHomePageController::class, 'updatePartners'])->name('update.partners');
    Route::post('/static-banner', [CmsHomePageController::class, 'updateStaticBanner'])->name('update.static_banner');
    Route::post('/why-choose', [CmsHomePageController::class, 'updateWhyChoose'])->name('update.why_choose');
    Route::post('/core-values', [CmsHomePageController::class, 'updateCoreValues'])->name('update.core_values');
    Route::post('/what-you-get', [CmsHomePageController::class, 'updateWhatYouGet'])->name('update.what_you_get');
    Route::post('/boss-beginnings', [CmsHomePageController::class, 'updateBossBeginnings'])->name('update.boss_beginnings');
    Route::post('/spotlight', [CmsHomePageController::class, 'updateSpotlight'])->name('update.spotlight');
    Route::post('/boss-beginning-winners', [CmsHomePageController::class, 'updateBossBeginningWinners'])->name('update.boss_beginning_winners');
    Route::post('/artist-spotlight-winners', [CmsHomePageController::class, 'updateArtistSpotlightWinners'])->name('update.artist_spotlight_winners');
    Route::post('/next-boss-beginnings', [CmsHomePageController::class, 'updateNextBossBeginnings'])->name('update.next_boss_beginnings');
    Route::post('/upcoming-events', [CmsHomePageController::class, 'updateUpcomingEvents'])->name('update.upcoming_events');
    Route::post('/past-event-highlights', [CmsHomePageController::class, 'updatePastEventHighlights'])->name('update.past_event_highlights');
    Route::post('/event-sponsors', [CmsHomePageController::class, 'updateEventSponsors'])->name('update.event_sponsors');
    Route::post('/highlights', [CmsHomePageController::class, 'updatePast6MonthsBossBeginningsHighlight'])->name('update.highlights');
    Route::post('/events', [CmsHomePageController::class, 'updateEvents'])->name('update.events');
    Route::post('/shop', [CmsHomePageController::class, 'updateShop'])->name('update.shop');
    Route::post('/cta', [CmsHomePageController::class, 'updateBeApartOfCommunity'])->name('update.cta');
    Route::post('/newsletter', [CmsHomePageController::class, 'updateNewsletter'])->name('update.newsletter');
});

// About CMS Routes
Route::prefix('cms/about')->name('admin.cms.about.')->group(function () {
    Route::get('/', [AboutCmsController::class, 'index'])->name('index');
    Route::post('/hero', [AboutCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/society', [AboutCmsController::class, 'updateSociety'])->name('update.society');
    Route::post('/origin', [AboutCmsController::class, 'updateOrigin'])->name('update.origin');
    Route::post('/mission', [AboutCmsController::class, 'updateMission'])->name('update.mission');
    Route::post('/what-we-do', [AboutCmsController::class, 'updateWhatWeDo'])->name('update.what_we_do');
    Route::post('/how-it-works', [AboutCmsController::class, 'updateHowItWorks'])->name('update.how_it_works');
    Route::post('/who-we-serve', [AboutCmsController::class, 'updateWhoWeServe'])->name('update.who_we_serve');
    Route::post('/why-exists', [AboutCmsController::class, 'updateWhyExists'])->name('update.why_exists');
    Route::post('/our-impact', [AboutCmsController::class, 'updateOurImpact'])->name('update.our_impact');
    Route::post('/founder-message', [AboutCmsController::class, 'updateFounderMessage'])->name('update.founder_message');
    Route::post('/join', [AboutCmsController::class, 'updateJoin'])->name('update.join');
    Route::post('/newsletter', [AboutCmsController::class, 'updateNewsletter'])->name('update.newsletter');
    Route::post('/sponsors', [AboutCmsController::class, 'updateSponsors'])->name('update.sponsors');
});

// Services CMS Routes
Route::prefix('cms/services')->name('admin.cms.services.')->group(function () {
    Route::get('/', [ServiceCmsController::class, 'index'])->name('index');
    Route::post('/hero', [ServiceCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/overview', [ServiceCmsController::class, 'updateOverview'])->name('update.overview');
    Route::post('/grow', [ServiceCmsController::class, 'updateGrow'])->name('update.grow');
    Route::post('/partners', [ServiceCmsController::class, 'updatePartners'])->name('update.partners');
    Route::post('/who-for', [ServiceCmsController::class, 'updateWhoFor'])->name('update.who_for');
    Route::post('/artist-spotlight', [ServiceCmsController::class, 'updateArtistSpotlight'])->name('update.artist_spotlight');
    Route::post('/business-spotlight', [ServiceCmsController::class, 'updateBusinessSpotlight'])->name('update.business_spotlight');
    Route::post('/risk-free', [ServiceCmsController::class, 'updateRiskFree'])->name('update.risk_free');
    Route::post('/newsletter', [ServiceCmsController::class, 'updateNewsletter'])->name('update.newsletter');
    Route::post('/faq', [ServiceCmsController::class, 'updateFaq'])->name('update.faq');
});

// Artist Spotlight CMS Routes
Route::prefix('cms/artist-spotlight')->name('admin.cms.artist_spotlight.')->group(function () {
    Route::get('/', [ArtistSpotlightCmsController::class, 'index'])->name('index');
    Route::post('/hero', [ArtistSpotlightCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/video', [ArtistSpotlightCmsController::class, 'updateVideo'])->name('update.video');
    Route::post('/list', [ArtistSpotlightCmsController::class, 'updateList'])->name('update.list');
    Route::post('/highlights', [ArtistSpotlightCmsController::class, 'updateHighlights'])->name('update.highlights');
    Route::post('/ladder', [ArtistSpotlightCmsController::class, 'updateLadder'])->name('update.ladder');
    Route::post('/join', [ArtistSpotlightCmsController::class, 'updateJoin'])->name('update.join');
    Route::post('/interview', [ArtistSpotlightCmsController::class, 'updateInterview'])->name('update.interview');
    Route::post('/why-exists', [ArtistSpotlightCmsController::class, 'updateWhyExists'])->name('update.why_exists');
});

// Business Spotlight CMS Routes
Route::prefix('cms/business-spotlight')->name('admin.cms.business_spotlight.')->group(function () {
    Route::get('/', [BusinessSpotlightCmsController::class, 'index'])->name('index');
    Route::post('/hero', [BusinessSpotlightCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/video', [BusinessSpotlightCmsController::class, 'updateVideo'])->name('update.video');
    Route::post('/list', [BusinessSpotlightCmsController::class, 'updateList'])->name('update.list');
    Route::post('/highlights', [BusinessSpotlightCmsController::class, 'updateHighlights'])->name('update.highlights');
    Route::post('/picks', [BusinessSpotlightCmsController::class, 'updatePicks'])->name('update.picks');
    Route::post('/ladder', [BusinessSpotlightCmsController::class, 'updateLadder'])->name('update.ladder');
    Route::post('/join', [BusinessSpotlightCmsController::class, 'updateJoin'])->name('update.join');
    Route::post('/interview', [BusinessSpotlightCmsController::class, 'updateInterview'])->name('update.interview');
    Route::post('/why-exists', [BusinessSpotlightCmsController::class, 'updateWhyExists'])->name('update.why_exists');
});

// Spotlight Ladder CMS Routes
Route::prefix('cms/spotlight-ladder')->name('admin.cms.spotlight_ladder.')->group(function () {
    Route::get('/', [SpotlightLadderCmsController::class, 'index'])->name('index');
    Route::post('/hero', [SpotlightLadderCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/details', [SpotlightLadderCmsController::class, 'updateDetails'])->name('update.details');
});

// Event CMS Routes
Route::prefix('cms/event')->name('admin.cms.event.')->group(function () {
    Route::get('/', [EventCmsController::class, 'index'])->name('index');
    Route::post('/hero', [EventCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/video', [EventCmsController::class, 'updateVideo'])->name('update.video');
    Route::post('/host', [EventCmsController::class, 'updateHost'])->name('update.host');
    Route::post('/vendor', [EventCmsController::class, 'updateVendor'])->name('update.vendor');
    Route::post('/booth-features', [EventCmsController::class, 'updateBoothFeatures'])->name('update.booth_features');
    Route::post('/upcoming-event1', [EventCmsController::class, 'updateUpcomingEvent1'])->name('update.upcoming_event1');
    Route::post('/upcoming-event2', [EventCmsController::class, 'updateUpcomingEvent2'])->name('update.upcoming_event2');
    Route::post('/event-gallery', [EventCmsController::class, 'updateEventGallery'])->name('update.event_gallery');
    Route::post('/past-event-highlights', [EventCmsController::class, 'updatePastEventHighlights'])->name('update.past_event_highlights');
});

// Shop CMS Routes
Route::prefix('cms/shop')->name('admin.cms.shop.')->group(function () {
    Route::get('/', [ShopCmsController::class, 'index'])->name('index');
    Route::post('/hero', [ShopCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/features', [ShopCmsController::class, 'updateFeatures'])->name('update.features');
    Route::post('/support', [ShopCmsController::class, 'updateSupport'])->name('update.support');
    Route::post('/footer-features', [ShopCmsController::class, 'updateFooterFeatures'])->name('update.footer_features');
    Route::post('/featured', [ShopCmsController::class, 'updateFeatured'])->name('update.featured');
    Route::post('/limited-drops', [ShopCmsController::class, 'updateLimitedDrops'])->name('update.limited_drops');
    Route::post('/faq', [ShopCmsController::class, 'updateFaq'])->name('update.faq');
});

/*
|--------------------------------------------------------------------------
| Sponsors Management
|--------------------------------------------------------------------------
*/
Route::prefix('sponsors')->name('admin.sponsors.')->group(function () {
    Route::get('/', [SponsorController::class, 'index'])->name('index');
    Route::get('/data', [SponsorController::class, 'getData'])->name('data');
    Route::post('/', [SponsorController::class, 'store'])->name('store');
    Route::put('/{sponsor}', [SponsorController::class, 'update'])->name('update');
    Route::patch('/{sponsor}/toggle-status', [SponsorController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{sponsor}', [SponsorController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Sponsor Applications Management
|--------------------------------------------------------------------------
*/
Route::prefix('sponsor-applications')->name('admin.sponsor-applications.')->group(function () {
    Route::get('/', [SponsorApplicationController::class, 'index'])->name('index');
    Route::get('/data', [SponsorApplicationController::class, 'getData'])->name('data');
    Route::delete('/{sponsorApplication}', [SponsorApplicationController::class, 'destroy'])->name('destroy');
});

// Sponsorship CMS Routes
Route::prefix('cms/sponsorship')->name('admin.cms.sponsorship.')->group(function () {
    Route::get('/', [SponsorshipCmsController::class, 'index'])->name('index');
    Route::post('/hero', [SponsorshipCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/video', [SponsorshipCmsController::class, 'updateVideo'])->name('update.video');
    Route::post('/why', [SponsorshipCmsController::class, 'updateWhy'])->name('update.why');
    Route::post('/steps', [SponsorshipCmsController::class, 'updateSteps'])->name('update.steps');
    Route::post('/levels-header', [SponsorshipCmsController::class, 'updateLevelsHeader'])->name('update.levels_header');
    Route::post('/footer', [SponsorshipCmsController::class, 'updateFooter'])->name('update.footer');
});
// Boss Beginnings CMS Routes
Route::prefix('cms/boss-beginnings')->name('admin.cms.boss-beginnings.')->group(function () {
    Route::get('/', [BossBeginningsCmsController::class, 'index'])->name('index');
    Route::post('/hero', [BossBeginningsCmsController::class, 'updateHero'])->name('update.hero');
    Route::post('/features', [BossBeginningsCmsController::class, 'updateFeatures'])->name('update.features');
    Route::post('/video-gallery', [BossBeginningsCmsController::class, 'updateVideoGallery'])->name('update.video_gallery');
    Route::post('/steps', [BossBeginningsCmsController::class, 'updateSteps'])->name('update.steps');
    Route::post('/section5', [BossBeginningsCmsController::class, 'updateSection5'])->name('update.section5');
    Route::post('/dynamic', [BossBeginningsCmsController::class, 'updateDynamicSection'])->name('update.dynamic');
});

//Boss Beginnings CMS Routes how winner will chosen
Route::prefix('cms/winner-chosen')->name('admin.cms.winner-chosen.')->group(function () {
    Route::get('/', [BossBeginningWinnerChossenCMSController::class, 'index'])->name('index');
    Route::post('/update-section1', [BossBeginningWinnerChossenCMSController::class, 'updateSection1'])->name('update.section1');
    Route::post('/update-section2', [BossBeginningWinnerChossenCMSController::class, 'updateSection2'])->name('update.section2');
});

// Round CMS Routes
Route::prefix('cms/rounds')->name('admin.cms.rounds.')->group(function () {
    Route::get('/', [RoundCmsController::class, 'index'])->name('index');
    Route::post('/', [RoundCmsController::class, 'updateRounds'])->name('update');
});

/*
|--------------------------------------------------------------------------
| Pricing plan Management
|--------------------------------------------------------------------------
*/
Route::prefix('pricing')->name('admin.pricing.')->group(function () {
    Route::get('/', [PricingController::class, 'index'])->name('index');
    Route::get('/create', [PricingController::class, 'create'])->name('create');
    Route::post('/', [PricingController::class, 'store'])->name('store');
    Route::get('/{plan}/edit', [PricingController::class, 'edit'])->name('edit');
    Route::put('/{plan}', [PricingController::class, 'update'])->name('update');
    Route::delete('/{plan}', [PricingController::class, 'destroy'])->name('destroy');
    Route::post('/reorder', [PricingController::class, 'reorder'])->name('reorder');
    Route::patch('/{plan}/toggle', [PricingController::class, 'toggle'])->name('toggle');
});

/*
|--------------------------------------------------------------------------
| FAQ Management
|--------------------------------------------------------------------------
*/
Route::prefix('cms/faq')->name('admin.cms.faq.')->group(function () {
    Route::get('/', [FAQController::class, 'index'])->name('index');
    Route::get('/create', [FAQController::class, 'create'])->name('create');
    Route::post('/', [FAQController::class, 'store'])->name('store');
    Route::get('/{faq}/edit', [FAQController::class, 'edit'])->name('edit');
    Route::put('/{faq}', [FAQController::class, 'update'])->name('update');
    Route::delete('/{faq}', [FAQController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Business Spotlights
|--------------------------------------------------------------------------
*/
Route::prefix('business-spotlights')->name('admin.business-spotlights.')->group(function () {
    Route::get('/', [AdminBusinessSpotlightController::class, 'index'])->name('index');
    Route::get('/data', [AdminBusinessSpotlightController::class, 'getData'])->name('data');
    Route::get('/statistics', [AdminBusinessSpotlightController::class, 'statistics'])->name('statistics');
    Route::post('/bulk-status', [AdminBusinessSpotlightController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::get('/{id}', [AdminBusinessSpotlightController::class, 'show'])->name('show');
    Route::patch('/{id}/status', [AdminBusinessSpotlightController::class, 'updateStatus'])->name('status.update');
    Route::post('/{id}/approve', [AdminBusinessSpotlightController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [AdminBusinessSpotlightController::class, 'reject'])->name('reject');
    Route::post('/{id}/under-review', [AdminBusinessSpotlightController::class, 'markUnderReview'])->name('under-review');
    Route::delete('/{id}', [AdminBusinessSpotlightController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [AdminBusinessSpotlightController::class, 'restore'])->name('restore');
});

/*
|--------------------------------------------------------------------------
| Artist Spotlights
|--------------------------------------------------------------------------
*/
Route::prefix('artist-spotlights')->name('admin.artist-spotlights.')->group(function () {
    Route::get('/', [AdminArtistSpotlightController::class, 'index'])->name('index');
    Route::get('/data', [AdminArtistSpotlightController::class, 'getData'])->name('data');
    Route::get('/statistics', [AdminArtistSpotlightController::class, 'statistics'])->name('statistics');
    Route::post('/bulk-status', [AdminArtistSpotlightController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::get('/{id}', [AdminArtistSpotlightController::class, 'show'])->name('show');
    Route::patch('/{id}/status', [AdminArtistSpotlightController::class, 'updateStatus'])->name('status.update');
    Route::post('/{id}/approve', [AdminArtistSpotlightController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [AdminArtistSpotlightController::class, 'reject'])->name('reject');
    Route::delete('/{id}', [AdminArtistSpotlightController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Artist Categories
|--------------------------------------------------------------------------
*/
Route::prefix('artist-categories')->name('admin.artist-categories.')->group(function () {
    Route::get('/', [AdminArtistCategoryController::class, 'index'])->name('index');
    Route::get('/data', [AdminArtistCategoryController::class, 'getData'])->name('data');
    Route::post('/', [AdminArtistCategoryController::class, 'store'])->name('store');
    Route::put('/{category}', [AdminArtistCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [AdminArtistCategoryController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Event Registrations (Registered Events)
|--------------------------------------------------------------------------
*/
Route::prefix('events/registrations')->name('admin.events.registrations.')->group(function () {
    Route::get('/', [EventRegistrationController::class, 'index'])->name('index');
    Route::get('/data', [EventRegistrationController::class, 'getData'])->name('data');
    Route::get('/export/csv', [EventRegistrationController::class, 'export'])->name('export.csv');
    Route::get('/export/excel', [EventRegistrationController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [EventRegistrationController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/{registration}', [EventRegistrationController::class, 'show'])->name('show');
    Route::post('/{registration}/status', [EventRegistrationController::class, 'updateStatus'])->name('status.update');
    Route::post('/{registration}/payment-status', [EventRegistrationController::class, 'updatePaymentStatus'])->name('payment-status.update');
});

/*
|--------------------------------------------------------------------------
| Events Management
|--------------------------------------------------------------------------
*/
Route::prefix('events')->name('admin.events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/data', [EventController::class, 'getData'])->name('data');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/', [EventController::class, 'store'])->name('store');
    Route::get('/export/csv', [EventController::class, 'export'])->name('export.csv');
    Route::get('/export/excel', [EventController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [EventController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/{event}/review', [EventController::class, 'review'])->name('review');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [EventController::class, 'update'])->name('update');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Businesses Management
|--------------------------------------------------------------------------
*/
Route::prefix('businesses')->name('admin.businesses.')->group(function () {
    Route::get('/', [AdminBusinessController::class, 'index'])->name('index');
    Route::get('/{business}', [AdminBusinessController::class, 'show'])->name('show');
    Route::patch('/{business}/toggle-status', [AdminBusinessController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{business}', [AdminBusinessController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Round Sessions
|--------------------------------------------------------------------------
*/
Route::prefix('round-sessions')->name('admin.round-sessions.')->group(function () {
    Route::get('/', [RoundSeasonController::class, 'index'])->name('index');
    Route::get('/create', [RoundSeasonController::class, 'create'])->name('create');
    Route::post('/', [RoundSeasonController::class, 'store'])->name('store');
    Route::get('/{season}/edit', [RoundSeasonController::class, 'edit'])->name('edit');
    Route::put('/{season}', [RoundSeasonController::class, 'update'])->name('update');
    Route::patch('/{season}/toggle-active', [RoundSeasonController::class, 'toggleActive'])->name('toggle-active');
    Route::delete('/{season}', [RoundSeasonController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Winners (final-round top 3 + admin winner confirmation)
|--------------------------------------------------------------------------
*/
Route::prefix('winners')->name('admin.winners.')->group(function () {
    Route::get('/', [WinnerController::class, 'index'])->name('index');
    Route::get('/rounds', [WinnerController::class, 'rounds'])->name('rounds');
    Route::post('/{round}/confirm-winner', [WinnerController::class, 'confirmWinner'])->name('confirm-winner');
});

/*
|--------------------------------------------------------------------------
| Contest Applications
|--------------------------------------------------------------------------
*/
Route::prefix('contest-applications')->name('admin.contest-applications.')->group(function () {
    Route::get('/', [AdminContestApplicationController::class, 'index'])->name('index');
    Route::get('/export/csv', [AdminContestApplicationController::class, 'export'])->name('export.csv');
    Route::get('/export/excel', [AdminContestApplicationController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [AdminContestApplicationController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/rounds-by-season/{season}', [AdminContestApplicationController::class, 'roundsBySeason'])->name('rounds-by-season');
    Route::get('/{contestApplication}', [AdminContestApplicationController::class, 'show'])->name('show');
    Route::post('/bulk-status', [AdminContestApplicationController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::post('/bulk-destroy', [AdminContestApplicationController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::patch('/{contestApplication}/status', [AdminContestApplicationController::class, 'updateStatus'])->name('status.update');
    Route::delete('/{contestApplication}', [AdminContestApplicationController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Products Management
|--------------------------------------------------------------------------
*/
Route::prefix('products')->name('admin.products.')->group(function () {
    Route::get('/', [AdminProductController::class, 'index'])->name('index');
    Route::get('/data', [AdminProductController::class, 'getData'])->name('data');
    Route::get('/create', [AdminProductController::class, 'create'])->name('create');
    Route::post('/', [AdminProductController::class, 'store'])->name('store');
    Route::get('/{product}', [AdminProductController::class, 'show'])->name('show');
    Route::get('/{product}/edit', [AdminProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [AdminProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Orders Management
|--------------------------------------------------------------------------
*/
Route::prefix('orders')->name('admin.orders.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::get('/data', [AdminOrderController::class, 'getData'])->name('data');
    Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
    Route::post('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('status.update');
    Route::post('/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('payment-status.update');
    Route::post('/{order}/refund', [AdminOrderController::class, 'refund'])->name('refund');
    Route::delete('/{order}', [AdminOrderController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Product Categories
|--------------------------------------------------------------------------
*/
Route::prefix('product-categories')->name('admin.product-categories.')->group(function () {
    Route::get('/', [AdminProductCategoryController::class, 'index'])->name('index');
    Route::get('/data', [AdminProductCategoryController::class, 'getData'])->name('data');
    Route::post('/', [AdminProductCategoryController::class, 'store'])->name('store');
    Route::put('/{category}', [AdminProductCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [AdminProductCategoryController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Contact Messages
|--------------------------------------------------------------------------
*/
Route::prefix('contacts')->name('admin.contacts.')->group(function () {
    Route::get('/', [WebContactController::class, 'index'])->name('index');
    Route::patch('/{contact}/read', [WebContactController::class, 'markAsRead'])->name('read');
    Route::delete('/{contact}', [WebContactController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Newsletter Subscriptions
|--------------------------------------------------------------------------
*/
Route::prefix('newsletters')->name('admin.newsletters.')->group(function () {
    Route::get('/', [WebNewsletterController::class, 'index'])->name('index');
    Route::delete('/{newsletter}', [WebNewsletterController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Spotlight Voting — Week Management & Vote Purchases
|--------------------------------------------------------------------------
*/
Route::prefix('spotlight')->name('admin.spotlight.')->group(function () {
    // Week Management
    Route::get('/weeks', [WebSpotlightWeekController::class, 'index'])->name('weeks.index');
    Route::post('/weeks', [WebSpotlightWeekController::class, 'store'])->name('weeks.store');
    Route::get('/weeks/{week}/edit', [WebSpotlightWeekController::class, 'edit'])->name('weeks.edit');
    Route::put('/weeks/{week}', [WebSpotlightWeekController::class, 'update'])->name('weeks.update');
    Route::get('/weeks/{week}', [WebSpotlightWeekController::class, 'show'])->name('weeks.show');
    Route::get('/weeks/{week}/applications', [WebSpotlightWeekController::class, 'applications'])->name('weeks.applications');
    Route::post('/weeks/{week}/select-nominees', [WebSpotlightWeekController::class, 'selectNominees'])->name('weeks.select-nominees');
    Route::post('/weeks/{week}/close-voting', [WebSpotlightWeekController::class, 'closeVoting'])->name('weeks.close-voting');
    Route::post('/weeks/{week}/announce-winner', [WebSpotlightWeekController::class, 'announceWinner'])->name('weeks.announce-winner');
    Route::patch('/weeks/{week}/status', [WebSpotlightWeekController::class, 'updateStatus'])->name('weeks.update-status');
    Route::patch('/weeks/{week}/cancel', [WebSpotlightWeekController::class, 'cancel'])->name('weeks.cancel');
    Route::delete('/weeks/{week}', [WebSpotlightWeekController::class, 'destroy'])->name('weeks.destroy');

    // Application Management (all weeks)
    Route::get('/applications', [WebSpotlightApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/bulk-approve', [WebSpotlightApplicationController::class, 'bulkApprove'])->name('applications.bulk-approve');
    Route::post('/applications/{application}/ai-review', [WebSpotlightApplicationController::class, 'runAiReview'])->name('applications.ai-review');
    Route::get('/applications/{application}', [WebSpotlightApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [WebSpotlightApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/update-status', [WebSpotlightApplicationController::class, 'updateStatus'])->name('applications.update-status');

    // Vote Purchase Management
    Route::get('/vote-purchases', [WebSpotlightVotePurchaseController::class, 'index'])->name('vote-purchases.index');
    Route::get('/vote-purchases/pending-count', [WebSpotlightVotePurchaseController::class, 'pendingCount'])->name('vote-purchases.pending-count');
    Route::get('/vote-purchases/{purchase}', [WebSpotlightVotePurchaseController::class, 'show'])->name('vote-purchases.show');
    Route::post('/vote-purchases/{purchase}/approve', [WebSpotlightVotePurchaseController::class, 'approve'])->name('vote-purchases.approve');
    Route::post('/vote-purchases/{purchase}/refund', [WebSpotlightVotePurchaseController::class, 'refund'])->name('vote-purchases.refund');

    // Vote Package Management
    Route::get('/vote-packages', [SpotlightVotePackageController::class, 'index'])->name('vote-packages.index');
    Route::get('/vote-packages/create', [SpotlightVotePackageController::class, 'create'])->name('vote-packages.create');
    Route::post('/vote-packages', [SpotlightVotePackageController::class, 'store'])->name('vote-packages.store');
    Route::get('/vote-packages/{package}/edit', [SpotlightVotePackageController::class, 'edit'])->name('vote-packages.edit');
    Route::put('/vote-packages/{package}', [SpotlightVotePackageController::class, 'update'])->name('vote-packages.update');
    Route::post('/vote-packages/{package}/toggle-active', [SpotlightVotePackageController::class, 'toggleActive'])->name('vote-packages.toggle-active');
    Route::delete('/vote-packages/{package}', [SpotlightVotePackageController::class, 'destroy'])->name('vote-packages.destroy');
});
