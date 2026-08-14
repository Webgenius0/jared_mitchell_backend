<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Contest\ContestApplication;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Models\Spotlight\SpotlightWeek;
use App\Services\Contest\SchedulerService as ContestScheduler;
use App\Services\Spotlight\SpotlightSchedulerService as SpotlightScheduler;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RunFastTestScheduler extends Command
{
    /**
     * Signature:
     *   php artisan app:run-fast-test-scheduler
     *   php artisan app:run-fast-test-scheduler --setup
     *   php artisan app:run-fast-test-scheduler --watch
     */
    protected $signature = 'app:run-fast-test-scheduler
                            {--setup : Reset and create fresh 5-minute test season and 10-minute spotlight week}
                            {--watch : Keep running in a continuous live loop in terminal (refreshes every 15s)}';

    protected $description = 'Fast live testing scheduler — 5 min applications, 5 min rounds, 10 min spotlight week';

    public function handle(
        ContestScheduler $contestScheduler,
        SpotlightScheduler $spotlightScheduler
    ): int {
        // Auto-initialize test environment if no test season exists or if --setup passed
        $hasTestSeason = Season::where('slug', 'like', 'fast-test-season-%')->exists();

        if ($this->option('setup') || ! $hasTestSeason) {
            $this->setupFastTestEnvironment();
        }

        $watch = $this->option('watch');

        do {
            $bdtNow = Carbon::now('Asia/Dhaka');
            $nowStr = $bdtNow->format('h:i:s A') . ' BDT';

            $this->info('========================================');
            $this->info("🚀 Fast Test Scheduler Execution [{$nowStr}]");
            $this->info('========================================');

            // ── Contest Scheduler ──
            $contestActions = $contestScheduler->run();

            if (empty($contestActions)) {
                $this->line('   [Contest] No actions needed right now.');
            } else {
                $this->info('   [Contest] Actions taken: ' . count($contestActions));
                foreach ($contestActions as $action) {
                    $this->line("   - [{$action['type']}]");
                }
            }

            // ── Spotlight Scheduler ──
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
                $this->comment('⏳ Live watching... Checking again in 15 seconds (Press Ctrl+C to stop)');
                sleep(15);
                $this->newLine(2);
            }
        } while ($watch);

        return Command::SUCCESS;
    }

    /**
     * Create fresh 5-minute test season & 10-minute spotlight week.
     */
    private function setupFastTestEnvironment(): void
    {
        $this->warn('Initializing Fast Test Environment...');

        $now = Carbon::now();
        $bdtNow = Carbon::now('Asia/Dhaka');

        // ── 1. Create 5-minute Contest Season ──
        $seasonStartsAt = $now->copy()->addMinutes(5); // Apps open for 5 mins
        $seasonEndsAt   = $seasonStartsAt->copy()->addMinutes(25); // 5 rounds * 5 mins = 25 mins

        $bdtStartsAt = $bdtNow->copy()->addMinutes(5);

        $season = Season::create([
            'contest_type'           => 'business',
            'title'                  => 'FAST TEST - Boss Beginnings (5-Min Cycles)',
            'slug'                   => 'fast-test-season-' . $now->timestamp,
            'description'            => 'Live fast-testing contest environment (5 min rounds)',
            'status'                 => 'open',
            'configuration'          => ['max_contestants' => 50, 'voting_strategy' => 'popular_vote'],
            'applications_starts_at' => $now->copy(),
            'applications_ends_at'   => $seasonStartsAt->copy(),
            'starts_at'              => $seasonStartsAt->copy(),
            'ends_at'                => $seasonEndsAt->copy(),
            'is_active'              => true,
            'is_featured'            => true,
        ]);

        $roundConfigs = [
            ['number' => 1, 'title' => 'Preliminary Round (5 Min)', 'limit' => 10],
            ['number' => 2, 'title' => 'Qualifiers (5 Min)',        'limit' => 8],
            ['number' => 3, 'title' => 'Semi-Finals (5 Min)',       'limit' => 4],
            ['number' => 4, 'title' => 'Finals Prep (5 Min)',       'limit' => 2],
            ['number' => 5, 'title' => 'Grand Finals (5 Min)',      'limit' => 1],
        ];

        $rStart = $seasonStartsAt->copy();

        foreach ($roundConfigs as $cfg) {
            $rEnd = $rStart->copy()->addMinutes(5);

            Round::create([
                'season_id'              => $season->id,
                'round_number'           => $cfg['number'],
                'title'                  => $cfg['title'],
                'goal'                   => "Fast test {$cfg['title']} goal",
                'requirements'           => 'Fast test requirements',
                'voting_strategy'        => 'popular_vote',
                'submission_type'        => 'multi',
                'advance_limit'          => $cfg['limit'],
                'elimination_rule'       => 'advance_limit',
                'advancement_config'     => [
                    'top_n'                  => $cfg['limit'],
                    'categories'             => ['innovation', 'presentation', 'impact'],
                    'max_score_per_category' => 10,
                ],
                'is_active'              => false,
                'starts_at'              => $rStart->copy(),
                'ends_at'                => $rEnd->copy(),
                'voting_ends_at'         => $rEnd->copy(),
                'sort_order'             => $cfg['number'],
            ]);

            $rStart = $rEnd->copy();
        }

        // ── 2. Create 10-minute Spotlight Week ──
        $currentMonday = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $currentSunday = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $week = SpotlightWeek::updateOrCreate(
            [
                'week_number' => (int) $currentMonday->isoWeek(),
                'year'        => (int) $currentMonday->year,
            ],
            [
                'status'           => 'nominating',
                'voting_starts_at' => $now->copy()->addMinutes(5), // 5 min nomination
                'voting_ends_at'   => $now->copy()->addMinutes(10), // 5 min voting
            ]
        );

        $this->info('========================================');
        $this->info('🎉 FAST TEST ENVIRONMENT READY!');
        $this->info('========================================');
        $this->line(" - Season ID: {$season->id} ({$season->title})");
        $this->line(" - Applications Open: NOW ({$bdtNow->format('h:i:s A')} BDT) → Ends in 5 mins ({$bdtStartsAt->format('h:i:s A')} BDT)");
        $this->line(" - Round 1 Starts: {$bdtStartsAt->format('h:i:s A')} BDT (5 min duration per round)");
        $this->line(" - Spotlight Week ID: {$week->id} (Nominating: NOW → Voting starts in 5 mins)");
        $this->newLine();
    }
}
