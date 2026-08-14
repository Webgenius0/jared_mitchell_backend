<?php

namespace App\Services\Contest;

use App\Jobs\Contest\AutoProcessEliminations;
use App\Jobs\Contest\OpenRoundForSubmissions;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Notifications\Contest\SeasonStartingNotification;
use App\Services\Contest\ContestNotificationService;
use Illuminate\Support\Facades\Log;

class SchedulerService
{
    public function __construct(
        protected ContestNotificationService $notificationService,
        protected ?AutoSeasonBuilderService $autoSeasonBuilder = null,
    ) {
        $this->autoSeasonBuilder ??= app(AutoSeasonBuilderService::class);
    }

    /**
     * Run a full scheduler check across all seasons and rounds.
     * This is designed to be called on a cron schedule (every 1-5 minutes).
     *
     * Returns a summary of actions taken.
     */
    public function run(): array
    {
        $actions = [];

        // ── 0. Seasons: Auto-create upcoming season if needed ──
        $actions = array_merge($actions, $this->checkAutoSeasonCreation());

        // ── 1. Seasons: Open for applications ──
        $actions = array_merge($actions, $this->checkSeasonApplicationOpenings());

        // ── 2. Seasons: Close applications ──
        $actions = array_merge($actions, $this->checkSeasonApplicationClosings());

        // ── 3. Seasons: Start competition ──
        $actions = array_merge($actions, $this->checkSeasonCompetitionStart());

        // ── 4. Rounds: Open for submissions ──
        $actions = array_merge($actions, $this->checkRoundOpenings());

        // ── 5. Rounds: Close and trigger transitions ──
        $actions = array_merge($actions, $this->checkRoundClosings());

        // ── 6. Rounds: Open voting ──
        $actions = array_merge($actions, $this->checkVotingPeriods());

        // ── 7. Seasons: Auto-close completed seasons ──
        $actions = array_merge($actions, $this->checkSeasonClosings());

        // ── 8. Seasons: Auto-create upcoming season if needed ──
        $actions = array_merge($actions, $this->checkAutoSeasonCreation());

        Log::info('SchedulerService: Run completed', [
            'actions_taken' => count($actions),
            'actions'       => $actions,
        ]);

        return $actions;
    }

    /**
     * Open seasons that have reached their application start time.
     */
    public function checkSeasonApplicationOpenings(): array
    {
        $actions = [];

        $seasons = Season::where('status', 'draft')
            ->where('applications_starts_at', '<=', now())
            ->whereNotNull('applications_starts_at')
            ->get();

        foreach ($seasons as $season) {
            $season->update([
                'status'     => 'open',
                'is_active'  => true,
            ]);

            Log::info('Scheduler: Season opened for applications', [
                'season_id' => $season->id,
                'title'     => $season->title,
            ]);

            $actions[] = [
                'type'       => 'season_opened_for_applications',
                'season_id'  => $season->id,
                'title'      => $season->title,
            ];
        }

        return $actions;
    }

    /**
     * Close seasons that have passed their application end time.
     */
    public function checkSeasonApplicationClosings(): array
    {
        $actions = [];

        $seasons = Season::where('status', 'open')
            ->where('applications_ends_at', '<=', now())
            ->whereNotNull('applications_ends_at')
            ->get();

        foreach ($seasons as $season) {
            $season->update([
                'status' => 'applications_closed',
            ]);

            Log::info('Scheduler: Season applications closed', [
                'season_id' => $season->id,
                'title'     => $season->title,
            ]);

            $actions[] = [
                'type'      => 'season_applications_closed',
                'season_id' => $season->id,
                'title'     => $season->title,
            ];
        }

        return $actions;
    }

