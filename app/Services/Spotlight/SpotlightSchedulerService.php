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
     * Create one spotlight week for the upcoming Monday–Sunday cycle
     * if it doesn't exist yet (manages both artist and business spotlights).
     * Typically triggered Monday 12:00 AM by the scheduler.
     */
    public function checkAndCreateWeeks(): array
    {
        $actions = [];
        $now     = now();

        // Determine current week's Monday 12:00 AM → Sunday 11:59:59 PM
        $monday  = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $sunday  = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $weekNumber = (int) $monday->isoWeek();
        $year       = (int) $monday->year;

        $exists = SpotlightWeek::where('week_number', $weekNumber)
            ->where('year', $year)
            ->exists();

        if (! $exists) {
            $week = $this->weekService->createWeek($monday->copy(), $sunday->copy());

            Log::info('SpotlightSchedulerService: Week auto-created', [
                'week_id' => $week->id,
                'week'    => "{$year}-W{$weekNumber}",
            ]);

            $actions[] = [
                'type'    => 'spotlight_week_created',
                'week_id' => $week->id,
                'week'    => "{$year}-W{$weekNumber}",
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
