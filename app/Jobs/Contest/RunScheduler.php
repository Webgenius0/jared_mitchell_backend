<?php

namespace App\Jobs\Contest;

use App\Services\Contest\SchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunScheduler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries   = 3;

    /**
     * Master scheduler — checks all timing conditions across seasons and rounds.
     * Designed to run every 1-5 minutes via cron/schedule.
     */
    public function handle(SchedulerService $scheduler): void
    {
        $actions = $scheduler->run();

        Log::info('RunScheduler completed', [
            'actions_taken' => count($actions),
        ]);
    }
}
