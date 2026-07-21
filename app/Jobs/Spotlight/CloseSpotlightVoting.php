<?php

namespace App\Jobs\Spotlight;

use App\Models\Spotlight\SpotlightWeek;
use App\Services\Spotlight\SpotlightWeekService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatched by SpotlightSchedulerService when a week's voting_ends_at has passed.
 * Closes voting, ranks all nominees, and sets the winner.
 */
class CloseSpotlightVoting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $spotlightWeekId
    ) {}

    public function handle(SpotlightWeekService $weekService): void
    {
        $week = SpotlightWeek::find($this->spotlightWeekId);

        if (! $week) {
            Log::warning('Job CloseSpotlightVoting: Week not found', [
                'spotlight_week_id' => $this->spotlightWeekId,
            ]);
            return;
        }

        if ($week->status !== 'voting') {
            Log::info('Job CloseSpotlightVoting: Week not in voting status, skipping', [
                'week_id' => $week->id,
                'status'  => $week->status,
            ]);
            return;
        }

        Log::info('Job CloseSpotlightVoting: Closing voting for week', [
            'week_id' => $week->id,
        ]);

        $result = $weekService->closeVoting($week);

        if ($result['success']) {
            Log::info('Job CloseSpotlightVoting: Voting closed, winner set', [
                'week_id' => $week->id,
                'winner'  => $result['winner']?->toArray(),
            ]);
        } else {
            Log::error('Job CloseSpotlightVoting: Failed to close voting', [
                'week_id' => $week->id,
                'message' => $result['message'],
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job CloseSpotlightVoting: Failed', [
            'week_id' => $this->spotlightWeekId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
