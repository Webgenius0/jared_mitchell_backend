<?php

namespace App\Console\Commands;

use App\Services\Spotlight\SpotlightSchedulerService;
use Illuminate\Console\Command;

class RunSpotlightScheduler extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'spotlight:run-scheduler
                            {--dry-run : Show what would happen without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Run the spotlight scheduler: create weeks, open/close voting as needed.';

    public function handle(SpotlightSchedulerService $schedulerService): int
    {
        $this->info('Running Spotlight Scheduler...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN mode — no changes will be made.');
            return self::SUCCESS;
        }

        $actions = $schedulerService->run();

        if (empty($actions)) {
            $this->info('No actions taken — everything is up to date.');
        } else {
            $this->info('Actions taken: ' . count($actions));
            foreach ($actions as $action) {
                $this->line("  → [{$action['type']}] week_id={$action['week_id']}");
            }
        }

        return self::SUCCESS;
    }
}
