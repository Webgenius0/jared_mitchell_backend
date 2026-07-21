<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BossBeginningWinnerController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/contest/winners/current
     *
     * Get the winner of the most recently completed Boss Beginnings season.
     * Returns the contestant with status 'winner' from the latest completed season,
     * along with their business details, season info, and vote/score summary.
     */
    public function currentWinner(): JsonResponse
    {
        // Find the most recently completed season
        $season = Season::where('status', 'completed')
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();

        if (!$season) {
            return $this->error(null, 'No completed season found.', 404);
        }

        // Find the winner (rank #1) contestant for this season
        $winner = Contestant::where('season_id', $season->id)
            ->where('status', 'winner')
            ->with([
                'contestable',
                'contestable.media',
                'season',
                'currentRound',
            ])
            ->first();

        if (!$winner) {
            return $this->error(null, 'No winner found for the current season.', 404);
        }

        return $this->success('Current Boss Beginnings winner retrieved successfully.', [
            'winner' => $this->formatWinnerData($winner, $season),
        ]);
    }

    /**
     * GET /api/v1/contest/winners/past-six-months
     *
     * Get all Boss Beginnings winners from seasons completed in the last 6 months.
     * Returns a list of winners with their business details, season info, and ranking.
     */
    public function pastWinners(): JsonResponse
    {
        $sixMonthsAgo = now()->subMonths(6);

        // Find all winners (contestants with status 'winner') whose season was completed in the last 6 months
        $winners = Contestant::where('status', 'winner')
            ->whereHas('season', function ($query) use ($sixMonthsAgo) {
                $query->where('status', 'completed')
                    ->where('ends_at', '>=', $sixMonthsAgo);
            })
            ->with([
                'contestable',
                'contestable.media',
                'season',
                'currentRound',
            ])
            ->orderByDesc(
                Contestant::select('ends_at')
                    ->from('seasons')
                    ->whereColumn('seasons.id', 'contestants.season_id')
                    ->limit(1)
            )
            ->get();

        if ($winners->isEmpty()) {
            return $this->error(null, 'No winners found in the last 6 months.', 404);
        }

        return $this->success('Past 6 months Boss Beginnings winners retrieved successfully.', [
            'winners' => $winners->map(fn($winner) => $this->formatWinnerData(
                $winner,
                $winner->season
            ))->values(),
        ]);
    }

    /**
     * Format a winner contestant into a consistent response structure.
     */
    private function formatWinnerData(Contestant $winner, Season $season): array
    {
        $contestable = $winner->contestable;

        return [
            'id' => $winner->id,
            'display_name' => $winner->display_name,
            'slug' => $winner->slug,
            'avatar_url' => $winner->avatar_url
                ? asset('storage/' . $winner->avatar_url)
                : asset('admin/default/user.jpg'),
            'status' => $winner->status,
            'total_score' => (float) $winner->total_score,
            'entered_at' => $winner->entered_at?->toIso8601String(),
            'created_at' => $winner->created_at?->toIso8601String(),

            // Business / contestable entity details
            'contestable' => $contestable ? [
                'id' => $contestable->id,
                'type' => get_class($contestable),
                'business_name' => $contestable->business_name ?? null,
                'owner_founder_name' => $contestable->owner_founder_name ?? null,
                'slug' => $contestable->slug ?? null,
                'story' => $contestable->story ?? null,
                'mission' => $contestable->mission ?? null,
                'website_social_media' => $contestable->website_social_media ?? null,
                'community_impact_statement' => $contestable->community_impact_statement ?? null,
                'revenue_stage' => $contestable->revenue_stage ?? null,
                'why_they_deserve_to_compete' => $contestable->why_they_deserve_to_compete ?? null,
                'status' => $contestable->status ?? null,
                'total_claps' => (int) ($contestable->total_claps ?? 0),
                'total_saves' => (int) ($contestable->total_saves ?? 0),
                'total_shares' => (int) ($contestable->total_shares ?? 0),
                'total_points' => (int) ($contestable->total_points ?? 0),
                'media' => $contestable->media && $contestable->media->count() > 0
                    ? $contestable->media->map(fn($m) => [
                        'id' => $m->id,
                        'file_path' => asset('storage/' . $m->file_path),
                        'file_name' => $m->file_name,
                        'mime_type' => $m->mime_type,
                    ])
                    : [],
            ] : null,

            // Season information
            'season' => [
                'id' => $season->id,
                'title' => $season->title,
                'slug' => $season->slug,
                'contest_type' => $season->contest_type,
                'status' => $season->status,
                'starts_at' => $season->starts_at?->toIso8601String(),
                'ends_at' => $season->ends_at?->toIso8601String(),
                'is_active' => $season->is_active,
            ],
        ];
    }
}
