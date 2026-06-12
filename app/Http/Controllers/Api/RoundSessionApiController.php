<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundSessionApiController extends Controller
{
    use ApiResponse;

    /**
     * Get the countdown timer for the last updated/created round's starts_at date.
     *
     * GET /api/v1/round-countdown
     */
    public function countdown(Request $request): JsonResponse
    {
        // 1. Retrieve the round (by optional ID parameter, or default to the latest stored round with a starts_at date)
        $roundId = $request->query('round_id');

        if ($roundId) {
            $round = Round::find($roundId);
        } else {
            // Find the last stored/updated round in the database
            $round = Round::whereNotNull('starts_at')->latest('id')->first();
        }

        if (!$round) {
            return $this->error(null, 'No round with a start date found.', 404);
        }

        $now = now();
        $startsAt = $round->starts_at;

        // 2. Calculate time difference
        if ($startsAt && $startsAt > $now) {
            $diff = $now->diff($startsAt);

            $days = $diff->days;
            $hours = $diff->h;
            $minutes = $diff->i;
            $seconds = $diff->s;

            $formatted = sprintf('%d days, %d hours, %d minutes, %d seconds', $days, $hours, $minutes, $seconds);
            $shortFormatted = sprintf('%dd %dh %dm %ds', $days, $hours, $minutes, $seconds);
            $totalSeconds = $startsAt->getTimestamp() - $now->getTimestamp();
        } else {
            // If already started or no date, set to 0
            $days = 0;
            $hours = 0;
            $minutes = 0;
            $seconds = 0;

            $formatted = '0 days, 0 hours, 0 minutes, 0 seconds';
            $shortFormatted = '0d 0h 0m 0s';
            $totalSeconds = 0;
        }

        return $this->success('Round countdown retrieved successfully.', [
            'round' => [
                'id' => $round->id,
                'round_number' => $round->round_number,
                'title' => $round->title,
                'starts_at' => $startsAt ? $startsAt->toIso8601String() : null,
                'current_time' => $now->toIso8601String(),
            ],
            'countdown' => [
                'days' => $days,
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds,
                'formatted' => $formatted,
                'short_formatted' => $shortFormatted,
                'total_seconds' => $totalSeconds,
            ],
        ]);
    }
}
