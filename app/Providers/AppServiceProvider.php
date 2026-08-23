<?php

namespace App\Providers;

use App\Events\Contest\ApplicationReviewed;
use App\Listeners\Contest\SendApplicationReviewedNotification;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Services\AiService;
use Illuminate\Pagination\Paginator;
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
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Use Bootstrap 5 pagination globally
        Paginator::useBootstrapFive();

        // ── Super-admin bypass: grant all permissions ──
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // ── Register Policies ──
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        // ── Application reviewed → notify applicant ──
        Event::listen(
            ApplicationReviewed::class,
            SendApplicationReviewedNotification::class,
        );
    }
}
