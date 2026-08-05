<?php

namespace App\Console\Commands\Contest;

use App\Jobs\Contest\ProcessRoundTransition;
use App\Models\Round;
use App\Services\Contest\EliminationService;
use Illuminate\Console\Command;

class ProcessRoundTransitions extends Command
{
    protected $signature = 'contest:process-round-transitions{--round= : Process a specific round by ID}{--force : Process even if round has not ended yet}{--dry-run : Show what would happen without making changes}';

    protected $description = 'Process round transitions — advance winners and eliminate losers';

    public function handle(EliminationService $eliminationService): int
    {
        $roundId = $this->option('round');
        $force   = $this->option('force');
        $dryRun  = $this->option('dry-run');

        if ($roundId) {
            $round = Round::find($roundId);

            if (!$round) {
                $this->error("Round #{$roundId} not found.");
                return Command::FAILURE;
            }

            $rounds = [$round];
        } else {
            $rounds = $eliminationService->findRoundsNeedingTransition();

            // --force without --round: process every active round in round order,
            // regardless of its end date (a full run-to-winner flow in one go).
            if ($force) {
                $rounds = Round::where('is_active', true)
                    ->orderBy('season_id')
                    ->orderBy('round_number')
                    ->get()
                    ->all();
            }
        }

        if (empty($rounds)) {
            $this->info('No rounds need transition.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . count($rounds) . ' round(s) to process:');

        foreach ($rounds as $round) {
            if (!$round->hasEnded() && !$force) {
                $this->warn("  Round #{$round->id} ({$round->title}) — has not ended yet. Use --force to process anyway.");
                continue;
            }

            $this->line("  Round #{$round->id} ({$round->title}) — {$round->elimination_rule}");

            if ($dryRun) {
                $this->line("[DRY RUN] Would process transition.");
                continue;
            }

            if ($force) {
                // Force = manual/testing action. Run synchronously so the result is
                // immediate and does not depend on a queue worker being online.
                $result = $eliminationService->processRoundTransition($round);
                $this->info(
                    '  ✅ Processed: ' . count($result['advanced']) . ' advanced, '
                    . count($result['eliminated']) . ' eliminated.'
                );
            } else {
                ProcessRoundTransition::dispatch($round);
                $this->info('Transition job dispatched.');
            }
        }

        return Command::SUCCESS;
    }
}
