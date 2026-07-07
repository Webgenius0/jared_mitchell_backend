<?php

namespace App\Services\Contest;

use App\Models\Contest\Contestant;
use App\Models\Contest\LeaderboardEntry;
use App\Models\Contest\Vote;
use App\Models\Round;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
    /**
     * Calculate and cache the leaderboard for a specific round.
     *
     * Snapshot is denormalized for fast rendering — avoids N+1 on contestable.
     */
    public function calculateLeaderboard(Round $round): void
    {
        $seasonId = $round->season_id;

        // Get all contestants in this round with their vote aggregates
        $contestants = Contestant::where('season_id', $seasonId)
            ->where('current_round_id', $round->id)
            ->where('status', 'active')
            ->get();

        // Get vote counts per contestant for this round
        $voteAggregates = Vote::where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->selectRaw('votable_id as contestant_id')
            ->selectRaw('COUNT(*) as votes_count')
            ->selectRaw('SUM(weight) as total_weighted')
            ->selectRaw('AVG(weight) as avg_score')
            ->groupBy('votable_id')
            ->get()
            ->keyBy('contestant_id');

        // Build leaderboard entries
        $leaderboard = [];
        foreach ($contestants as $contestant) {
            $aggregate = $voteAggregates->get($contestant->id);

            $totalScore = $aggregate
                ? (float) $aggregate->total_weighted
                : 0.0;

            $votesCount = $aggregate
                ? (int) $aggregate->votes_count
                : 0;

            $avgScore = $aggregate && $votesCount > 0
                ? round((float) $aggregate->avg_score, 2)
                : null;

            // Build snapshot for fast rendering
            $contestable = $contestant->contestable;
            $snapshot = [
                'display_name'  => $contestant->display_name,
                'avatar_url'    => $contestant->avatar_url,
                'contestant_slug' => $contestant->slug,
                'contestable_name' => $contestable ? $contestable->getContestantName() : null,
                'contestable_avatar'=> $contestable ? $contestable->getContestantAvatar() : null,
            ];

            $leaderboard[] = [
                'season_id'      => $seasonId,
                'round_id'       => $round->id,
                'contestant_id'  => $contestant->id,
                'total_score'    => $totalScore,
                'votes_count'    => $votesCount,
                'avg_score'      => $avgScore,
                'snapshot'       => $snapshot,
                'calculated_at'  => now(),
            ];
        }

        // Sort by total_score descending, then votes_count
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $b['votes_count'] <=> $a['votes_count'];
        });

        // Assign ranks (with tie handling: 1, 1, 3, 4, 4, 6...)
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if ($i > 0
                && $entry['total_score'] === $leaderboard[$i - 1]['total_score']
                && $entry['votes_count'] === $leaderboard[$i - 1]['votes_count']
            ) {
                // Tie — same rank as previous
                $entry['rank'] = $leaderboard[$i - 1]['rank'];
            } else {
                $entry['rank'] = $rank;
            }
            $rank++;
        }
        unset($entry);

        // Upsert all entries in a transaction
        DB::transaction(function () use ($round, $leaderboard, $seasonId) {
            // Remove old entries for this round
            LeaderboardEntry::where('round_id', $round->id)->delete();

            // Insert new entries
            foreach ($leaderboard as $entry) {
                LeaderboardEntry::create($entry);
            }
        });

        // Also update overall leaderboard for the season
        $this->calculateOverallLeaderboard($seasonId, $round);

        Log::info('Leaderboard calculated', [
            'season_id' => $seasonId,
            'round_id'  => $round->id,
            'entries'   => count($leaderboard),
        ]);
    }

    /**
     * Calculate the overall season leaderboard (aggregates across all rounds).
     */
    public function calculateOverallLeaderboard(int $seasonId, ?Round $currentRound = null): void
    {
        $entries = LeaderboardEntry::where('season_id', $seasonId)
            ->whereNotNull('round_id')
            ->selectRaw('contestant_id')
            ->selectRaw('SUM(total_score) as total_score')
            ->selectRaw('SUM(votes_count) as votes_count')
            ->selectRaw('AVG(avg_score) as avg_score')
            ->groupBy('contestant_id')
            ->get();

        DB::transaction(function () use ($seasonId, $entries) {
            // Remove old overall entries
            LeaderboardEntry::where('season_id', $seasonId)
                ->whereNull('round_id')
                ->delete();

            foreach ($entries as $entry) {
                $contestant = Contestant::find($entry->contestant_id);
                $snapshot = [];
                if ($contestant) {
                    $contestable = $contestant->contestable;
                    $snapshot = [
                        'display_name'  => $contestant->display_name,
                        'avatar_url'    => $contestant->avatar_url,
                        'contestant_slug' => $contestant->slug,
                        'contestable_name' => $contestable ? $contestable->getContestantName() : null,
                        'contestable_avatar'=> $contestable ? $contestable->getContestantAvatar() : null,
                    ];
                }

                LeaderboardEntry::create([
                    'season_id'     => $seasonId,
                    'round_id'      => null,
                    'contestant_id' => $entry->contestant_id,
                    'total_score'   => $entry->total_score,
                    'votes_count'   => $entry->votes_count,
                    'avg_score'     => round((float) $entry->avg_score, 2),
                    'snapshot'      => $snapshot,
                    'calculated_at' => now(),
                ]);
            }
        });

        // Re-rank the overall entries
        $this->reRankOverall($seasonId);
    }

    /**
     * Re-rank overall leaderboard after recalculation.
     */
    private function reRankOverall(int $seasonId): void
    {
        $entries = LeaderboardEntry::where('season_id', $seasonId)
            ->whereNull('round_id')
            ->orderByDesc('total_score')
            ->orderByDesc('votes_count')
            ->get();

        $rank = 1;
        foreach ($entries as $i => $entry) {
            if ($i > 0
                && $entry->total_score === $entries[$i - 1]->total_score
                && $entry->votes_count === $entries[$i - 1]->votes_count
            ) {
                $entry->update(['rank' => $entries[$i - 1]->rank]);
            } else {
                $entry->update(['rank' => $rank]);
            }
            $rank++;
        }
    }

    /**
     * Get the cached leaderboard for a round (with fallback to live calculation).
     */
    public function getLeaderboard(Round $round)
    {
        $entries = LeaderboardEntry::forRound($round->id)
            ->ranked()
            ->get();

        // If no cached entries exist, calculate now
        if ($entries->isEmpty()) {
            $this->calculateLeaderboard($round);
            $entries = LeaderboardEntry::forRound($round->id)
                ->ranked()
                ->get();
        }

        return $entries;
    }

    /**
     * Get the overall season leaderboard.
     */
    public function getOverallLeaderboard(int $seasonId)
    {
        $entries = LeaderboardEntry::forSeason($seasonId)
            ->overall()
            ->ranked()
            ->get();

        if ($entries->isEmpty()) {
            // Calculate from the latest round
            $latestRound = Round::where('season_id', $seasonId)
                ->orderByDesc('round_number')
                ->first();

            if ($latestRound) {
                $this->calculateLeaderboard($latestRound);
                $entries = LeaderboardEntry::forSeason($seasonId)
                    ->overall()
                    ->ranked()
                    ->get();
            }
        }

        return $entries;
    }
}
