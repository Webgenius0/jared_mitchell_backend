<?php

namespace App\Services\Spotlight;

use App\Jobs\Spotlight\CloseSpotlightVoting;
use App\Jobs\Spotlight\CreateSpotlightWeeks;
use App\Models\Spotlight\SpotlightWeek;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SpotlightSchedulerService
{
    public function __construct(
        protected SpotlightWeekService $weekService,
    ) {}

    /**
     * Master scheduler method — call this from the Laravel Scheduler every 5 minutes
     * to handle all spotlight week state transitions.
     */
    public function run(): array
    {
        $actions = [];

        $actions = array_merge($actions, $this->checkAndCreateWeeks());
        $actions = array_merge($actions, $this->checkAndOpenVoting());
        $actions = array_merge($actions, $this->checkAndCloseVoting());

        Log::info('SpotlightSchedulerService: Run completed', [
            'actions_taken' => count($actions),
            'actions'       => $actions,
        ]);

        return $actions;
    }

    /**
     * Create spotlight weeks for the current week AND the upcoming (next) week
     * if they don't exist yet (manages both artist and business spotlights).
     */
    public function checkAndCreateWeeks(): array
    {
        $actions = [];
        $now     = now();

        // 1. Current Week (Monday 12:00 AM → Sunday 11:59:59 PM)
        $currentMonday = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $currentSunday = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $currentWeekNumber = (int) $currentMonday->isoWeek();
        $currentYear       = (int) $currentMonday->year;

        $currentExists = SpotlightWeek::where('week_number', $currentWeekNumber)
            ->where('year', $currentYear)
            ->exists();

        if (! $currentExists) {
            $week = $this->weekService->createWeek($currentMonday->copy(), $currentSunday->copy());

            Log::info('SpotlightSchedulerService: Current week auto-created', [
                'week_id' => $week->id,
                'week'    => "{$currentYear}-W{$currentWeekNumber}",
            ]);

            $actions[] = [
                'type'    => 'spotlight_week_created',
                'week_id' => $week->id,
                'week'    => "{$currentYear}-W{$currentWeekNumber}",
            ];
        }

        // 2. Upcoming Week (Next Monday 12:00 AM → Next Sunday 11:59:59 PM)
        $nextMonday = $currentMonday->copy()->addWeek();
        $nextSunday = $currentSunday->copy()->addWeek();

        $nextWeekNumber = (int) $nextMonday->isoWeek();
        $nextYear       = (int) $nextMonday->year;

        $nextExists = SpotlightWeek::where('week_number', $nextWeekNumber)
            ->where('year', $nextYear)
            ->exists();

        if (! $nextExists) {
            $nextWeek = $this->weekService->createWeek($nextMonday->copy(), $nextSunday->copy());

            Log::info('SpotlightSchedulerService: Upcoming week auto-created', [
                'week_id' => $nextWeek->id,
                'week'    => "{$nextYear}-W{$nextWeekNumber}",
            ]);

            $actions[] = [
                'type'    => 'spotlight_upcoming_week_created',
                'week_id' => $nextWeek->id,
                'week'    => "{$nextYear}-W{$nextWeekNumber}",
            ];
        }

        return $actions;
    }

    /**
     * Open voting for any weeks that have reached their voting_starts_at time
     * AND have nominees selected (status = 'voting' already set by selectNominees).
     * This is a passive check — actual open happens when admin selects nominees.
     * We just log / flag weeks that should have started but haven't been actioned.
     */
    public function checkAndOpenVoting(): array
    {
        $actions = [];

        // Find weeks that should be in voting but are still in 'nominating'
        // (admin forgot to select nominees — we log a warning)
        $overdueNominating = SpotlightWeek::where('status', 'nominating')
            ->where('voting_starts_at', '<=', now())
            ->get();

        foreach ($overdueNominating as $week) {
            Log::warning('SpotlightSchedulerService: Week is overdue for nominee selection', [
                'week_id' => $week->id,
                'started' => $week->voting_starts_at,
            ]);

            $actions[] = [
                'type'    => 'spotlight_week_overdue_nominating',
                'week_id' => $week->id,
            ];
        }

        // Also auto-transition 'pending' weeks to 'nominating' when voting window starts
        $pendingWeeks = SpotlightWeek::where('status', 'pending')
            ->where('voting_starts_at', '<=', now())
            ->get();

        foreach ($pendingWeeks as $week) {
            $week->update(['status' => 'nominating']);

            Log::info('SpotlightSchedulerService: Week transitioned to nominating', [
                'week_id' => $week->id,
            ]);

            $actions[] = [
                'type'    => 'spotlight_week_opened_for_nominating',
                'week_id' => $week->id,
            ];
        }

        return $actions;
    }

    /**
     * Close voting for any weeks whose voting_ends_at has passed.
     * Dispatches the CloseSpotlightVoting job per week.
     */
    public function checkAndCloseVoting(): array
    {
        $actions = [];

        $expiredWeeks = SpotlightWeek::where('status', 'voting')
            ->where('voting_ends_at', '<=', now())
            ->get();

        foreach ($expiredWeeks as $week) {
            CloseSpotlightVoting::dispatch($week->id);

            Log::info('SpotlightSchedulerService: Closing voting dispatched', [
                'week_id' => $week->id,
            ]);

            $actions[] = [
                'type'    => 'spotlight_voting_close_dispatched',
                'week_id' => $week->id,
            ];
        }

        return $actions;
    }
}
