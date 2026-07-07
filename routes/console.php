<?php

use App\Console\Commands\Contest\ProcessRoundTransitions;
use App\Console\Commands\Contest\RunContestScheduler;
use App\Jobs\Contest\AutoProcessEliminations;
use App\Jobs\Contest\RunScheduler;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Contest: Master scheduler (every 5 minutes) ──
// Checks season openings/closings, round openings, and dispatches transitions.
Schedule::job(new RunScheduler, 'high')->everyFiveMinutes();

// ── Contest: Fallback — check for ended rounds every 5 minutes (backup) ──
Schedule::job(new AutoProcessEliminations, 'high')->everyFiveMinutes();

// ── Contest: Manually triggered transition processor ──
Artisan::command('contest:process-round-transitions', function () {
    $this->call(ProcessRoundTransitions::class);
})->purpose('Process round transitions — advance winners, eliminate losers');

// ── Contest: Manually triggered scheduler ──
Artisan::command('contest:scheduler', function () {
    $this->call(RunContestScheduler::class);
})->purpose('Run contest scheduler — check timing conditions');
