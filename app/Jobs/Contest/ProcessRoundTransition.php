<?php

namespace App\Jobs\Contest;

use App\Models\Round;
use App\Services\Contest\EliminationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRoundTransition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries   = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Round $round
    ) {}

    public function handle(EliminationService $eliminationService): void
    {
        // Check round has ended
        if (!$this->round->hasEnded()) {
            Log::info('ProcessRoundTransition: Round has not ended yet, skipping', [
                'round_id' => $this->round->id,
                'ends_at'  => $this->round->ends_at,
            ]);
            return;
        }

        try {
            $result = $eliminationService->processRoundTransition($this->round);

            Log::info('ProcessRoundTransition completed', [
                'round_id'          => $this->round->id,
                'advanced'          => count($result['advanced']),
                'eliminated'        => count($result['eliminated']),
            ]);
        } catch (Throwable $e) {
            Log::error('ProcessRoundTransition failed', [
                'round_id' => $this->round->id,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('ProcessRoundTransition permanently failed', [
            'round_id' => $this->round->id,
            'error'    => $e->getMessage(),
        ]);
    }
}
