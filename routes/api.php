<?php

use App\Http\Controllers\Api\ArtistCategoryController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\ArtistSpotlightController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\V2\ForgotPasswordController as V2ForgotPasswordController;
use App\Http\Controllers\Api\Auth\V2\RegisterController as V2RegisterController;
use App\Http\Controllers\Api\BusinessSpotlightController;
use App\Http\Controllers\Api\Chat\ConversationController;
use App\Http\Controllers\Api\Chat\MessageController;
use App\Http\Controllers\Api\Chat\TypingController;
use App\Http\Controllers\Api\Cms\CmsAboutController;
use App\Http\Controllers\Api\Cms\CmsArtistSpotlightController;
use App\Http\Controllers\Api\Cms\CmsBusinessSpotlightController;
use App\Http\Controllers\Api\Cms\CmsHomePageController;
use App\Http\Controllers\Api\Cms\CmsPricingController;
use App\Http\Controllers\Api\Cms\CmsServiceController;
use App\Http\Controllers\Api\Cms\CmsSpotlightLadderController;
use App\Http\Controllers\Api\Cms\FAQController as ApiFAQController;
use App\Http\Controllers\Api\EventController;
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
| V1 Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1'], function ($router) {
    /*
    |--------------------------------------------------------------------------
    | User Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'guest:api'], function () {
        //register
        Route::post('/register', [RegisterController::class, 'register']); // DONE: user registraion
        Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']); // DONE: email verification
        Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']); // DONE: resend otp

        //login
        Route::post('/login', [LoginController::class, 'login']); // DONE: user login

        //forgot password
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']); // DONE: send forgot password otp
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']); // DONE: verify forgot password otp
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']); // DONE: Reset password
    });

    /*
    |--------------------------------------------------------------------------
    | CMS — Public read-only routes (no auth required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('cms')->group(function () {
        Route::get('/pricing', [CmsPricingController::class, 'index']); // Visible pricing plans with feature groups
        Route::get('/homepage', [CmsHomePageController::class, 'index']); // Homepage CMS
        Route::get('/about', [CmsAboutController::class, 'index']); // About page CMS
        Route::get('/services', [CmsServiceController::class, 'index']); // Services page CMS
        Route::get('/artist-spotlight', [CmsArtistSpotlightController::class, 'index']); // Artist spotlight page CMS
        Route::get('/business-spotlight', [CmsBusinessSpotlightController::class, 'index']); // Business spotlight page CMS
        Route::get('/spotlight-ladder', [CmsSpotlightLadderController::class, 'index']); // Spotlight ladder page CMS
        Route::get('/faq', [ApiFAQController::class, 'index']); // Active FAQs
    });

    /*
    |--------------------------------------------------------------------------
    | Business Spotlight — Public form submission (no auth required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('business-spotlight')->group(function () {
        Route::get('/', [BusinessSpotlightController::class, 'index']);
        Route::post('/', [BusinessSpotlightController::class, 'store']); // Submit complete form
        Route::post('/draft', [BusinessSpotlightController::class, 'saveDraft']); // Save draft (partial)
        Route::get('/draft', [BusinessSpotlightController::class, 'getDraft']);  // Retrieve draft by email
        Route::get('/{id}', [BusinessSpotlightController::class, 'show']);  // Retrieve draft by email
    });

    /*
    |--------------------------------------------------------------------------
    | Artist Spotlight — Public form submission
    |--------------------------------------------------------------------------
    | Includes categories listing and spotlight submission/drafts.
    */
    Route::get('/artist-categories', [ArtistCategoryController::class, 'index']);

    Route::prefix('artist-spotlight')->group(function () {
        Route::get('/', [ArtistSpotlightController::class, 'index']);     // Submit complete form
        Route::post('/', [ArtistSpotlightController::class, 'store']);     // Submit complete form
        Route::post('/draft', [ArtistSpotlightController::class, 'saveDraft']); // Save draft (partial)
        Route::get('/draft', [ArtistSpotlightController::class, 'getDraft']);  // Retrieve draft by email
    });

    /*
    |--------------------------------------------------------------------------
    | Events — Public listing and registration
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']); // List all
        Route::get('/{slug}', [EventController::class, 'show'])->middleware('auth:api'); // Detail
        Route::get('/{slug}/attendees', [EventController::class, 'attendees'])->middleware('auth:api'); // Attendees list
        Route::post('/register', [EventController::class, 'register'])->middleware('auth:api'); // Register
    });

    /*
    |--------------------------------------------------------------------------
    | Artists — Public listing and profile
    |--------------------------------------------------------------------------
    */
    Route::prefix('artists')->group(function () {
        Route::get('/', [ArtistController::class, 'index']);    // List all artists
        Route::get('/{id}', [ArtistController::class, 'show']); // Artist detail
        Route::post('/{id}/share', [ArtistController::class, 'recordShare']); // Public share (optional auth)
    });

    /*
    |--------------------------------------------------------------------------
    | User Profile and After Authentication
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'auth:api'], function ($router) {
        Route::post('/refresh-token', [LoginController::class, 'refreshToken']); // DONE: refresh token
        Route::post('/logout', [LoginController::class, 'logout']); // DONE: logout

        Route::get('/profile', [UserProfileController::class, 'profile']); // DONE: user profile
        Route::post('/update-profile', [UserProfileController::class, 'updateProfile']); // DONE: update profile
        Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']); // DONE: update avatar
        Route::delete('/delete-profile', [UserProfileController::class, 'destroy']); // DONE: delete profile
        Route::post('/change-password', [UserProfileController::class, 'changePassword']); // DONE: change password

        // Artist interactions
        Route::prefix('artists')->group(function () {
            Route::post('/{id}/like', [ArtistController::class, 'toggleLike']);
            Route::post('/{id}/bookmark', [ArtistController::class, 'toggleBookmark']);
        });

        // Event interactions
        Route::prefix('events')->group(function () {
            Route::post('/{id}/like', [EventController::class, 'toggleLike']);
            Route::post('/{id}/bookmark', [EventController::class, 'toggleBookmark']);
            Route::post('/{id}/share', [EventController::class, 'recordShare']);
        });

        // Business Spotlight interactions
        Route::prefix('business-spotlight')->group(function () {
            Route::post('/{id}/like', [BusinessSpotlightController::class, 'toggleLike']);
            Route::post('/{id}/bookmark', [BusinessSpotlightController::class, 'toggleBookmark']);
            Route::post('/{id}/share', [BusinessSpotlightController::class, 'recordShare']);
        });

        // Artist Spotlight interactions
        Route::prefix('artist-spotlight')->group(function () {
            Route::post('/{id}/like', [ArtistSpotlightController::class, 'toggleLike']);
            Route::post('/{id}/bookmark', [ArtistSpotlightController::class, 'toggleBookmark']);
            Route::post('/{id}/share', [ArtistSpotlightController::class, 'recordShare']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Messaging/Conversation
    |--------------------------------------------------------------------------
    */
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::post('/', [ConversationController::class, 'store']);
        Route::get('/{conversation}', [ConversationController::class, 'show']);
        Route::post('/{conversation}', [ConversationController::class, 'update']);
        Route::delete('/{conversation}', [ConversationController::class, 'destroy']);

        // Group management
        Route::post('/{conversation}/add-user', [ConversationController::class, 'addUser']);
        Route::post('/{conversation}/remove-user', [ConversationController::class, 'removeUser']);
        Route::post('/{conversation}/make-admin', [ConversationController::class, 'makeAdmin']);

        // Conversation settings
        Route::post('/{conversation}/toggle-mute', [ConversationController::class, 'toggleMute']);
        Route::post('/{conversation}/toggle-archive', [ConversationController::class, 'toggleArchive']);

        // Messages in conversation
        Route::get('/{conversation}/messages', [MessageController::class, 'index']);

        // Typing indicators
        Route::post('/{conversation}/typing', [TypingController::class, 'typing']);
        Route::post('/{conversation}/stop-typing', [TypingController::class, 'stopTyping']);
        Route::get('/{conversation}/typing-users', [TypingController::class, 'getCurrentlyTyping']);
    });

    // Message routes
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'store']);
        Route::get('/unread-count', [MessageController::class, 'unreadCount']);
        Route::post('/mark-as-read', [MessageController::class, 'markAsRead']);
        Route::get('/{message}', [MessageController::class, 'show']);
        Route::put('/{message}', [MessageController::class, 'update']);
        Route::delete('/{message}', [MessageController::class, 'destroy']);
        Route::post('/{message}/reaction', [MessageController::class, 'toggleReaction']);
    });
});


