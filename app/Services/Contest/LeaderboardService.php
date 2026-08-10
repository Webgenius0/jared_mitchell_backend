<?php

namespace App\Services\Contest;

use App\Models\Business;
use App\Models\BusinessInteraction;
use App\Models\Contest\Contestant;
use App\Models\Contest\Vote;
use App\Models\Round;
use App\Services\BusinessService;
use Illuminate\Support\Collection;

class LeaderboardService
{
    /**
     * Get ranked leaderboard for a specific round, calculated on-demand from votes.
     */
    public function getLeaderboard(Round $round): array
    {
        $seasonId = $round->season_id;

        if ($round->round_number === 1) {
            return $this->getRoundOneLeaderboard($round);
        }

        // Get all active contestants in this round
        $contestants = Contestant::where('season_id', $seasonId)
            ->where('current_round_id', $round->id)
            ->where('status', 'active')
            ->with(['contestable.media', 'submissions.round'])
            ->get();

        // Clap/save/share counts for THIS round only, so a business that
        // registered a new session starts at 0 and grows as users interact.
        $interactionCounts = $this->interactionCountsByBusiness($round->id);

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
            $roundInteractions = $this->roundInteractionCounts($interactionCounts, $contestable);

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
                'avatar_url' => $this->avatarFor($contestant),
                'contestable_name' => $contestable ? $contestable->getContestantName() : null,
                'total_score' => $totalScore,
                'votes_count' => $votesCount,
                'avg_score' => $avgScore,
                'claps'  => $roundInteractions['claps'],
                'shares' => $roundInteractions['shares'],
                'saves'  => $roundInteractions['saves'],
                'total_points' => $roundInteractions['total_points'],
                'trend' => $trend,
            ];
        }

        // Sort by total_score descending, then votes_count, then contestant id
        // (contestant id keeps the order fully deterministic when everything ties,
        // e.g. when a round has no votes yet).
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            if ($b['votes_count'] !== $a['votes_count']) {
                return $b['votes_count'] <=> $a['votes_count'];
            }
            return $a['contestant_id'] <=> $b['contestant_id'];
        });

        // Assign ranks (with tie handling)
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if (
                $i > 0
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
            ->with(['contestable.media', 'submissions.round'])
            ->get();

        // Aggregate interaction counts across ALL rounds for the season view.
        $interactionCounts = $this->interactionCountsByBusiness(null);

        $leaderboard = [];

        foreach ($contestants as $contestant) {
            $voteData = Vote::where('votable_type', Contestant::class)
                ->where('votable_id', $contestant->id)
                ->selectRaw('COUNT(*) as votes_count')
                ->selectRaw('COALESCE(SUM(weight), 0) as total_score')
                ->first();

            $contestable = $contestant->contestable;
            $roundInteractions = $this->roundInteractionCounts($interactionCounts, $contestable);

            $leaderboard[] = [
                'contestant'      => $contestant,
                'contestant_id'   => $contestant->id,
                'display_name'    => $contestant->display_name,
                'avatar_url'      => $this->avatarFor($contestant),
                'contestable_name' => $contestable ? $contestable->getContestantName() : null,
                'total_score'     => (float) ($voteData->total_score ?? 0),
                'votes_count'     => (int) ($voteData->votes_count ?? 0),
                'claps'           => $roundInteractions['claps'],
                'shares'          => $roundInteractions['shares'],
                'saves'           => $roundInteractions['saves'],
                'total_points'    => $roundInteractions['total_points'],
                'trend'           => 'neutral', // Overall trend can be neutral or calculated later
            ];
        }

        // Sort by total_score descending, then votes_count, then contestant id
        // for deterministic ordering.
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            if ($b['votes_count'] !== $a['votes_count']) {
                return $b['votes_count'] <=> $a['votes_count'];
            }
            return $a['contestant_id'] <=> $b['contestant_id'];
        });

        // Assign ranks
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if (
                $i > 0
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

    private function getRoundOneLeaderboard(Round $round): array
    {
        $contestants = Contestant::where('season_id', $round->season_id)
            ->where('current_round_id', $round->id)
            ->where('status', 'active')
            ->with(['contestable.media', 'submissions.round'])
            ->get();

        // Round 1 counts come from interactions recorded for THIS round, so a
        // freshly registered business starts at 0 until users clap/save/share.
        $interactionCounts = $this->interactionCountsByBusiness($round->id);

        $leaderboard = [];
        foreach ($contestants as $contestant) {
            $business = $contestant->contestable; // Business model, could be null
            $roundInteractions = $this->roundInteractionCounts($interactionCounts, $business);

            $leaderboard[] = [
                'contestant'       => $contestant,
                'contestant_id'    => $contestant->id,
                'display_name'     => $contestant->display_name,
                'avatar_url'       => $this->avatarFor($contestant),
                'contestable_name' => $business ? $business->getContestantName() : null,
                // Round 1 scores come from THIS round's interaction points, so a
                // newly registered business starts at 0 and ranks dynamically.
                'total_score'      => (float) $roundInteractions['total_points'],
                'votes_count'      => 0,
                'avg_score'        => null,
                'claps'            => $roundInteractions['claps'],
                'shares'           => $roundInteractions['shares'],
                'saves'            => $roundInteractions['saves'],
                'total_points'     => $roundInteractions['total_points'],
                'trend'            => 'neutral',
            ];
        }

        // Sort by total_score descending, then contestant id for determinism.
        usort($leaderboard, function ($a, $b) {
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $a['contestant_id'] <=> $b['contestant_id'];
        });

        // Assign ranks (with tie handling) — same logic as round 2+
        $rank = 1;
        foreach ($leaderboard as $i => &$entry) {
            if ($i > 0 && $entry['total_score'] === $leaderboard[$i - 1]['total_score']) {
                $entry['rank'] = $leaderboard[$i - 1]['rank'];
            } else {
                $entry['rank'] = $rank;
            }
            $rank++;
        }
        unset($entry);

        return $leaderboard;
    }

    /*
    |--------------------------------------------------------------------------
    | Round-wise interaction counts (clap / save / share)
    |--------------------------------------------------------------------------
    */

    /**
     * Clap/save/share counts per business, scoped to a single round.
     *
     * When $roundId is null every round is aggregated (used by the overall
     * season leaderboard). Returns business_id → [action_type => count].
     */
    private function interactionCountsByBusiness(?int $roundId = null): Collection
    {
        $query = BusinessInteraction::whereIn('action_type', ['clap', 'save', 'share']);

        if ($roundId !== null) {
            $query->where('round_id', $roundId);
        }

        return $query
            ->selectRaw('business_id, action_type, COUNT(*) as total')
            ->groupBy('business_id', 'action_type')
            ->get()
            ->groupBy('business_id')
            ->mapWithKeys(fn ($rows, $businessId) => [$businessId => $rows->pluck('total', 'action_type')]);
    }

    /**
     * Resolve the avatar for a leaderboard entry.
     *
     * Uses the contestant's own avatar_url when set, otherwise falls back to
     * the contestable's avatar (e.g. the business's first media file) so the
     * leaderboard never shows an empty avatar for a business with media.
     */
    private function avatarFor(Contestant $contestant): ?string
    {
        if ($contestant->avatar_url) {
            return $contestant->avatar_url;
        }

        $contestable = $contestant->contestable;

        return $contestable ? $contestable->getContestantAvatar() : null;
    }

    /**
     * Extract clap/save/share counts for one contestable entry.
     *
     * Only Business contestables carry business interactions; every other
     * contestable type gets zero counts.
     */
    private function roundInteractionCounts(Collection $counts, $contestable): array
    {
        if (!$contestable instanceof Business) {
            return ['claps' => 0, 'saves' => 0, 'shares' => 0, 'total_points' => 0];
        }

        $row = $counts->get($contestable->id, collect());

        $claps  = (int) ($row['clap'] ?? 0);
        $saves  = (int) ($row['save'] ?? 0);
        $shares = (int) ($row['share'] ?? 0);

        return [
            'claps'  => $claps,
            'saves'  => $saves,
            'shares' => $shares,
            // Same point values the toggle methods use, so round-wise points
            // always match how the real counters accumulate.
            'total_points' => $claps * BusinessService::POINTS_CLAP
                + $saves * BusinessService::POINTS_SAVE
                + $shares * BusinessService::POINTS_SHARE,
        ];
    }
}
