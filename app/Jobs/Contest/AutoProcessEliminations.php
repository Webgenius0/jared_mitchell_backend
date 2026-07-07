<?php

namespace App\Jobs\Contest;

use App\Services\Contest\EliminationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoProcessEliminations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries   = 3;

    /**
     * Process all rounds that have ended and need transitions.
     * This job is designed to be run on a schedule (e.g., every 5 minutes).
     */
    public function handle(EliminationService $eliminationService): void
    {
        $rounds = $eliminationService->findRoundsNeedingTransition();

        if (empty($rounds)) {
            Log::info('AutoProcessEliminations: No rounds need transition');
            return;
        }

        Log::info('AutoProcessEliminations: Dispatched transitions', [
            'round_count' => count($rounds),
            'round_ids' => array_map(fn($r) => $r->id, $rounds),
        ]);

        foreach ($rounds as $round) {
            ProcessRoundTransition::dispatch($round);
        }
    }
}
