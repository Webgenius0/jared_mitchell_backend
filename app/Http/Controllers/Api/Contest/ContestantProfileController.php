<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Contest\ContestantProfileResource;
use App\Models\Contest\Contestant;
use App\Models\Contest\Vote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContestantProfileController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/contest/contestants/{contestant}
     *
     * Get the full profile of a contestant including their business info,
     * current round details, voting statistics, and current submission.
     * Public endpoint — no authentication required.
     */
    public function show(Contestant $contestant): JsonResponse
    {
        // Load relationships eagerly
        $contestant->load([
            'contestable',
            'contestable.media',
            'currentRound',
            'submissions' => function ($query) {
                $query->whereIn('status', ['submitted', 'approved'])
                    ->latest('submitted_at');
            },
        ]);

        // Compute vote aggregates for this contestant in current round
        $currentRoundId = $contestant->current_round_id;

        $voteAggregates = Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->selectRaw('COUNT(*) as total_votes')
            ->selectRaw('COALESCE(SUM(weight), 0) as total_weighted_score')
            ->first();

        // Today vs yesterday votes for trend
        $todayVotes = Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $yesterdayVotes = Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()])
            ->count();

        // Attach computed data to the model so the resource can access it
        $contestant->voteStats = [
            'total_votes' => (int) ($voteAggregates->total_votes ?? 0),
            'total_weighted_score' => (float) ($voteAggregates->total_weighted_score ?? 0),
            'today_votes' => $todayVotes,
            'yesterday_votes' => $yesterdayVotes,
        ];

        return $this->success('Contestant profile retrieved successfully.', [
            'contestant' => new ContestantProfileResource($contestant),
        ]);
    }
}
