<?php

namespace App\Listeners\Contest;

use App\Events\Contest\RoundEnded;
use App\Notifications\Contest\RoundEndedNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRoundEndedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ContestNotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(RoundEnded $event): void
    {
        $round = $event->round;
        $round->loadMissing(['season']);

        $season = $round->season;
        if (!$season) {
            return;
        }

        // Load transition to get advanced/eliminated counts
        $transition = $round->transitions()
            ->where('status', 'completed')
            ->latest('processed_at')
            ->first();

        $advancedCount = $transition?->advanced_count ?? 0;
        $eliminatedCount = $transition?->eliminated_count ?? 0;

        $admins = $this->notificationService->getAdminUsers();
        if ($admins->isEmpty()) {
            return;
        }

        $this->notificationService->notifyUsers(
            $admins,
            new RoundEndedNotification(
                season: $season,
                roundNumber: $round->round_number ?? 0,
                roundTitle: $round->title ?? "Round {$round->round_number}",
                advancedCount: $advancedCount,
                eliminatedCount: $eliminatedCount,
            ),
        );
    }
}