/*
|--------------------------------------------------------------------------
| API V2 — Authentication Routes (link-based, no OTP)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v2'], function () {
    Route::group(['middleware' => 'guest:api'], function () {

        // ── Registration ──────────────────────────────────────────────────────
        Route::post('/register', [V2RegisterController::class, 'register']); // DONE: user registration
        Route::get('/verify-email', [V2RegisterController::class, 'verifyEmail']); // DONE: otp verification
        Route::post('/resend-verification', [V2RegisterController::class, 'resendVerification']); // DONE: resend verification token

        // ── Login (reuse v1 LoginController) ─────────────────────────────────
        Route::post('/login', [LoginController::class, 'login']); // DONE: user login

        // ── Forgot Password ───────────────────────────────────────────────────
        Route::post('/forgot-password', [V2ForgotPasswordController::class, 'sendResetLink']); // DONE: forgot password
        Route::get('/verify-reset-token', [V2ForgotPasswordController::class, 'verifyResetToken']); // DONE: verify password reset token
        Route::post('/reset-password', [V2ForgotPasswordController::class, 'resetPassword']); // DONE: Set new password
    });

    Route::group(['middleware' => 'auth:api'], function () {
        // Reuse v1 protected routes as-is or add v2-specific ones here
        Route::post('/refresh-token', [LoginController::class, 'refreshToken']); // DONE: refresh token
        Route::post('/logout', [LoginController::class, 'logout']); // DONE: logout
    });
});

// Legacy alias — now delegates to the proper CMS controller
Route::get('/pricing-plans', [CmsPricingController::class, 'index']);
