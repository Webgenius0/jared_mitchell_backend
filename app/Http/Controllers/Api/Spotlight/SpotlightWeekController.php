<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Services\Spotlight\SpotlightVoteService;
use App\Services\Spotlight\SpotlightWeekService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotlightWeekController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SpotlightWeekService $weekService,
        protected SpotlightVoteService $voteService,
    ) {}

    /**
     * GET /api/v1/spotlight/weeks/current
     *
     * Returns the current active voting week with Top 12 leaderboard.
     * Public — no auth required.
     *
     * @queryParam type string Optional. Filter by 'artist', 'business', or 'all' (default).
     */
    public function current(Request $request): JsonResponse
    {
        $type = $request->input('type', 'all');

        if (! in_array($type, ['all', 'artist', 'business'])) {
            $type = 'all';
        }

        $week = $this->weekService->getCurrentVotingWeek();

        if (! $week) {
            // Also try to find the most recent nominating week
            $week = SpotlightWeek::whereIn('status', ['nominating'])
                ->latest('voting_starts_at')
                ->first();
        }

        if (! $week) {
            return $this->success('No active spotlight week found.', ['week' => null]);
        }

        $filterType = $type === 'all' ? null : $type;
        $leaderboard = $this->voteService->getLeaderboard($week->id, $filterType);

        return $this->success('Current spotlight week retrieved.', [
            'week' => [
                'id'               => $week->id,
                'week_number'      => $week->week_number,
                'year'             => $week->year,
                'status'           => $week->status,
                'is_voting_open'   => $week->isVotingOpen(),
                'voting_starts_at' => $week->voting_starts_at,
                'voting_ends_at'   => $week->voting_ends_at,
            ],
            'nominees_count' => $leaderboard->count(),
            'leaderboard' => $leaderboard,
            'pricing' => \App\Models\Spotlight\SpotlightVotePackage::active()->ordered()->get()->map(function ($pkg) {
                return [
                    'id'          => $pkg->id,
                    'name'        => $pkg->name,
                    'slug'        => $pkg->slug,
                    'votes'       => $pkg->votes_count,
                    'price'       => (float) $pkg->price,
                    'label'       => $pkg->label,
                ];
            }),
            'max_paid_votes' => SpotlightWeek::maxPurchasedVotes(),
        ]);
    }

    /**
     * GET /api/v1/spotlight/weeks
     *
     * List all spotlight weeks with filters.
     * Public — no auth required.
     *
     * @queryParam status string Filter by status
     */
    public function index(Request $request): JsonResponse
    {
        $query = SpotlightWeek::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $weeks = $query->latest('voting_starts_at')->paginate(15);

        $data = collect($weeks->items())->map(function ($week) {
            return [
                'id'               => $week->id,
                'week_number'      => $week->week_number,
                'year'             => $week->year,
                'status'           => $week->status,
                'is_voting_open'   => $week->isVotingOpen(),
                'is_accepting_applications' => $week->isAcceptingApplications(),
                'voting_starts_at' => $week->voting_starts_at,
                'voting_ends_at'   => $week->voting_ends_at,
            ];
        });

        return $this->success('Spotlight weeks retrieved.', [
            'weeks'      => $data,
            'pagination' => [
                'total'        => $weeks->total(),
                'per_page'     => $weeks->perPage(),
                'current_page' => $weeks->currentPage(),
                'last_page'    => $weeks->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/spotlight/weeks/{week}/leaderboard
     *
     * Real-time leaderboard for a specific week.
     * Public — no auth required.
     *
     * @queryParam type string Optional. Filter by 'artist', 'business', or 'all' (default).
     */
    public function leaderboard(Request $request, SpotlightWeek $week): JsonResponse
    {
        $type = $request->input('type', 'all');

        if (! in_array($type, ['all', 'artist', 'business'])) {
            $type = 'all';
        }

        $filterType = $type === 'all' ? null : $type;
        $leaderboard = $this->voteService->getLeaderboard($week->id, $filterType);

        return $this->success('Leaderboard retrieved.', [
            'week' => [
                'id'             => $week->id,
                'status'         => $week->status,
                'is_voting_open' => $week->isVotingOpen(),
                'voting_ends_at' => $week->voting_ends_at,
            ],
            'type'        => $type,
            'nominees_count' => $leaderboard->count(),
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * GET /api/v1/spotlight/weeks/winners
     *
     * Get the most recent spotlight winner. Includes archive list.
     * Public — no auth required.
     */
    public function winners(): JsonResponse
    {
        $latestWinner = $this->weekService->getLastWinner();

        $archive = SpotlightWeekNominee::whereHas('week', function ($q) {
                $q->where('status', 'completed')
                  ->whereNotNull('announced_at');
            })
            ->where('is_winner', true)
            ->with('spotlightable', 'week', 'user.profile')
            ->latest()
            ->paginate(10);

        $archiveData = collect($archive->items())->map(function ($nominee) {
            $isArtist = $nominee->spotlightable_type === \App\Models\ArtistSpotlight::class;
            $spotlight = $nominee->spotlightable;

            return [
                'id'              => $nominee->id,
                'week_number'     => $nominee->week?->week_number,
                'year'            => $nominee->week?->year,
                'week_status'     => $nominee->week?->status,
                'spotlight'       => $spotlight ? [
                    'id'   => $spotlight->id,
                    'type' => $isArtist ? 'artist' : 'business',
                    'name' => $isArtist
                        ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                        : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                    'city'  => $spotlight->city ?? null,
                    'state' => $spotlight->state ?? null,
                ] : null,
                'total_votes'     => $nominee->total_vote_count,
                'announced_at'    => $nominee->week?->announced_at,
            ];
        });

        return $this->success('Spotlight winners retrieved.', [
            'current_winner' => $latestWinner ? $this->formatWinner($latestWinner) : null,
            'archive'        => $archiveData,
            'pagination'     => [
                'total'        => $archive->total(),
                'per_page'     => $archive->perPage(),
                'current_page' => $archive->currentPage(),
                'last_page'    => $archive->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/spotlight/historical-winners
     *
     * Get past 6 months of announced winners, filtered by spotlight type.
     * Public — no auth required.
     *
     * @queryParam type string Required. Either 'artist' or 'business'. Filter winners by spotlight type.
     */
    public function historicalWinners(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:artist,business'],
        ]);

        $type = $validated['type'];
        $spotlightableType = $type === 'artist'
            ? ArtistSpotlight::class
            : BusinessSpotlight::class;

        $sixMonthsAgo = now()->subMonths(6);

        $winners = SpotlightWeekNominee::where('is_winner', true)
            ->where('spotlightable_type', $spotlightableType)
            ->whereHas('week', function ($q) use ($sixMonthsAgo) {
                $q->where('status', 'completed')
                  ->whereNotNull('announced_at')
                  ->where('voting_ends_at', '>=', $sixMonthsAgo);
            })
            ->with(['spotlightable', 'week', 'user.profile'])
            ->orderByRaw('(SELECT voting_ends_at FROM spotlight_weeks WHERE id = spotlight_week_nominees.spotlight_week_id LIMIT 1) DESC')
            ->get();

        $data = $winners->map(function ($nominee) {
            return $this->formatWinner($nominee);
        });

        return $this->success("Past 6 months {$type} spotlight winners retrieved.", [
            'type'    => $type,
            'total'   => $data->count(),
            'winners' => $data,
        ]);
    }

    /**
     * Format a spotlight week nominee winner into a clean response array.
     */
    private function formatWinner($nominee): array
    {
        $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
        $spotlight = $nominee->spotlightable;

        return [
            'id'          => $nominee->id,
            'week_number' => $nominee->week?->week_number,
            'year'        => $nominee->week?->year,
            'spotlight'   => $spotlight ? [
                'id'   => $spotlight->id,
                'type' => $isArtist ? 'artist' : 'business',
                'name' => $isArtist
                    ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                    : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                'city'  => $spotlight->city ?? null,
                'state' => $spotlight->state ?? null,
            ] : null,
            'owner'       => [
                'id'   => $nominee->user->id,
                'name' => $nominee->user->profile?->name ?? $nominee->user->email ?? '—',
            ],
            'total_votes'  => $nominee->total_vote_count,
            'free_votes'   => $nominee->free_vote_count,
            'paid_votes'   => $nominee->paid_vote_count,
            'announced_at' => $nominee->week?->announced_at,
        ];
    }
}
