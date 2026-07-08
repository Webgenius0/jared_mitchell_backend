<?php

use App\Console\Commands\Contest\RunContestScheduler;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Contest: Master scheduler (every 5 minutes) ──
// Checks season openings/closings, round openings, and dispatches round transitions.
Schedule::command(RunContestScheduler::class)->everyFiveMinutes()->withoutOverlapping();
