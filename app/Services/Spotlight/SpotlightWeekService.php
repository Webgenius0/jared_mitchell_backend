<?php

namespace App\Services\Spotlight;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpotlightWeekService
{
    /**
     * Create a new spotlight week.
     *
     * @param  Carbon  $votingStartsAt  Typically Monday 12:00 AM
     * @param  Carbon  $votingEndsAt    Typically Sunday 11:59:59 PM
     */
    public function createWeek(
        Carbon $votingStartsAt,
        Carbon $votingEndsAt,
        ?array $metadata = null
    ): SpotlightWeek {
        $weekNumber = (int) $votingStartsAt->isoWeek();
        $year       = (int) $votingStartsAt->year;

        $week = SpotlightWeek::create([
            'week_number'      => $weekNumber,
            'year'             => $year,
            'status'           => 'pending',
            'voting_starts_at' => $votingStartsAt,
            'voting_ends_at'   => $votingEndsAt,
            'metadata'         => $metadata,
        ]);

        Log::info('SpotlightWeekService: Week created', [
            'id'   => $week->id,
            'week' => "{$year}-W{$weekNumber}",
        ]);

        return $week;
    }

    /**
     * Promote selected applications to nominees (Top 12) for a week.
     * Transitions week status from 'nominating' → 'voting'.
     *
     * @param  SpotlightWeek  $week
     * @param  array<int>     $applicationIds  IDs of SpotlightApplication to select
     */
    public function selectNominees(SpotlightWeek $week, array $applicationIds): array
    {
        if (! in_array($week->status, ['pending', 'nominating'])) {
            return [
                'success' => false,
                'message' => "Week is in '{$week->status}' status. Cannot select nominees.",
            ];
        }

        if (count($applicationIds) > 12) {
            return [
                'success' => false,
                'message' => 'You can select a maximum of 12 nominees.',
            ];
        }

        DB::transaction(function () use ($week, $applicationIds) {
            // Reject all pending applications for this week first
            SpotlightApplication::where('spotlight_week_id', $week->id)
                ->where('status', 'pending')
                ->whereNotIn('id', $applicationIds)
                ->update([
                    'status'      => 'rejected',
                    'reviewed_at' => now(),
                ]);

            // Select chosen applications and create nominees
            $applications = SpotlightApplication::whereIn('id', $applicationIds)
                ->where('spotlight_week_id', $week->id)
                ->get();

            foreach ($applications as $application) {
                // Mark application as selected
                $application->update([
                    'status'      => 'selected',
                    'reviewed_at' => now(),
                ]);

                // Create the nominee record
                SpotlightWeekNominee::firstOrCreate(
                    [
                        'spotlight_week_id'   => $week->id,
                        'spotlightable_type'  => $application->spotlightable_type,
                        'spotlightable_id'    => $application->spotlightable_id,
                    ],
                    [
                        'user_id'             => $application->user_id,
                        'free_vote_count'     => 0,
                        'paid_vote_count'     => 0,
                        'total_vote_count'    => 0,
                        'is_winner'           => false,
                    ]
                );
            }

            // Open voting
            $week->update(['status' => 'voting']);
        });

        Log::info('SpotlightWeekService: Nominees selected, voting opened', [
            'week_id'          => $week->id,
            'application_ids'  => $applicationIds,
        ]);

        return [
            'success'  => true,
            'message'  => 'Nominees selected and voting is now open.',
            'nominees' => $week->fresh()->nominees()->with('spotlightable')->get(),
        ];
    }

    /**
     * Close voting for a week, rank all nominees, set the winner.
     * Transitions week status to 'completed'.
     */
    public function closeVoting(SpotlightWeek $week): array
    {
        if ($week->status !== 'voting') {
            return [
                'success' => false,
                'message' => "Week is not in 'voting' status. Current: {$week->status}",
            ];
        }

        DB::transaction(function () use ($week) {
            // Order nominees by total votes desc, then free votes as tiebreaker
            $nominees = $week->nominees()
                ->orderByDesc('total_vote_count')
                ->orderByDesc('free_vote_count')
                ->get();

            $rank   = 1;
            $winner = null;

            foreach ($nominees as $nominee) {
                $isWinner = ($rank === 1);

                $nominee->update([
                    'rank'      => $rank,
                    'is_winner' => $isWinner,
                ]);

                if ($isWinner) {
                    $winner = $nominee;
                }

                $rank++;
            }

            // Update the week with winner info
            $week->update([
                'status'                     => 'completed',
                'winner_spotlightable_type'  => $winner?->spotlightable_type,
                'winner_spotlightable_id'    => $winner?->spotlightable_id,
                'announced_at'               => $week->announced_at ?? now(),
            ]);
        });

        Log::info('SpotlightWeekService: Voting closed, winner determined', [
            'week_id'                    => $week->id,
            'winner_spotlightable_type'  => $week->fresh()->winner_spotlightable_type,
            'winner_spotlightable_id'    => $week->fresh()->winner_spotlightable_id,
        ]);

        return [
            'success'   => true,
            'message'   => 'Voting closed. Winner determined.',
            'winner'    => $week->fresh()->nominees()->where('is_winner', true)->with('spotlightable', 'user')->first(),
            'leaderboard' => $week->nominees()->orderBy('rank')->with('spotlightable')->get(),
        ];
    }

    /**
     * Announce the winner (set announced_at timestamp).
     * Typically called after admin verifies and confirms the winner.
     */
    public function announceWinner(SpotlightWeek $week): array
    {
        if ($week->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Week must be completed before announcing the winner.',
            ];
        }

        $week->update(['announced_at' => now()]);

        Log::info('SpotlightWeekService: Winner announced', ['week_id' => $week->id]);

        return [
            'success' => true,
            'message' => 'Winner announced successfully.',
        ];
    }

    /**
     * Get the current active voting week.
     */
    public function getCurrentVotingWeek(): ?SpotlightWeek
    {
        return SpotlightWeek::votingOpen()
            ->latest()
            ->first();
    }

    /**
     * Get the most recent completed (announced) week.
     *
     * @param  string|null  $type  Optional. Filter by spotlight type: 'artist', 'business', or null for any.
     */
    public function getLastWinner(?string $type = null): ?SpotlightWeekNominee
    {
        $query = SpotlightWeekNominee::where('is_winner', true)
            ->whereHas('week', function ($q) {
                $q->where('status', 'completed');
            });

        if ($type === 'artist') {
            $query->where('spotlightable_type', ArtistSpotlight::class);
        } elseif ($type === 'business') {
            $query->where('spotlightable_type', BusinessSpotlight::class);
        }

        return $query->with(['spotlightable', 'user.profile', 'week'])
            ->orderByDesc('id')
            ->first();
    }
}
