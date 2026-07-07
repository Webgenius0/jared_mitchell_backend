<?php

namespace App\Jobs\Contest;

use App\Models\Contest\RoundTransition;
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

    public $timeout = 300; // 5 minutes for large transition batches
    public $tries   = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Round $round
    ) {}

    public function handle(EliminationService $eliminationService): void
    {
        // Check if transition already completed
        $existing = RoundTransition::where('from_round_id', $this->round->id)
            ->where('status', 'completed')
            ->exists();

        if ($existing) {
            Log::info('ProcessRoundTransition: Round already transitioned, skipping', [
                'round_id' => $this->round->id,
            ]);
            return;
        }

        // Check round has ended
        if (!$this->round->hasEnded()) {
            Log::info('ProcessRoundTransition: Round has not ended yet, skipping', [
                'round_id' => $this->round->id,
                'ends_at'  => $this->round->ends_at,
            ]);
            return;
        }

        try {
            $transition = $eliminationService->processRoundTransition($this->round);

            Log::info('ProcessRoundTransition completed', [
                'round_id'          => $this->round->id,
                'transition_id'     => $transition->id,
                'advanced'          => $transition->advanced_count,
                'eliminated'        => $transition->eliminated_count,
                'next_round_id'     => $transition->to_round_id,
            ]);
        } catch (Throwable $e) {
            Log::error('ProcessRoundTransition failed', [
                'round_id' => $this->round->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            // Mark the transition as failed so it can be retried
            RoundTransition::create([
                'from_round_id'     => $this->round->id,
                'to_round_id'       => null,
                'season_id'         => $this->round->season_id,
                'status'            => 'failed',
                'elimination_rule'  => $this->round->elimination_rule ?? 'advance_limit',
                'transition_config' => $this->round->advancement_config,
                'total_contestants' => 0,
                'advanced_count'    => 0,
                'eliminated_count'  => 0,
                'metadata'          => ['error' => $e->getMessage()],
                'processed_at'      => now(),
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
