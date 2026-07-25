<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Contest\RoundResource;
use App\Http\Resources\Contest\SeasonResource;
use App\Models\Contest\Season;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BossBeginningSeasonController extends Controller
{
    use ApiResponse;

    /**
     * Get the active season and its rounds.
     *
     * GET /api/v1/active-season-rounds
     */
    public function activeRounds(): JsonResponse
    {
        $season = Season::active();

        if (!$season) {
            return $this->error(null, 'No active season found.', 404);
        }

        $season->load([
            'sponsor',
            'rounds' => function ($query) {
                $query->orderBy('round_number');
            },
        ]);

        return $this->success('Active season rounds retrieved successfully.', [
            'season' => new SeasonResource($season),
            'rounds' => RoundResource::collection($season->rounds),
        ]);
    }
}
