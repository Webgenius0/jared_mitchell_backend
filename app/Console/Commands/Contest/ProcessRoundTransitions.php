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

            if ($force) {
                $rounds = Round::ended()->get()->all();
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
                $this->line("[DRY RUN] Would dispatch transition job.");
                continue;
            }

            ProcessRoundTransition::dispatch($round);
            $this->info("Transition job dispatched.");
        }

        return Command::SUCCESS;
    }
}
