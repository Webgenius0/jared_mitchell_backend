<?php

namespace App\Console\Commands\Contest;

use App\Jobs\Contest\RunScheduler;
use App\Services\Contest\SchedulerService;
use Illuminate\Console\Command;

class RunContestScheduler extends Command
{
    protected $signature = 'contest:scheduler{--sync : Run synchronously instead of dispatching a job}';

    protected $description = 'Run the contest scheduler — check timing conditions and dispatch needed actions';

    public function handle(): int
    {
        $sync = $this->option('sync');

        if ($sync) {
            $scheduler = app(SchedulerService::class);
            $actions = $scheduler->run();

            $this->info('Scheduler ran synchronously.');
            $this->line('Actions taken: ' . count($actions));

            foreach ($actions as $action) {
                $this->line('  - [' . $action['type'] . '] ' . json_encode($action));
            }
        } else {
            RunScheduler::dispatch();
            $this->info('Scheduler job dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}
