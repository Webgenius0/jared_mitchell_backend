<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ContestantsAdvanced;
use App\Models\Contest\Contestant;
use App\Notifications\Contest\ContestantAdvancedNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendContestantAdvancedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ContestNotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ContestantsAdvanced $event): void
    {
        $transition = $event->transition;
        $transition->loadMissing(['fromRound.season', 'toRound']);

        $season = $transition->fromRound?->season ?? $transition->season;
        if (!$season) {
            return;
        }

        $fromRoundNumber = $transition->fromRound?->round_number ?? 0;
        $toRoundNumber = $transition->toRound?->round_number ?? ($fromRoundNumber + 1);

        $contestantIds = array_column($event->advancedContestants, 'id');
        $contestants = Contestant::whereIn('id', $contestantIds)->get();

        foreach ($contestants as $contestant) {
            $user = $this->notificationService->resolveContestantUser($contestant);
            if (!$user) {
                continue;
            }

            // Find this contestant's rank/score from the event data
            $data = collect($event->advancedContestants)->firstWhere('id', $contestant->id);
            $rank = $data['rank'] ?? 0;
            $score = $data['score'] ?? '0';

            $user->notify(new ContestantAdvancedNotification(
                season: $season,
                fromRoundNumber: $fromRoundNumber,
                toRoundNumber: $toRoundNumber,
                rank: (int) $rank,
                score: (string) $score,
            ));
        }
    }
}
