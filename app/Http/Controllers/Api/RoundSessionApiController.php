<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest\Season;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundSessionApiController extends Controller
{
    use ApiResponse;

    /**
     * Get the countdown timer for the nearest upcoming season's starts_at date.
     *
     * GET /api/v1/round-countdown
     */
    public function countdown(Request $request): JsonResponse
    {
        // 1. Retrieve the season (by optional ID parameter, or default to the nearest upcoming season)
        $seasonId = $request->query('season_id');

        $now = now();

        if ($seasonId) {
            $season = Season::find($seasonId);
        } else {
            // Find the nearest season that hasn't started yet
            $season = Season::whereNotNull('starts_at')
                ->where('starts_at', '>', $now)
                ->orderBy('starts_at', 'asc')
                ->first();

            if (!$season) {
                // fallback to the active season
                $season = Season::active();
            }
        }

        if (!$season) {
            return $this->error(null, 'No upcoming season found.', 404);
        }

        $startsAt = $season->starts_at;

        // 2. Calculate time difference
        if ($startsAt && $startsAt > $now) {
            $diff = $now->diff($startsAt);

            $days = $diff->days;
            $hours = $diff->h;
            $minutes = $diff->i;
            $seconds = $diff->s;

            // Zero-padded format with spaces around colons: 12 : 04 : 33 : 14
            $formatted = sprintf('%02d : %02d : %02d : %02d', $days, $hours, $minutes, $seconds);
            $shortFormatted = sprintf('%dd %dh %dm %ds', $days, $hours, $minutes, $seconds);
            $totalSeconds = $startsAt->getTimestamp() - $now->getTimestamp();
        } else {
            // If already started or no date, set to 0
            $days = 0;
            $hours = 0;
            $minutes = 0;
            $seconds = 0;

            $formatted = '00 : 00 : 00 : 00';
            $shortFormatted = '0d 0h 0m 0s';
            $totalSeconds = 0;
        }

        return $this->success('Season countdown retrieved successfully.', [
            'season' => [
                'id' => $season->id,
                'title' => $season->title,
                'starts_at' => $startsAt ? $startsAt->toIso8601String() : null,
                'current_time' => $now->toIso8601String(),
            ],
            'countdown' => [
                'days' => sprintf('%02d', $days),
                'hours' => sprintf('%02d', $hours),
                'minutes' => sprintf('%02d', $minutes),
                'seconds' => sprintf('%02d', $seconds),
                'formatted' => $formatted,
                'short_formatted' => $shortFormatted,
                'total_seconds' => $totalSeconds,
            ],
        ]);
    }
}
