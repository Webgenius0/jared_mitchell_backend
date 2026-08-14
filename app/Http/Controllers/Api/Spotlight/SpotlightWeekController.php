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
use Illuminate\Support\Facades\Storage;

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
            // 'pricing' => \App\Models\Spotlight\SpotlightVotePackage::active()->ordered()->get()->map(function ($pkg) {
            //     return [
            //         'id'          => $pkg->id,
            //         'name'        => $pkg->name,
            //         'slug'        => $pkg->slug,
            //         'votes'       => $pkg->votes_count,
            //         'price'       => (float) $pkg->price,
            //         'label'       => $pkg->label,
            //     ];
            // }),
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
     * GET /api/v1/spotlight/weeks/spotlight-of-the-week
     *
     * Get the most recent announced spotlight winner.
     * Public — no auth required.
     *
     * @queryParam type string Optional. Filter by 'artist', 'business', or 'all' (default).
     */
    public function spotlightOfTheWeek(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:all,artist,business'],
        ]);

        $type = $validated['type'] ?? 'all';
        $latestWinner = $this->weekService->getLastWinner($type === 'all' ? null : $type);

        // $archive = SpotlightWeekNominee::whereHas('week', function ($q) {
        //         $q->where('status', 'completed')
        //           ->whereNotNull('announced_at');
        //     })
        //     ->where('is_winner', true)
        //     ->with('spotlightable', 'week', 'user.profile')
        //     ->latest()
        //     ->paginate($perPage);

        // $archiveData = collect($archive->items())->map(function ($nominee) {
        //     $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
        //     $spotlight = $nominee->spotlightable;

        //     return [
        //         'id'              => $nominee->id,
        //         'week_number'     => $nominee->week?->week_number,
        //         'year'            => $nominee->week?->year,
        //         'week_status'     => $nominee->week?->status,
        //         'spotlight'       => $spotlight ? [
        //             'id'   => $spotlight->id,
        //             'type' => $isArtist ? 'artist' : 'business',
        //             'name' => $isArtist
        //                 ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
        //                 : ($spotlight->business_name ?? $spotlight->owner_founder_name),
        //             'city'  => $spotlight->city ?? null,
        //             'state' => $spotlight->state ?? null,
        //         ] : null,
        //         'total_votes'     => $nominee->total_vote_count,
        //         'announced_at'    => $nominee->week?->announced_at,
        //     ];
        // });

        return $this->success('Spotlight winners retrieved.', [
            'type'           => $type,
            'current_winner' => $latestWinner ? $this->formatWinner($latestWinner) : null,
            // 'archive'        => $archiveData,
            // 'pagination'     => [
            //     'total'        => $archive->total(),
            //     'per_page'     => (int) $archive->perPage(),
            //     'current_page' => $archive->currentPage(),
            //     'last_page'    => $archive->lastPage(),
            //     'has_more'     => $archive->hasMorePages(),
            // ],
        ]);
    }

    /**
     * GET /api/v1/spotlight/historical-winners
     *
     * Get past 6 months of announced winners, filtered by spotlight type.
     * Public — no auth required.
     *
     * @queryParam type string Optional. 'artist', 'business', or 'all' (default). Filter winners by spotlight type.
     * @queryParam per_page int Optional. Items per page (default 10).
     */
    public function historicalWinners(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'     => ['sometimes', 'string', 'in:artist,business,all'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $type = $validated['type'] ?? 'all';
        $perPage = $validated['per_page'] ?? 10;
        $sixMonthsAgo = now()->subMonths(6);

        $query = SpotlightWeekNominee::where('is_winner', true)
            ->whereHas('week', function ($q) use ($sixMonthsAgo) {
                $q->where('status', 'completed')
                    ->where(function ($w) use ($sixMonthsAgo) {
                        $w->where('voting_ends_at', '>=', $sixMonthsAgo)
                            ->orWhere('updated_at', '>=', $sixMonthsAgo)
                            ->orWhereNull('voting_ends_at');
                    });
            });

        if ($type === 'artist') {
            $query->where('spotlightable_type', ArtistSpotlight::class);
        } elseif ($type === 'business') {
            $query->where('spotlightable_type', BusinessSpotlight::class);
        }

        $winners = $query->with(['spotlightable', 'week', 'user.profile'])
            ->orderByRaw('(SELECT created_at FROM spotlight_weeks WHERE id = spotlight_week_nominees.spotlight_week_id LIMIT 1) DESC')
            ->orderByDesc('id')
            ->paginate($perPage);

        $data = collect($winners->items())->map(function ($nominee) {
            return $this->formatWinner($nominee);
        });

        return $this->success("Past 6 months {$type} spotlight winners retrieved.", [
            'type'       => $type,
            'total'      => $winners->total(),
            'winners'    => $data,
            'pagination' => [
                'current_page' => $winners->currentPage(),
                'per_page'     => (int) $winners->perPage(),
                'last_page'    => $winners->lastPage(),
                'total'        => $winners->total(),
                'has_more'     => $winners->hasMorePages(),
            ],
        ]);
    }

    /**
     * GET /api/v1/spotlight/nominated
     *
     * Get the nominated spotlights for a given week — the spotlights selected for voting.
     * Defaults to the current voting week. Includes the spotlight and owner details.
     * Public — no auth required.
     *
     * @queryParam week_id int Optional. Week ID (defaults to current voting week).
     * @queryParam type string Optional. Filter by 'artist', 'business', or 'all' (default).
     * @queryParam per_page int Optional. Items per page (default 12).
     * @queryParam grouped bool Optional. If true, returns artists and businesses as separate groups (default false).
     */
    public function nominated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'week_id' => ['sometimes', 'integer', 'exists:spotlight_weeks,id'],
            'type' => ['sometimes', 'string', 'in:all,artist,business'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'grouped' => ['sometimes', 'boolean'],
        ]);

        $perPage = $validated['per_page'] ?? 12;
        $type = $validated['type'] ?? 'all';
        $grouped = $validated['grouped'] ?? false;

        // Resolve the week: given week_id, or current voting week
        if (! empty($validated['week_id'])) {
            $week = SpotlightWeek::findOrFail($validated['week_id']);
        } else {
            $week = $this->weekService->getCurrentVotingWeek();

            if (! $week) {
                $week = SpotlightWeek::whereIn('status', ['nominating', 'voting', 'completed'])
                    ->latest('voting_starts_at')
                    ->first();
            }

            if (! $week) {
                return $this->success('No active week with nominees found.', [
                    'week'     => null,
                    'nominees' => [],
                ]);
            }
        }

        // ── Week response data ────────────────────────────────────────
        $weekData = [
            'id'               => $week->id,
            'week_number'      => $week->week_number,
            'year'             => $week->year,
            'status'           => $week->status,
            'is_voting_open'   => $week->isVotingOpen(),
            'voting_starts_at' => $week->voting_starts_at,
            'voting_ends_at'   => $week->voting_ends_at,
        ];

        // ── GROUPED: return artists + businesses as separate groups ───
        if ($grouped) {
            $allNominees = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
                ->with(['spotlightable', 'user.profile', 'week'])
                ->orderByDesc('total_vote_count')
                ->orderByDesc('free_vote_count')
                ->get();

            $formatItem = function ($nominee): array {
                $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
                $spotlight = $nominee->spotlightable;

                $image = null;
                if ($isArtist) {
                    $image = $spotlight?->headshot_path
                        ? $this->formatImageUrl($spotlight->headshot_path)
                        : 'https://placehold.co/400x600?text=Artist';
                } else {
                    $image = $spotlight?->portrait_photo_path
                        ? $this->formatImageUrl($spotlight->portrait_photo_path)
                        : 'https://placehold.co/400x600?text=Business';
                }

                return [
                    'id'        => $nominee->id,
                    'rank'      => $nominee->rank,
                    'is_winner' => $nominee->is_winner,
                    'spotlight' => $spotlight ? [
                        'id'    => $spotlight->id,
                        'type'  => $isArtist ? 'artist' : 'business',
                        'name'  => $isArtist
                            ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                            : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                        'city'  => $spotlight->city ?? null,
                        'state' => $spotlight->state ?? null,
                        'image' => $image,
                    ] : null,
                    'owner'     => [
                        'id'   => $nominee->user->id,
                        'name' => $nominee->user->profile?->name ?? $nominee->user->email ?? '—',
                    ],
                    'votes'     => [
                        'free'            => $nominee->free_vote_count,
                        'paid'            => $nominee->paid_vote_count,
                        'total'           => $nominee->total_vote_count,
                        'paid_cap'        => SpotlightWeek::maxPurchasedVotes(),
                        'cap_reached'     => $nominee->hasReachedPaidVoteCap(),
                        'remaining_slots' => $nominee->remainingPaidVoteSlots(),
                    ],
                ];
            };

            $artistNominees   = $allNominees->where('spotlightable_type', ArtistSpotlight::class)->take($perPage)->values()->map($formatItem);
            $businessNominees = $allNominees->where('spotlightable_type', BusinessSpotlight::class)->take($perPage)->values()->map($formatItem);

            return $this->success('Nominated spotlights retrieved.', [
                'week'       => $weekData,
                'artists'    => [
                    'nominees_count' => $artistNominees->count(),
                    'nominees'       => $artistNominees,
                ],
                'businesses' => [
                    'nominees_count' => $businessNominees->count(),
                    'nominees'       => $businessNominees,
                ],
            ]);
        }

        // ── DEFAULT (backward compatible): flat list with pagination ──
        $query = SpotlightWeekNominee::where('spotlight_week_id', $week->id);

        if ($type === 'artist') {
            $query->where('spotlightable_type', ArtistSpotlight::class);
        } elseif ($type === 'business') {
            $query->where('spotlightable_type', BusinessSpotlight::class);
        }
        // 'all' → no filter

        $nominees = $query
            ->with(['spotlightable', 'user.profile', 'week'])
            ->orderByDesc('total_vote_count')
            ->orderByDesc('free_vote_count')
            ->paginate($perPage);

        $data = collect($nominees->items())->map(function ($nominee) {
            $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
            $spotlight = $nominee->spotlightable;

            return [
                'id'        => $nominee->id,
                'rank'      => $nominee->rank,
                'is_winner' => $nominee->is_winner,
                'spotlight' => $spotlight ? [
                    'id'       => $spotlight->id,
                    'type'     => $isArtist ? 'artist' : 'business',
                    'name'     => $isArtist
                        ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                        : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                    'city'     => $spotlight->city ?? null,
                    'state'    => $spotlight->state ?? null,
                    'headshot' => $isArtist
                        ? $this->formatImageUrl($spotlight->headshot_path)
                        : $this->formatImageUrl($spotlight->portrait_photo_path),
                ] : null,
                'owner'     => [
                    'id'   => $nominee->user->id,
                    'name' => $nominee->user->profile?->name ?? $nominee->user->email ?? '—',
                ],
                'votes'     => [
                    'free'            => $nominee->free_vote_count,
                    'paid'            => $nominee->paid_vote_count,
                    'total'           => $nominee->total_vote_count,
                    'paid_cap'        => SpotlightWeek::maxPurchasedVotes(),
                    'cap_reached'     => $nominee->hasReachedPaidVoteCap(),
                    'remaining_slots' => $nominee->remainingPaidVoteSlots(),
                ],
            ];
        });

        return $this->success('Nominated spotlights retrieved.', [
            'week'            => $weekData,
            'type'            => $type,
            'nominees_count'  => $nominees->total(),
            'nominees'        => $data,
            'pagination'      => [
                'current_page' => $nominees->currentPage(),
                'per_page'     => (int) $nominees->perPage(),
                'last_page'    => $nominees->lastPage(),
                'total'        => $nominees->total(),
            ],
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
                'media' => $this->formatSpotlightMedia($spotlight, $isArtist),
            ] : null,
            'owner'       => [
                'id'   => $nominee->user?->id,
                'name' => $nominee->user?->profile?->name ?? $nominee->user?->email ?? '—',
            ],
            'total_votes'  => $nominee->total_vote_count,
            'free_votes'   => $nominee->free_vote_count,
            'paid_votes'   => $nominee->paid_vote_count,
            'announced_at' => $nominee->week?->announced_at ?? $nominee->week?->updated_at,
        ];
    }

    /**
     * Format spotlight media into full URLs, matching the pattern in SpotlightDetailsController.
     */
    private function formatSpotlightMedia($spotlight, bool $isArtist): array
    {
        if ($isArtist) {
            $headshot        = $spotlight->headshot_path ? $this->formatImageUrl($spotlight->headshot_path) : null;
            $artworkPhotos   = $spotlight->artwork_photo_paths && is_array($spotlight->artwork_photo_paths)
                ? array_values(array_filter(array_map([$this, 'formatImageUrl'], $spotlight->artwork_photo_paths)))
                : [];
            $behindScenes    = $spotlight->behind_scenes_photo_path ? $this->formatImageUrl($spotlight->behind_scenes_photo_path) : null;
            $introVideo      = $spotlight->intro_video_path ? $this->formatImageUrl($spotlight->intro_video_path) : null;
        } else {
            // Map business photos onto the same key names as artist so both
            // responses have an identical structure.
            $headshot        = $spotlight->portrait_photo_path ? $this->formatImageUrl($spotlight->portrait_photo_path) : null;
            $artworkPhotos   = $spotlight->product_service_photo_paths && is_array($spotlight->product_service_photo_paths)
                ? array_values(array_filter(array_map([$this, 'formatImageUrl'], $spotlight->product_service_photo_paths)))
                : [];
            $behindScenes    = $spotlight->storefront_workspace_photo_path ? $this->formatImageUrl($spotlight->storefront_workspace_photo_path) : null;
            $introVideo      = null;
        }

        return [
            'headshot'            => $headshot,
            'artwork_photos'      => $artworkPhotos,
            'behind_scenes_photo' => $behindScenes,
            'intro_video'         => $introVideo,
        ];
    }

    /**
     * Convert a storage path or URL to a public URL.
     *
     * Strips the 'storage/' prefix that FileHandle adds so it is not
     * duplicated when Storage::disk('public')->url() prepends it.
     */
    private function formatImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
    }
}
