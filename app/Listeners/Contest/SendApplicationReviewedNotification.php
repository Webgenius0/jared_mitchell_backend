<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ApplicationReviewed;
use App\Notifications\Contest\ApplicationReviewedNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApplicationReviewedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ContestNotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ApplicationReviewed $event): void
    {
        $application = $event->application;
        $application->loadMissing(['season', 'business.user']);

        $season = $application->season;
        if (!$season) {
            return;
        }

        // Notify the business owner
        $user = $application->business?->user;
        if (!$user) {
            return;
        }

        $user->notify(new ApplicationReviewedNotification(
            season: $season,
            verdict: $event->verdict,
            adminNote: $application->admin_note,
        ));
    }
}
