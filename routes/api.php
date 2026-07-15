<?php

use App\Http\Controllers\Api\ArtistCategoryController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\ArtistSpotlightController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\V1\ArtistProfileController;
use App\Http\Controllers\Api\Auth\V1\BusinessProfileController;
use App\Http\Controllers\Api\Auth\V1\MemberProfileController;
use App\Http\Controllers\Api\Auth\V1\SponsorProfileController;
use App\Http\Controllers\Api\Auth\V2\ForgotPasswordController as V2ForgotPasswordController;
use App\Http\Controllers\Api\Auth\V2\RegisterController as V2RegisterController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\BusinessSpotlightController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\Chat\ConversationController;
use App\Http\Controllers\Api\Chat\MessageController;
use App\Http\Controllers\Api\Chat\TypingController;
use App\Http\Controllers\Api\Cms\BossBeginingsController;
use App\Http\Controllers\Api\Cms\BossWinnerChosenController;
use App\Http\Controllers\Api\Cms\CmsAboutController;
use App\Http\Controllers\Api\Cms\CmsArtistSpotlightController;
use App\Http\Controllers\Api\Cms\CmsBusinessSpotlightController;
use App\Http\Controllers\Api\Cms\CmsHomePageController;
use App\Http\Controllers\Api\Cms\CmsPricingController;
use App\Http\Controllers\Api\Cms\CmsServiceController;
use App\Http\Controllers\Api\Cms\CmsSpotlightLadderController;
use App\Http\Controllers\Api\Cms\EventController as CmsEventController;
use App\Http\Controllers\Api\Cms\FAQController as ApiFAQController;
use App\Http\Controllers\Api\Cms\ShopController;
use App\Http\Controllers\Api\Cms\SponsorsipController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Contest\LeaderboardController;
use App\Http\Controllers\Api\Contest\RoundSubmissionController;
use App\Http\Controllers\Api\Contest\VoteController;
use App\Http\Controllers\Api\ContestApplicationController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FeaturedEventController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoundSessionApiController;
use App\Http\Controllers\Api\SpotlightController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\WishlistController;
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
    | Public route
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => 'guest:api'], function ($router) {
        /*
        |--------------------------------------------------------------------------
        | User Authentication Routes
        |--------------------------------------------------------------------------
        */
        Route::group([], function () {
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
            Route::get('/boss-beginnings', [BossBeginingsController::class, 'index']); // Boss Beginnings page CMS
            Route::get('/boss-beginnings-winner', [BossWinnerChosenController::class, 'index']); // Boss Beginnings Winner Chosen page CMS
            Route::get('/events', [CmsEventController::class, 'index']); // Events page CMS
            Route::get('/shop', [ShopController::class, 'index']); // Shop page CMS
            Route::get('/sponsorsip', [SponsorsipController::class, 'index']); // Sponsorship page CMS
            Route::get('/faq', [ApiFAQController::class, 'index']); // Active FAQs
        });

        // Artist Categories - for artist registering
        Route::get('/artist-categories', [ArtistCategoryController::class, 'index']); // DONE: artist categories

        /*
        |--------------------------------------------------------------------------
        | Events — Public listing and registration
        |--------------------------------------------------------------------------
        */
        Route::prefix('events')->group(function () {
            Route::get('/', [EventController::class, 'index']); // DONE: List all events (public)
            Route::get('/upcomming-events', [EventController::class, 'upcommingEvents']); // DONE: Upcoming events(from today onwards)
            Route::get('/calendar-views', [EventController::class, 'eventCalaenderView']); // DONE : event calender view for Event Calendar Page
            Route::get('/featured', [FeaturedEventController::class, 'index']);
            Route::get('/galary', [EventController::class, 'galary']); // DONE: Event Galary (all images from event media)
            Route::get('/past-events', [EventController::class, 'pastEvents']); // DONE: Past events (Ends before today)
            Route::get('/{slug}', [EventController::class, 'show']); // DONE: Event Detail (public)
            // Route::get('/{slug}/attendees', [EventController::class, 'attendees']); // Attendees list
        });

        // Contact Us
        Route::post('/contact', [ContactController::class, 'store']);

        // Newsletter
        Route::post('/newsletter', [NewsletterController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | Products — Public listing and detail
        |--------------------------------------------------------------------------
        */
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{slug}', [ProductController::class, 'show']);

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
        | Spotlights (Accessible by both guest and authenticated users)
        |--------------------------------------------------------------------------
        */
        Route::get('/spotlight', [SpotlightController::class, 'index']);
        Route::get('/round-countdown', [RoundSessionApiController::class, 'countdown']);

        /*
        |--------------------------------------------------------------------------
        | Pricing (Accessible by both guest and authenticated users)
        |--------------------------------------------------------------------------
        */
        Route::get('/pricing', [PricingController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Contest — Voting, Submissions & Leaderboard
        |--------------------------------------------------------------------------
        */
        Route::prefix('contest')->group(function () {
            // Seasons → Leaderboard
            Route::get('/leaderboard/overall', [LeaderboardController::class, 'activeOverall']);
            Route::get('/seasons/{season}/leaderboard', [LeaderboardController::class, 'overall']);
            Route::get('/seasons/{season}/leaderboard/calculate', [LeaderboardController::class, 'recalculate']);

            // Rounds → Submissions, Votes, Leaderboard
            Route::get('/rounds/{round}/submissions', [RoundSubmissionController::class, 'index']);
            Route::get('/rounds/{round}/leaderboard', [LeaderboardController::class, 'forRound']);
            Route::get('/rounds/{round}/votes/counts', [VoteController::class, 'counts']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Public routes (Accessible by both guest and auth)
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->group(function () {
        Route::post('/register', [EventController::class, 'register']); // Register (Guest & Auth)
    });


    /*
    |--------------------------------------------------------------------------
    | Private route
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

        /*
        |--------------------------------------------------------------------------
        | Artist Route
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:artist,api')->prefix('')->group(function () {
            // Artist profile update
            Route::group(['prefix' => 'artist'], function () {
                Route::post('/profile/store', [ArtistProfileController::class, 'store']);
                Route::post('/profile/update', [ArtistProfileController::class, 'update']);
            });

            // Artist Spotlight
            Route::group(['prefix' => 'artist-spotlight'], function () {
                Route::get('/', [ArtistSpotlightController::class, 'index']); // List all
                Route::post('/', [ArtistSpotlightController::class, 'store']); // Submit complete form
                Route::post('/draft', [ArtistSpotlightController::class, 'saveDraft']); // Save draft (partial)
                Route::get('/draft', [ArtistSpotlightController::class, 'getDraft']); // Retrieve draft by email
                Route::get('/{id}', [ArtistSpotlightController::class, 'show']); // Get single spotlight
                Route::post('/update/{id}', [ArtistSpotlightController::class, 'update']); // Full update (owner only)
                Route::delete('/delete/{id}', [ArtistSpotlightController::class, 'destroy']); // Delete (owner only)
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Businesses/Boss Route (protected by boss role) - Business Dashboard
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:boss,api')->group(function () {
            // Profile manage
            Route::group(['prefix' => 'businesses'], function () {
                Route::post('/profile/store', [BusinessProfileController::class, 'store']);
                Route::post('/profile/update', [BusinessProfileController::class, 'update']);
            });

            // Business management
            Route::group(['prefix' => 'businesses'], function () {
                Route::get('/list', [BusinessController::class, 'index']); // DONE: List all
                Route::post('/store', [BusinessController::class, 'store']); // DONE: Store new business
                Route::get('/details/{business}', [BusinessController::class, 'show']); // DONE: Business details
                Route::post('/update/{business}', [BusinessController::class, 'update']); // DONE: Update business
                Route::delete('/delete/{business}', [BusinessController::class, 'destroy']); // DONE: Delete business
                Route::patch('/{business}/toggle-status', [BusinessController::class, 'toggleStatus']); // DONE: Toggle active/inactive
                Route::patch('/{business}/terminate', [BusinessController::class, 'terminate']); // DONE: Terminate
            });

            // Active round session
            Route::get('/active-round-session', [ContestApplicationController::class, 'activeRoundSession']); // DONE: current active session

            // Contestent application
            Route::group(['prefix' => 'contest-applications'], function () {
                Route::post('/', [ContestApplicationController::class, 'store']); // DONE: Apply to contest
                Route::get('/my', [ContestApplicationController::class, 'myApplications']); // DONE: My applications
                Route::get('/{application}', [ContestApplicationController::class, 'show']); // DONE: Show application
                Route::post('/{application}/withdraw', [ContestApplicationController::class, 'withdraw']); // DONE: Withdraw application

                Route::get('/session/{season}', [ContestApplicationController::class, 'listBySession']); // List by session (admin)
                Route::patch('/{application}/approve', [ContestApplicationController::class, 'approve']); // Approve (admin)
                Route::patch('/{application}/reject', [ContestApplicationController::class, 'reject']); // Reject (admin)
            });

            // Business Spotlight
            Route::group(['prefix' => 'business-spotlight'], function () {
                Route::get('/', [BusinessSpotlightController::class, 'index']); // List all
                Route::post('/', [BusinessSpotlightController::class, 'store']); // Submit complete form
                Route::post('/draft', [BusinessSpotlightController::class, 'saveDraft']); // Save draft (partial)
                Route::get('/draft', [BusinessSpotlightController::class, 'getDraft']); // Retrieve draft by email
                Route::get('/{id}', [BusinessSpotlightController::class, 'show']); // Get single spotlight
                Route::post('/update/{id}', [BusinessSpotlightController::class, 'update']); // Full update
                Route::delete('/delete/{id}', [BusinessSpotlightController::class, 'destroy']); // Delete spotlight
            });
        });

        // Business interactions (clap, save, share)
        Route::group(['prefix' => 'businesses', 'role:boss|member|artist|sponsor,api'], function () {
            Route::post('/{business}/clap', [BusinessController::class, 'toggleClap']);
            Route::post('/{business}/save', [BusinessController::class, 'toggleSave']);
            Route::post('/{business}/share', [BusinessController::class, 'toggleShare']);
            Route::get('/{business}/interactions', [BusinessController::class, 'userInteractions']);
        });

        /*
        |--------------------------------------------------------------------------
        | Member Route (protected by member role) - Member Dashboard
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:member,api')->group(function () {
            // Profile manage
            Route::group(['prefix' => 'member'], function () {
                Route::post('/profile/store', [MemberProfileController::class, 'store']);
                Route::post('/profile/update', [MemberProfileController::class, 'update']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Sponsor Route (protected by sponsor role) - Sponsor Dashboard
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:sponsor,api')->prefix('sponsor')->group(function () {
            Route::post('/profile/store', [SponsorProfileController::class, 'store']);
            Route::post('/profile/update', [SponsorProfileController::class, 'update']);
        });

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

        /*
        |--------------------------------------------------------------------------
        | E-commerce: Wishlist, Cart & Orders
        |--------------------------------------------------------------------------
        */
        // Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']); // DONE: get all wishlist product
            Route::post('/toggle/{product}', [WishlistController::class, 'toggle']); // DONE: toggle wishlist product
            Route::delete('/{product}', [WishlistController::class, 'destroy']); // DONE: delete wishlist product
            Route::delete('/', [WishlistController::class, 'clear']); // DONE: clear wishlist product
        });

        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']); // DONE: get all cart product
            Route::post('/add', [CartController::class, 'add']); // DONE: add product to cart
            Route::post('/{cart}/update', [CartController::class, 'update']); // DONE: update product in cart
            Route::delete('/{cart}/delete', [CartController::class, 'destroy']); // DONE: delete product from cart
            Route::delete('/clear', [CartController::class, 'clear']); // DONE: clear cart
        });

        // Order
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']); // DONE: get all order
            Route::post('/place', [OrderController::class, 'place']); // DONE: place a new oeder
            Route::post('/buy-now', [OrderController::class, 'buyNow']); // DONE: buy now
            Route::get('/{order}', [OrderController::class, 'show']); // DONE: show order
            Route::post('/{order}/cancel', [OrderController::class, 'cancel']); // DONE: cancel order
        });


        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('/{id}/mark-read', [NotificationController::class, 'markOneAsRead']);
        });

        // Event Registrations (Dashboard)
        Route::prefix('event-registrations')->group(function () {
            Route::get('/', [EventController::class, 'myRegistrations']);
            Route::get('/{id}/ticket', [EventController::class, 'downloadTicket']);
            Route::post('/{id}/cancel', [EventController::class, 'cancelRegistration']);
        });

        /*
        |--------------------------------------------------------------------------
        | Contest — Voting, Submissions & Leaderboard
        |--------------------------------------------------------------------------
        */
        Route::prefix('contest')->group(function () {
            //My contest
            Route::get('/my-contests', [ContestApplicationController::class, 'myContests']);
            
            // Submissions
            Route::post('/rounds/{round}/submissions', [RoundSubmissionController::class, 'store']);
            Route::post('/rounds/{round}/submissions/draft', [RoundSubmissionController::class, 'saveDraft']);
            Route::get('/rounds/{round}/submissions/my', [RoundSubmissionController::class, 'mySubmission']);

            // Votes
            Route::post('/rounds/{round}/votes', [VoteController::class, 'store']);
            Route::get('/rounds/{round}/votes/my', [VoteController::class, 'myVotes']);
            Route::get('/rounds/{round}/votes/check/{contestant}', [VoteController::class, 'check']);
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
});


/*
|--------------------------------------------------------------------------
| API V2 — Authentication Routes (link-based, no OTP)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v2'], function () {
    Route::group(['middleware' => 'guest:api'], function () {

        // Registration
        Route::post('/register', [V2RegisterController::class, 'register']); // DONE: user registration
        Route::get('/verify-email', [V2RegisterController::class, 'verifyEmail']); // DONE: otp verification
        Route::post('/resend-verification', [V2RegisterController::class, 'resendVerification']); // DONE: resend verification token

        // Login (reuse v1 LoginController)
        Route::post('/login', [LoginController::class, 'login']); // DONE: user login

        // Forgot Password
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

/*
|--------------------------------------------------------------------------
| Stripe Webhook (public, no auth)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
