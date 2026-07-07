<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Round;
use App\Services\Contest\VoteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VoteService $voteService
    ) {}

    /**
     * POST /api/v1/contest/rounds/{round}/votes
     *
     * Cast or toggle a vote on a contestant/entity for the given round.
     *
     * @bodyParam votable_type string required The type of entity being voted for (e.g., "contestant")
     * @bodyParam votable_id int required The ID of the entity being voted for
     * @bodyParam vote_type string Vote type: upvote, downvote (default: upvote)
     */
    public function store(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'votable_type' => 'required|string|max:100',
            'votable_id'   => 'required|integer',
            'vote_type'    => 'sometimes|string|in:upvote,downvote,score_1_5,score_1_10',
        ]);

        $user = auth('api')->user();

        $result = $this->voteService->castVote(
            user: $user,
            round: $round,
            votableType: $validated['votable_type'],
            votableId: $validated['votable_id'],
            voteType: $validated['vote_type'] ?? 'upvote',
        );

        if (!$result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'action' => $result['action'],
            'vote'   => $result['vote']?->load('votable'),
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/votes/my
     *
     * Get the authenticated user's votes for a given round.
     */
    public function myVotes(Round $round): JsonResponse
    {
        $user  = auth('api')->user();
        $votes = $this->voteService->userVotesInRound($user, $round);

        return $this->success('My votes retrieved successfully.', [
            'votes' => $votes,
            'count' => $votes->count(),
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/votes/counts
     *
     * Get aggregated vote counts for all contestants in a round.
     */
    public function counts(Round $round): JsonResponse
    {
        $contestants = Contestant::where('current_round_id', $round->id)
            ->active()
            ->get();

        $results = [];
        foreach ($contestants as $contestant) {
            $results[] = [
                'contestant_id' => $contestant->id,
                'display_name'  => $contestant->display_name,
                'votes'         => $this->voteService->countVotes(
                    Contestant::class,
                    $contestant->id,
                    $round->id
                ),
            ];
        }

        // Sort by net_score descending
        usort($results, fn($a, $b) => $b['votes']['net_score'] <=> $a['votes']['net_score']);

        return $this->success('Vote counts retrieved successfully.', [
            'round_id' => $round->id,
            'results'  => $results,
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/votes/check/{contestant}
     *
     * Check if the authenticated user has voted for a specific contestant.
     */
    public function check(Round $round, Contestant $contestant): JsonResponse
    {
        $user     = auth('api')->user();
        $hasVoted = $this->voteService->hasVoted($user, $round, Contestant::class, $contestant->id);

        return $this->success('Vote status retrieved.', [
            'has_voted' => $hasVoted,
        ]);
    }
}
