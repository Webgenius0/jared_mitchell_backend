<?php

namespace App\Console\Commands;

use App\Services\Contest\SchedulerService as ContestScheduler;
use App\Services\Spotlight\SpotlightSchedulerService as SpotlightScheduler;
use Illuminate\Console\Command;

class RunAllSchedulers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:run-schedulers';

    /**
     * The console command description.
     */
    protected $description = 'Run both Contest Scheduler and Spotlight Scheduler together';

    public function handle(
        ContestScheduler $contestScheduler,
        SpotlightScheduler $spotlightScheduler
    ): int {
        $this->info('========================================');
        $this->info('🏆 Running Contest Scheduler...');
        $this->info('========================================');
        $contestActions = $contestScheduler->run();

        if (empty($contestActions)) {
            $this->line('   No contest actions needed.');
        } else {
            $this->info('   Contest Actions taken: ' . count($contestActions));
            foreach ($contestActions as $action) {
                $this->line("   - [{$action['type']}]");
            }
        }

        $this->newLine();

        $this->info('========================================');
        $this->info('⭐ Running Spotlight Scheduler...');
        $this->info('========================================');
        $spotlightActions = $spotlightScheduler->run();

        if (empty($spotlightActions)) {
            $this->line('   No spotlight actions needed.');
        } else {
            $this->info('   Spotlight Actions taken: ' . count($spotlightActions));
            foreach ($spotlightActions as $action) {
                $this->line("   - [{$action['type']}]");
            }
        }

        $this->newLine();
        $this->info('✅ All schedulers completed successfully!');

        return Command::SUCCESS;
    }
}
