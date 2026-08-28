<?php

namespace App\Console\Commands;

use App\Models\Contest\Season;
use App\Models\Spotlight\SpotlightWeek;
use App\Services\Contest\AutoSeasonBuilderService;
use App\Services\Contest\SchedulerService as ContestScheduler;
use App\Services\Spotlight\SpotlightSchedulerService as SpotlightScheduler;
use App\Services\Spotlight\SpotlightWeekService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunRealScheduler extends Command
{
    /**
     * Signature:
     *   php artisan app:run-real-scheduler
     *   php artisan app:run-real-scheduler --setup
     *   php artisan app:run-real-scheduler --watch
     *   php artisan app:run-real-scheduler --setup --watch
     */
    protected $signature = 'app:run-real-scheduler
                            {--setup : Ensure upcoming 5-week Boss Beginnings season (1-week rounds) & 1-week Spotlight cycle}
                            {--watch : Keep running in a continuous live loop in terminal (refreshes every 15s)}';

    protected $description = 'Real production scheduler — 5 rounds of 1 week each (5-week season), 1-week spotlight cycle';

    public function handle(
        ContestScheduler $contestScheduler,
        SpotlightScheduler $spotlightScheduler,
        AutoSeasonBuilderService $autoSeasonBuilder,
        SpotlightWeekService $spotlightWeekService
    ): int {
        $hasSeason = Season::where('contest_type', 'business')->exists();

        if ($this->option('setup') || ! $hasSeason) {
            $this->setupRealEnvironment($autoSeasonBuilder, $spotlightWeekService);
        }

        $watch = $this->option('watch');

        do {
            $bdtNow = Carbon::now('Asia/Dhaka');
            $nowStr = $bdtNow->format('h:i:s A') . ' BDT';

            $this->info('========================================');
            $this->info("🏆 Real Master Scheduler Execution [{$nowStr}]");
            $this->info('========================================');

            // ── 1. Contest Scheduler (1-week rounds) ──
            $contestActions = $contestScheduler->run();

            if (empty($contestActions)) {
                $this->line('   [Boss Beginnings] No actions needed right now.');
            } else {
                $this->info('   [Boss Beginnings] Actions taken: ' . count($contestActions));
                foreach ($contestActions as $action) {
                    $this->line("   - [{$action['type']}]");
                }
            }

            // ── 2. Spotlight Scheduler (1-week cycles) ──
            $spotlightActions = $spotlightScheduler->run();

            if (empty($spotlightActions)) {
                $this->line('   [Spotlight] No actions needed right now.');
            } else {
                $this->info('   [Spotlight] Actions taken: ' . count($spotlightActions));
                foreach ($spotlightActions as $action) {
                    $this->line("   - [{$action['type']}]");
                }
            }

            $this->newLine();
            $this->info('✅ Execution finished at ' . Carbon::now('Asia/Dhaka')->format('h:i:s A') . ' BDT');

            if ($watch) {
                $this->comment('⏳ Live watching real schedulers... Checking again in 15 seconds (Press Ctrl+C to stop)');
                sleep(15);
                $this->newLine(2);
            }
        } while ($watch);

        return Command::SUCCESS;
    }

    /**
     * Create/ensure fresh 5-week Boss Beginnings season (1-week per round) & 1-week Spotlight cycle.
     */
    private function setupRealEnvironment(
        AutoSeasonBuilderService $autoSeasonBuilder,
        SpotlightWeekService $spotlightWeekService
    ): void {
        $this->warn('Initializing Real 5-Week Scheduler Environment...');

        $now = Carbon::now();
        $bdtNow = Carbon::now('Asia/Dhaka');

        // 1. Ensure 5-week Boss Beginnings season (1-week rounds)
        $season = $autoSeasonBuilder->ensureUpcomingSeasonExists();

        if (! $season) {
            $season = Season::where('contest_type', 'business')
                ->with('rounds')
                ->orderByDesc('id')
                ->first();
        }

        // 2. Ensure 1-week Spotlight cycle respecting admin setting
        $adminSpotlightStart = \App\Models\Setting::current()?->spotlight_start_date;
        if ($adminSpotlightStart && $adminSpotlightStart->isAfter($now)) {
            $adminMonday = $adminSpotlightStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $currentMonday = $adminMonday->gte($now->copy()->startOfDay())
                ? $adminMonday
                : ($adminSpotlightStart->copy()->isMonday() ? $adminSpotlightStart->copy()->startOfDay() : $adminSpotlightStart->copy()->next(Carbon::MONDAY)->startOfDay());
        } else {
            $currentMonday = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        }

        $currentSunday = $currentMonday->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $week = SpotlightWeek::firstOrCreate(
            [
                'week_number' => (int) $currentMonday->isoWeek(),
                'year'        => (int) $currentMonday->year,
            ],
            [
                'status'           => 'pending',
                'voting_starts_at' => $currentMonday->copy(),
                'voting_ends_at'   => $currentSunday->copy(),
            ]
        );

        $this->info('========================================');
        $this->info('🎉 REAL SCHEDULER ENVIRONMENT READY!');
        $this->info('========================================');
        if ($season) {
            $this->line(" - Boss Beginnings Season: {$season->title}");
            $this->line(" - Season Duration: {$season->starts_at?->format('Y-m-d')} to {$season->ends_at?->format('Y-m-d')} (" . $season->starts_at?->diffInDays($season->ends_at) . " days / 5 weeks)");
            if ($season->rounds) {
                foreach ($season->rounds as $r) {
                    $this->line("   • Round {$r->round_number}: {$r->title} ({$r->starts_at?->format('Y-m-d')} → {$r->ends_at?->format('Y-m-d')}) [1 Week]");
                }
            }
        }
        $this->line(" - Spotlight Week ID: {$week->id} (Mon {$currentMonday->format('Y-m-d')} → Sun {$currentSunday->format('Y-m-d')} [1 Week Cycle])");
        $this->newLine();
    }
}
