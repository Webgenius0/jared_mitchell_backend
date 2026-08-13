<?php

use App\Console\Commands\Contest\RunContestScheduler;
use App\Console\Commands\RunSpotlightScheduler;
use App\Console\Commands\SpotlightSelectWinner;
use App\Jobs\Spotlight\CreateSpotlightWeeks;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Contest: Master scheduler (every 5 minutes) ──
// Checks season openings/closings, round openings, and dispatches round transitions.
Schedule::command(RunContestScheduler::class)->everyFiveMinutes()->withoutOverlapping();

// ── Spotlight: Create weekly cycles every Monday 12:00 AM ──
// Creates a new artist + business spotlight week for Mon–Sun cycle.
Schedule::job(new CreateSpotlightWeeks)->weeklyOn(1, '00:00');

// ── Spotlight: Master scheduler (every 5 minutes) ──
// Handles: pending→nominating transitions and dispatches voting close when time expires.
Schedule::command(RunSpotlightScheduler::class)->everyFiveMinutes()->withoutOverlapping();

// ── Spotlight: Select winner command ──
Artisan::command('spotlight:select-winner {week?} {--force}', function ($week = null) {
    return $this->call(SpotlightSelectWinner::class, [
        'week' => $week,
        '--force' => $this->option('force'),
    ]);
})->purpose('Close voting for a spotlight week and select the winner based on votes');




