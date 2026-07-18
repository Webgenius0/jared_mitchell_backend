<?php

namespace App\Services\Contest;

use App\Models\Contest\Contestant;
use App\Models\Contest\Vote;
use App\Models\Round;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
    /**
     * Get ranked leaderboard for a specific round, calculated on-demand from votes.
     */
    public function getLeaderboard(Round $round): array
    {
        $seasonId = $round->season_id;

        // Get all active contestants in this round
        $contestants = Contestant::where('season_id', $seasonId)
            ->where('current_round_id', $round->id)
            ->where('status', 'active')
            ->with('contestable')
            ->get();

        // Get vote aggregates per contestant for this round
        $voteAggregates = Vote::where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->selectRaw('votable_id as contestant_id')
            ->selectRaw('COUNT(*) as votes_count')
            ->selectRaw('COALESCE(SUM(weight), 0) as total_weighted')
            ->selectRaw('AVG(weight) as avg_score')
            ->groupBy('votable_id')
            ->get()
            ->keyBy('contestant_id');

        // Calculate trends (votes today vs yesterday)
        $todayVotes = Vote::where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->where('created_at', '>=', now()->startOfDay())
            ->selectRaw('votable_id as contestant_id, COUNT(*) as count')
            ->groupBy('votable_id')
            ->pluck('count', 'contestant_id');

        $yesterdayVotes = Vote::where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()])
            ->selectRaw('votable_id as contestant_id, COUNT(*) as count')
            ->groupBy('votable_id')
            ->pluck('count', 'contestant_id');

        // Build leaderboard
        $leaderboard = [];
        foreach ($contestants as $contestant) {
            $aggregate = $voteAggregates->get($contestant->id);

            $totalScore = $aggregate ? (float) $aggregate->total_weighted : 0.0;
            $votesCount = $aggregate ? (int) $aggregate->votes_count : 0;
            $avgScore = $aggregate && $votesCount > 0 ? round((float) $aggregate->avg_score, 2) : null;

            $contestable = $contestant->contestable;

            $tVotes = $todayVotes->get($contestant->id, 0);
            $yVotes = $yesterdayVotes->get($contestant->id, 0);

            $trend = 'neutral';
            if ($tVotes > $yVotes) {
                $trend = 'up';
            } elseif ($tVotes < $yVotes) {
                $trend = 'down';
            }

            $leaderboard[] = [
                'contestant' => $contestant,
                'contestant_id' => $contestant->id,
                'display_name' => $contestant->display_name,
                'avatar_url' => $contestant->avatar_url,
                'contestable_name'=> $contestable ? $contestable->getContestantName() : null,
                'total_score' => $totalScore,
                'votes_count' => $votesCount,
                'avg_score' => $avgScore,
                'trend' => $trend,
            ];
        }

        // Sort by total_score descending, then votes_count
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $b['votes_count'] <=> $a['votes_count'];
        });

        // Assign ranks (with tie handling)
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if ($i > 0
                && $entry['total_score'] === $leaderboard[$i - 1]['total_score']
                && $entry['votes_count'] === $leaderboard[$i - 1]['votes_count']
            ) {
                $entry['rank'] = $leaderboard[$i - 1]['rank'];
            } else {
                $entry['rank'] = $rank;
            }
            $rank++;
        }
        unset($entry);

        return $leaderboard;
    }

    /**
     * Get the overall season leaderboard (aggregated across all rounds).
     */
    public function getOverallLeaderboard(int $seasonId): array
    {
        // Sum vote scores across all rounds for active contestants
        $contestants = Contestant::where('season_id', $seasonId)
            ->whereIn('status', ['active', 'winner', 'runner_up', 'finalist'])
            ->with('contestable')
            ->get();

        $leaderboard = [];

        foreach ($contestants as $contestant) {
            $voteData = Vote::where('votable_type', Contestant::class)
                ->where('votable_id', $contestant->id)
                ->selectRaw('COUNT(*) as votes_count')
                ->selectRaw('COALESCE(SUM(weight), 0) as total_score')
                ->first();

            $contestable = $contestant->contestable;

            $leaderboard[] = [
                'contestant'      => $contestant,
                'contestant_id'   => $contestant->id,
                'display_name'    => $contestant->display_name,
                'avatar_url'      => $contestant->avatar_url,
                'contestable_name'=> $contestable ? $contestable->getContestantName() : null,
                'total_score'     => (float) ($voteData->total_score ?? 0),
                'votes_count'     => (int) ($voteData->votes_count ?? 0),
                'trend'           => 'neutral', // Overall trend can be neutral or calculated later
            ];
        }

        // Sort by total_score descending
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $b['votes_count'] <=> $a['votes_count'];
        });

        // Assign ranks
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if ($i > 0
                && $entry['total_score'] === $leaderboard[$i - 1]['total_score']
                && $entry['votes_count'] === $leaderboard[$i - 1]['votes_count']
            ) {
                $entry['rank'] = $leaderboard[$i - 1]['rank'];
            } else {
                $entry['rank'] = $rank;
            }
            $rank++;
        }
        unset($entry);

        return $leaderboard;
    }
}
