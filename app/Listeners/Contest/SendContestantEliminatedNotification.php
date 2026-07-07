<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ContestantsEliminated;
use App\Models\Contest\Contestant;
use App\Notifications\Contest\ContestantEliminatedNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendContestantEliminatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected ContestNotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ContestantsEliminated $event): void
    {
        $transition = $event->transition;
        $transition->loadMissing(['fromRound.season']);

        $season = $transition->fromRound?->season ?? $transition->season;
        if (!$season) {
            return;
        }

        $roundNumber = $transition->fromRound?->round_number ?? 0;

        $contestantIds = array_column($event->eliminatedContestants, 'id');
        $contestants = Contestant::whereIn('id', $contestantIds)->get();

        foreach ($contestants as $contestant) {
            $user = $this->notificationService->resolveContestantUser($contestant);
            if (!$user) {
                continue;
            }

            $data = collect($event->eliminatedContestants)->firstWhere('id', $contestant->id);
            $rank = $data['rank'] ?? 0;
            $score = $data['score'] ?? '0';

            $user->notify(new ContestantEliminatedNotification(
                season: $season,
                roundNumber: $roundNumber,
                rank: (int) $rank,
                score: (string) $score,
            ));
        }
    }
}
