<?php

namespace App\Console\Commands;

use App\Models\Spotlight\SpotlightWeek;
use App\Services\Spotlight\SpotlightWeekService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SpotlightSelectWinner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan spotlight:select-winner
     *   php artisan spotlight:select-winner 20
     *   php artisan spotlight:select-winner 20 --force
     */
    protected $signature = 'spotlight:select-winner
                            {week? : The ID of the Spotlight Week (optional)}
                            {--force : Force close voting and select winner even if end time has not passed}';

    /**
     * The console command description.
     */
    protected $description = 'Close voting and select the winner for every spotlight week that needs one';

    public function handle(SpotlightWeekService $weekService): int
    {
        $weekId = $this->argument('week');
        $force  = $this->option('force');

        if ($weekId) {
            $week = SpotlightWeek::find($weekId);

            if (! $week) {
                $this->error("Spotlight Week #{$weekId} not found.");
                return self::FAILURE;
            }

            return $this->finalizeWeek($weekService, $week, $force);
        }

        return $this->finalizeAllWeeks($weekService, $force);
    }

    /**
     * Finalize every week that still needs a winner:
     *   - voting weeks whose voting period has ended
     *   - completed weeks whose nominees were never finalized (no rank/winner)
     *
     * @param  SpotlightWeekService  $weekService
     * @param  bool  $force
     * @return int
     */
    private function finalizeAllWeeks(SpotlightWeekService $weekService, bool $force): int
    {
        $weeks = $this->resolveWeeksNeedingWinner($force);

        if ($weeks->isEmpty()) {
            $this->info('No spotlight weeks need winner selection — everything is up to date.');
            return self::SUCCESS;
        }

        $this->info("Found {$weeks->count()} week(s) needing winner selection.");

        $successCount = 0;

        foreach ($weeks as $week) {
            $this->newLine();

            if ($this->finalizeWeek($weekService, $week, $force) === self::SUCCESS) {
                $successCount++;
            }
        }

        $this->newLine();
        $this->info("Done. Winners selected for {$successCount} of {$weeks->count()} week(s).");

        return self::SUCCESS;
    }

    /**
     * Resolve all weeks that need a winner selected.
     *
     * @param  bool  $force
     * @return Collection<int, SpotlightWeek>
     */
    private function resolveWeeksNeedingWinner(bool $force): Collection
    {
        return SpotlightWeek::with('nominees')
            ->where(function ($q) use ($force) {
                // Voting weeks — only process them once their voting has ended
                // (or immediately when --force is passed)
                $q->where('status', 'voting');

                if (! $force) {
                    $q->where(function ($q2) {
                        $q2->whereNull('voting_ends_at')
                            ->orWhere('voting_ends_at', '<=', now());
                    });
                }
            })
            ->orWhere(function ($q) {
                // Completed weeks that were marked completed without ever
                // finalizing their nominees (no rank / winner assigned)
                $q->where('status', 'completed')
                    ->whereHas('nominees', function ($q2) {
                        $q2->whereNull('rank')->orWhereNull('is_winner');
                    });
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Close voting for a single week and select its winner.
     *
     * @param  SpotlightWeekService  $weekService
     * @param  SpotlightWeek  $week
     * @param  bool  $force
     * @return int
     */
    private function finalizeWeek(SpotlightWeekService $weekService, SpotlightWeek $week, bool $force): int
    {
        $this->info("Processing Spotlight Week #{$week->id} (Year {$week->year}, Week {$week->week_number})");
        $this->line("Current Status: {$week->status}");
        $this->line("Voting Window: {$week->voting_starts_at} → {$week->voting_ends_at}");

        // A completed week whose nominees were never finalized (no rank / winner)
        $needsFinalization = $week->status === 'completed'
            && $week->nominees()->whereNull('rank')->orWhereNull('is_winner')->exists();

        if ($week->status === 'completed' && ! $needsFinalization) {
            $this->warn("Week #{$week->id} has already been completed.");

            $winner = $week->nominees()->where('is_winner', true)->first();

            if ($winner) {
                $wName = $winner->spotlightable?->business_name
                    ?? $winner->spotlightable?->artist_stage_name
                    ?? "Nominee #{$winner->id}";
                $this->info("Winner: {$wName} ({$winner->total_vote_count} votes)");
            }

            return self::SUCCESS;
        }

        // If week status is pending or nominating, require --force to proceed
        if (in_array($week->status, ['pending', 'nominating'])) {
            if ($force) {
                $week->update(['status' => 'voting']);
                $this->info("Forced week status to 'voting'.");
            } else {
                $this->warn("Week is in '{$week->status}' status. Use --force to proceed.");
                return self::FAILURE;
            }
        }

        // Reopen a completed-but-unfinalized week so closeVoting can finalize it
        if ($week->status === 'completed' && $needsFinalization) {
            $week->update(['status' => 'voting']);
            $this->info("Week was completed without a finalized winner — reopening to select the winner.");
        }

        // Check time condition unless --force is passed
        if ($week->voting_ends_at && $week->voting_ends_at->isFuture() && ! $force) {
            $this->warn("Voting for Week #{$week->id} is still ongoing until {$week->voting_ends_at}.");
            $this->line("Use --force to close voting immediately and select the winner.");
            return self::FAILURE;
        }

        // Close voting and calculate ranks/winner
        $result = $weekService->closeVoting($week);

        if ($result['success']) {
            $this->info('========================================');
            $this->info('🎉 Spotlight Winner Selected Successfully!');
            $this->info('========================================');

            $winner = $result['winner'];

            if ($winner) {
                $name = $winner->spotlightable?->business_name
                    ?? $winner->spotlightable?->artist_stage_name
                    ?? $winner->spotlightable?->full_legal_name
                    ?? "Nominee #{$winner->id}";

                $this->line("  🏆 Winner: {$name}");
                $this->line("  📊 Total Votes: {$winner->total_vote_count} (Free: {$winner->free_vote_count}, Paid: {$winner->paid_vote_count})");
                $this->line("  👤 User ID: {$winner->user_id}");
            } else {
                $this->warn("  No nominees were registered for this week.");
            }

            $this->newLine();
            $this->info('Leaderboard Overview:');

            foreach ($result['leaderboard'] as $nominee) {
                $nName = $nominee->spotlightable?->business_name
                    ?? $nominee->spotlightable?->artist_stage_name
                    ?? "Nominee #{$nominee->id}";

                $badge = $nominee->is_winner ? ' [WINNER 🏆]' : '';
                $this->line("  Rank #{$nominee->rank}: {$nName} - {$nominee->total_vote_count} votes{$badge}");
            }

            return self::SUCCESS;
        }

        $this->error("Failed to select winner: {$result['message']}");
        return self::FAILURE;
    }
}