    /**
     * Start the competition phase for seasons that have reached their start time.
     */
    public function checkSeasonCompetitionStart(): array
    {
        $actions = [];

        $seasons = Season::whereIn('status', ['open', 'applications_closed'])
            ->where('starts_at', '<=', now())
            ->whereNotNull('starts_at')
            ->get();

        foreach ($seasons as $season) {
            $season->update([
                'status' => 'in_progress',
            ]);

            // Notify all active contestants about the season starting
            $activeContestants = $season->contestants()->where('status', 'active')->get();
            $firstRound = $season->rounds()->orderBy('round_number')->orderBy('sort_order')->first();

            foreach ($activeContestants as $contestant) {
                $user = $this->notificationService->resolveContestantUser($contestant);
                if ($user) {
                    $user->notify(new SeasonStartingNotification(
                        season: $season,
                        firstRoundTitle: $firstRound?->title,
                    ));
                }
            }

            if ($firstRound) {
                OpenRoundForSubmissions::dispatch($firstRound);
            }

            Log::info('Scheduler: Season competition started', [
                'season_id' => $season->id,
                'title'     => $season->title,
            ]);

            $actions[] = [
                'type'       => 'season_competition_started',
                'season_id'  => $season->id,
                'title'      => $season->title,
            ];
        }

        return $actions;
    }

    /**
     * Open rounds that have reached their start time.
     */
    public function checkRoundOpenings(): array
    {
        $actions = [];

        $rounds = Round::where(function ($q) {
                $q->where('is_active', false)->orWhereNull('is_active');
            })
            ->where('starts_at', '<=', now())
            ->whereNotNull('starts_at')
            ->get();

        foreach ($rounds as $round) {
            $round->update([
                'is_active' => true,
                'starts_at' => $round->starts_at ?? now(),
            ]);

            OpenRoundForSubmissions::dispatchSync($round);

            Log::info('Scheduler: Round opening dispatched and activated', [
                'round_id'     => $round->id,
                'round_number' => $round->round_number,
            ]);

            $actions[] = [
                'type'          => 'round_opening_dispatched',
                'round_id'      => $round->id,
                'round_number'  => $round->round_number,
            ];
        }

        return $actions;
    }

    /**
     * Check for rounds that have ended and need transitions.
     */
    public function checkRoundClosings(): array
    {
        $actions = [];

        // Find active rounds whose ends_at time has passed (ends_at <= now)
        $endedRounds = Round::where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();

        foreach ($endedRounds as $round) {
            // Process elimination for this specific round: sets current round is_active=false and activates next round
            ProcessRoundTransition::dispatchSync($round);

            Log::info("Scheduler: Processed elimination for round {$round->id} (Round {$round->round_number})");

            $actions[] = [
                'type'          => 'round_elimination_processed',
                'round_id'      => $round->id,
                'round_number'  => $round->round_number,
            ];
        }

        return $actions;
    }

    /**
     * Voting periods are handled naturally by Round::isVotingOpen() and the VoteController.
     * Voting opens automatically when the round starts (starts_at <= now)
     * and closes when voting_ends_at (or ends_at) is reached.
     * No scheduling actions needed at the queue level.
     */
    public function checkVotingPeriods(): array
    {
        return [];
    }

    /**
     * Close seasons that have passed their end time.
     */
    public function checkSeasonClosings(): array
    {
        $actions = [];

        $seasons = Season::whereIn('status', ['in_progress', 'open'])
            ->where('ends_at', '<=', now())
            ->whereNotNull('ends_at')
            ->get();

        foreach ($seasons as $season) {
            $season->update([
                'status'    => 'completed',
                'is_active' => false,
            ]);

            Log::info('Scheduler: Season completed (time-based)', [
                'season_id' => $season->id,
                'title'     => $season->title,
            ]);

            $actions[] = [
                'type'      => 'season_completed',
                'season_id' => $season->id,
                'title'     => $season->title,
            ];
        }

        return $actions;
    }

    /**
     * Ensure an upcoming Boss Beginnings season is scheduled automatically.
     */
    public function checkAutoSeasonCreation(): array
    {
        $actions = [];

        $createdSeason = $this->autoSeasonBuilder->ensureUpcomingSeasonExists();

        if ($createdSeason) {
            Log::info('Scheduler: Upcoming season auto-created', [
                'season_id' => $createdSeason->id,
                'title'     => $createdSeason->title,
                'starts_at' => $createdSeason->starts_at?->toIso8601String(),
                'ends_at'   => $createdSeason->ends_at?->toIso8601String(),
            ]);

            $actions[] = [
                'type'      => 'season_auto_created',
                'season_id' => $createdSeason->id,
                'title'     => $createdSeason->title,
                'starts_at' => $createdSeason->starts_at?->toIso8601String(),
                'ends_at'   => $createdSeason->ends_at?->toIso8601String(),
            ];
        }

        return $actions;
    }
}
