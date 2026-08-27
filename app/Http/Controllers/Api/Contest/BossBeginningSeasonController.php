<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Contest\RoundResource;
use App\Http\Resources\Contest\SeasonResource;
use App\Models\Contest\Season;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BossBeginningSeasonController extends Controller
{
    use ApiResponse;

    /**
     * Get the active season and its rounds.
     *
     * GET /api/v1/contest/active-season-rounds
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

    /**
     * Get real-time countdown timer for all rounds in the active season.
     *
     * GET /api/v1/contest/active-round-countdown
     */
    public function activeRoundsCountdown(Request $request): JsonResponse
    {
        $seasonId = $request->query('season_id');

        if ($seasonId) {
            $season = Season::find($seasonId);
        } else {
            $season = Season::active();
        }

        if (!$season) {
            return $this->error(null, 'No active season found.', 404);
        }

        $season->load([
            'rounds' => function ($query) {
                $query->orderBy('round_number');
            },
        ]);

        $now = now();

        $rounds = $season->rounds->map(function ($round) use ($now) {
            $countdownData = $this->calculateRoundCountdown($round, $now);

            return [
                'round_id' => $round->id,
                'round_number' => $round->round_number,
                'title' => $round->title,
                'is_active' => (bool) $round->is_active,
                'status' => $round->is_active ? 'active' : $countdownData['status'],
                'starts_at' => $round->starts_at?->toIso8601String(),
                'ends_at' => $round->ends_at?->toIso8601String(),
                'target_date' => $countdownData['target_date'],
                'countdown' => $countdownData['countdown'],
            ];
        });

        return $this->success('Active season rounds countdown retrieved successfully.', [
            'season_id' => $season->id,
            'season_title' => $season->title,
            'rounds' => $rounds,
        ]);
    }

    /**
     * Helper to calculate countdown breakdown for a round based on starts_at and ends_at.
     */
    private function calculateRoundCountdown($round, Carbon $now): array
    {
        $startsAt = $round->starts_at;
        $endsAt = $round->voting_ends_at ?? $round->ends_at;

        if (!$startsAt && !$endsAt) {
            return [
                'status' => 'no_dates',
                'target_date' => null,
                'countdown' => $this->formatCountdown(0, 0, 0, 0, 0),
            ];
        }

        if ($startsAt && $startsAt > $now) {
            $status = 'upcoming';
            $targetDate = $startsAt;
        } elseif ($endsAt && $endsAt > $now) {
            $status = 'active';
            $targetDate = $endsAt;
        } else {
            $status = 'ended';
            $targetDate = $endsAt ?? $startsAt;
        }

        if ($status === 'ended' || !$targetDate || $targetDate <= $now) {
            return [
                'status' => 'ended',
                'target_date' => $targetDate?->toIso8601String(),
                'countdown' => $this->formatCountdown(0, 0, 0, 0, 0),
            ];
        }

        $diff = $now->diff($targetDate);
        $days = (int) $diff->days;
        $hours = (int) $diff->h;
        $minutes = (int) $diff->i;
        $seconds = (int) $diff->s;
        $totalSeconds = $targetDate->getTimestamp() - $now->getTimestamp();

        return [
            'status' => $status,
            'target_date' => $targetDate->toIso8601String(),
            'countdown' => $this->formatCountdown($days, $hours, $minutes, $seconds, $totalSeconds),
        ];
    }

    /**
     * Format countdown numbers into structured fields and labels.
     */
    private function formatCountdown(int $days, int $hours, int $minutes, int $seconds, int $totalSeconds): array
    {
        $paddedDays = sprintf('%02d', $days);
        $paddedHours = sprintf('%02d', $hours);
        $paddedMinutes = sprintf('%02d', $minutes);
        $paddedSeconds = sprintf('%02d', $seconds);

        return [
            'formatted' => "{$paddedDays} Days : {$paddedHours} Hours : {$paddedMinutes} Minutes : {$paddedSeconds} Seconds",
            'formatted_short' => "{$days}d {$hours}h {$minutes}m {$seconds}s",
        ];
    }
}
