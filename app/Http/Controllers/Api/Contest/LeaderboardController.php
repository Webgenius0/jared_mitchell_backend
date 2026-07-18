<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Services\Contest\LeaderboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Contest\LeaderboardEntryResource;

class LeaderboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaderboardService $leaderboardService
    ) {}

    /**
     * GET /api/v1/contest/leaderboard/overall
     *
     * Get the overall leaderboard for the currently active season.
     */
    public function activeOverall(): JsonResponse
    {
        $season = Season::active();

        if (!$season) {
            return $this->notFound('No active season found.');
        }

        return $this->overallResponse($season);
    }

    /**
     * GET /api/v1/contest/seasons/{season}/leaderboard
     *
     * Get the overall season leaderboard (aggregated across all rounds).
     */
    public function overall(Season $season): JsonResponse
    {
        return $this->overallResponse($season);
    }

    private function overallResponse(Season $season): JsonResponse
    {
        $entries = $this->leaderboardService->getOverallLeaderboard($season->id);

        return $this->success('Overall leaderboard retrieved successfully.', [
            'season_id' => $season->id,
            'season' => $season->only(['id', 'title', 'contest_type', 'status']),
            'entries' => LeaderboardEntryResource::collection($entries),
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/leaderboard
     *
     * Get the leaderboard for a specific round.
     */
    public function forRound(Round $round): JsonResponse
    {
        $entries = $this->leaderboardService->getLeaderboard($round);

        return $this->success('Round leaderboard retrieved successfully.', [
            'round_id' => $round->id,
            'round' => $round->only(['id', 'round_number', 'title']),
            'entries' => LeaderboardEntryResource::collection($entries),
        ]);
    }

    /**
     * GET /api/v1/contest/seasons/{season}/leaderboard/calculate
     *
     * Fetch the leaderboard (on-demand calculation, no caching needed).
     */
    public function recalculate(Season $season): JsonResponse
    {
        $latestRound = Round::where('season_id', $season->id)
            ->orderByDesc('round_number')
            ->first();

        if (!$latestRound) {
            return $this->error(null, 'No rounds found for this season.', 404);
        }

        $entries = $this->leaderboardService->getLeaderboard($latestRound);

        return $this->success('Leaderboard retrieved successfully.', [
            'round_id' => $latestRound->id,
            'entries'  => LeaderboardEntryResource::collection($entries),
        ]);
    }
}
