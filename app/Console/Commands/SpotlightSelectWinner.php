<?php

namespace App\Console\Commands;

use App\Models\Spotlight\SpotlightWeek;
use App\Services\Spotlight\SpotlightWeekService;
use Illuminate\Console\Command;

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
    protected $description = 'Close voting for a spotlight week and select the winner based on votes';

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
        } else {
            // Find current active voting week
            $week = SpotlightWeek::where('status', 'voting')->latest()->first();

            if (! $week) {
                // Fallback to latest week in nominating or pending status
                $week = SpotlightWeek::whereIn('status', ['nominating', 'pending'])->latest()->first();
            }

            if (! $week) {
                $this->error('No active or pending spotlight week found.');
                return self::FAILURE;
            }
        }

        $this->info("Selected Spotlight Week #{$week->id} (Year {$week->year}, Week {$week->week_number})");
        $this->line("Current Status: {$week->status}");
        $this->line("Voting Window: {$week->voting_starts_at} → {$week->voting_ends_at}");

        // If week status is pending or nominating, prompt or transition
        if (in_array($week->status, ['pending', 'nominating'])) {
            if ($force) {
                $week->update(['status' => 'voting']);
                $this->info("Forced week status to 'voting'.");
            } else {
                $this->warn("Week is in '{$week->status}' status. Use --force to proceed.");
                return self::FAILURE;
            }
        }

        if ($week->status === 'completed') {
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
