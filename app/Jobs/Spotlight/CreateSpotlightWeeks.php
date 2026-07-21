<?php

namespace App\Jobs\Spotlight;

use App\Services\Spotlight\SpotlightSchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatched by the Laravel Scheduler every Monday 12:00 AM.
 * Creates new spotlight weeks (artist + business) for the current week.
 */
class CreateSpotlightWeeks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function handle(SpotlightSchedulerService $schedulerService): void
    {
        Log::info('Job CreateSpotlightWeeks: Starting');

        $actions = $schedulerService->checkAndCreateWeeks();

        Log::info('Job CreateSpotlightWeeks: Completed', [
            'actions' => $actions,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job CreateSpotlightWeeks: Failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
