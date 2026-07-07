<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ApplicationSubmitted;
use App\Notifications\Contest\ApplicationSubmittedNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApplicationSubmittedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ContestNotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        $application = $event->application;
        $application->loadMissing(['season']);

        $season = $application->season;
        if (!$season) {
            return;
        }

        // Resolve the applicant name from the contestable
        $applicantName = 'Unknown';
        $contestable = $application->contestable;
        if ($contestable) {
            if (method_exists($contestable, 'getContestantName')) {
                $applicantName = $contestable->getContestantName();
            } elseif (isset($contestable->name)) {
                $applicantName = $contestable->name;
            }
        }

        $admins = $this->notificationService->getAdminUsers();
        if ($admins->isEmpty()) {
            return;
        }

        $this->notificationService->notifyUsers(
            $admins,
            new ApplicationSubmittedNotification(
                season: $season,
                applicantName: $applicantName,
                applicationId: $application->id,
            ),
        );
    }
}
