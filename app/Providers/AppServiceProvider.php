<?php

namespace App\Providers;

use App\Events\Contest\ApplicationReviewed;
use App\Events\Contest\ApplicationSubmitted;
use App\Events\Contest\ContestantsAdvanced;
use App\Events\Contest\ContestantsEliminated;
use App\Events\Contest\RoundEnded;
use App\Listeners\Contest\DispatchAiReview;
use App\Listeners\Contest\SendApplicationReviewedNotification;
use App\Listeners\Contest\SendApplicationSubmittedNotification;
use App\Listeners\Contest\SendContestantAdvancedNotification;
use App\Listeners\Contest\SendContestantEliminatedNotification;
use App\Listeners\Contest\SendRoundEndedNotification;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Services\AiService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Super-admin bypass: grant all permissions ──
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // ── Register Policies ──
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        // ── Register Contest Events & Listeners ──
        Event::listen(
            ApplicationSubmitted::class,
            DispatchAiReview::class,
        );

        // Contestant advancement/elimination notifications
        Event::listen(
            ContestantsAdvanced::class,
            SendContestantAdvancedNotification::class,
        );

        Event::listen(
            ContestantsEliminated::class,
            SendContestantEliminatedNotification::class,
        );

        // Round ended → notify admins
        Event::listen(
            RoundEnded::class,
            SendRoundEndedNotification::class,
        );

        // Application submitted → notify admins
        Event::listen(
            ApplicationSubmitted::class,
            SendApplicationSubmittedNotification::class,
        );

        // Application reviewed → notify applicant
        Event::listen(
            ApplicationReviewed::class,
            SendApplicationReviewedNotification::class,
        );
    }
}
