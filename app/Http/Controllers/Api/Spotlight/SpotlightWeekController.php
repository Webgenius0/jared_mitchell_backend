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
use Carbon\Carbon;
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
     * Format a spotlight week nominee winner into a clean response array with showcase data.
     */
    private function formatWinner($nominee): array
    {
        $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
        $spotlight = $nominee->spotlightable;
        $week = $nominee->week;

        $metadata = $week?->metadata ?? [];
        $showcases = $metadata['showcases'] ?? [];
        $showcase = $showcases[$nominee->id] ?? [];
        $excludedMediaIds = $showcase['excluded_media_ids'] ?? [];

        $defaultName = $isArtist
            ? ($spotlight?->artist_stage_name ?? $spotlight?->full_legal_name)
            : ($spotlight?->business_name ?? $spotlight?->owner_founder_name);

        $defaultDescription = $isArtist
            ? ($spotlight?->short_bio ?? $spotlight?->full_artist_story)
            : ($spotlight?->business_story ?? $spotlight?->products_services);

        $title = $showcase['title'] ?? $defaultName;
        $description = $showcase['description'] ?? $defaultDescription;

        // Custom uploaded media
        $customMedia = ! empty($showcase['media'])
            ? array_map(function ($m) {
                $path = $m['file_path'] ?? '';
                return [
                    'id'        => $m['id'] ?? null,
                    'file_name' => $m['file_name'] ?? basename($path),
                    'url'       => $this->formatImageUrl($path),
                    'mime_type' => $m['mime_type'] ?? null,
                    'type'      => $m['type'] ?? 'image',
                ];
            }, $showcase['media'])
            : [];

        // Format media respecting excluded IDs
        $media = $this->formatSpotlightMedia($spotlight, $isArtist, $excludedMediaIds, $customMedia);

        return [
            'id'          => $nominee->id,
            'week_number' => $week?->week_number,
            'year'        => $week?->year,
            'title'       => $title,
            'description' => $description,
            'spotlight'   => $spotlight ? [
                'id'           => $spotlight->id,
                'type'         => $isArtist ? 'artist' : 'business',
                'name'         => $title,
                'default_name' => $defaultName,
                'city'         => $spotlight->city ?? null,
                'state'        => $spotlight->state ?? null,
                'media'        => $media,
            ] : null,
            'showcase'    => [
                'title'              => $title,
                'description'        => $description,
                'custom_media'       => $customMedia,
                'excluded_media_ids' => $excludedMediaIds,
            ],
            'owner'       => [
                'id'   => $nominee->user?->id,
                'name' => $nominee->user?->profile?->name ?? $nominee->user?->email ?? '—',
            ],
            'total_votes'  => $nominee->total_vote_count,
            'free_votes'   => $nominee->free_vote_count,
            'paid_votes'   => $nominee->paid_vote_count,
            'announced_at' => $week?->announced_at ?? $week?->updated_at,
        ];
    }

    /**
     * Format spotlight media into full URLs, matching the pattern in SpotlightDetailsController.
     */
    private function formatSpotlightMedia($spotlight, bool $isArtist, array $excludedMediaIds = [], array $customMedia = []): array
    {
        if (! $spotlight) {
            return [
                'headshot'            => null,
                'artwork_photos'      => [],
                'behind_scenes_photo' => null,
                'intro_video'         => null,
                'custom_media'        => $customMedia,
            ];
        }

        if ($isArtist) {
            $headshot = (! in_array('orig_headshot', $excludedMediaIds, true) && $spotlight->headshot_path)
                ? $this->formatImageUrl($spotlight->headshot_path)
                : null;

            $artworkPhotos = [];
            if ($spotlight->artwork_photo_paths && is_array($spotlight->artwork_photo_paths)) {
                foreach ($spotlight->artwork_photo_paths as $idx => $path) {
                    if (! in_array('orig_artwork_' . $idx, $excludedMediaIds, true)) {
                        $formatted = $this->formatImageUrl($path);
                        if ($formatted) {
                            $artworkPhotos[] = $formatted;
                        }
                    }
                }
            }

            $behindScenes = (! in_array('orig_behind_scenes', $excludedMediaIds, true) && $spotlight->behind_scenes_photo_path)
                ? $this->formatImageUrl($spotlight->behind_scenes_photo_path)
                : null;

            $introVideo = (! in_array('orig_intro_video', $excludedMediaIds, true) && $spotlight->intro_video_path)
                ? $this->formatImageUrl($spotlight->intro_video_path)
                : null;
        } else {
            $headshot = (! in_array('orig_portrait', $excludedMediaIds, true) && $spotlight->portrait_photo_path)
                ? $this->formatImageUrl($spotlight->portrait_photo_path)
                : null;

            $artworkPhotos = [];
            if ($spotlight->product_service_photo_paths && is_array($spotlight->product_service_photo_paths)) {
                foreach ($spotlight->product_service_photo_paths as $idx => $path) {
                    if (! in_array('orig_product_' . $idx, $excludedMediaIds, true)) {
                        $formatted = $this->formatImageUrl($path);
                        if ($formatted) {
                            $artworkPhotos[] = $formatted;
                        }
                    }
                }
            }

            $behindScenes = (! in_array('orig_storefront', $excludedMediaIds, true) && $spotlight->storefront_workspace_photo_path)
                ? $this->formatImageUrl($spotlight->storefront_workspace_photo_path)
                : null;

            $introVideo = null;
        }

        return [
            'headshot'            => $headshot,
            'artwork_photos'      => $artworkPhotos,
            'behind_scenes_photo' => $behindScenes,
            'intro_video'         => $introVideo,
            'custom_media'        => $customMedia,
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

    /**
     * GET /api/v1/spotlight/weeks/upcoming-countdown
     * GET /api/v1/spotlight/upcoming-countdown
     *
     * Simple countdown to when the upcoming spotlight week's voting starts.
     * Guaranteed to return the next future week after the currently running week.
     */
    public function upcomingCountdown(Request $request): JsonResponse
    {
        $now = now();

        // 1. Get running week ID so we exclude it from upcoming query
        $runningWeek = SpotlightWeek::query()
            ->where(function ($q) use ($now) {
                $q->where('status', 'voting')
                  ->orWhere('status', 'nominating');
            })
            ->where('voting_starts_at', '<=', $now)
            ->first();

        $runningWeekId = $runningWeek?->id;

        // 2. Find upcoming week whose voting starts in the future, excluding current running week
        $week = SpotlightWeek::query()
            ->where('voting_starts_at', '>', $now)
            ->when($runningWeekId, fn ($q) => $q->where('id', '!=', $runningWeekId))
            ->orderBy('voting_starts_at', 'asc')
            ->first();

        if (! $week) {
            // Fallback: any pending week in the future
            $week = SpotlightWeek::where('status', 'pending')
                ->when($runningWeekId, fn ($q) => $q->where('id', '!=', $runningWeekId))
                ->orderBy('voting_starts_at', 'asc')
                ->first();
        }

        if (! $week) {
            return $this->error(null, 'No upcoming spotlight week found.', 404);
        }

        $countdown = $this->calculateWeekCountdown($week->voting_starts_at, $now);

        return $this->success('Upcoming spotlight week countdown retrieved successfully.', [
            'id'                        => $week->id,
            'week_number'               => $week->week_number,
            'year'                      => $week->year,
            'name'                      => "Spotlight Week {$week->week_number} ({$week->year})",
            'status'                    => $week->status,
            'phase'                     => 'upcoming',
            'is_accepting_applications' => $week->isAcceptingApplications(),
            'is_voting_open'            => $week->isVotingOpen(),
            'voting_starts_at'          => $week->voting_starts_at?->toIso8601String(),
            'voting_ends_at'            => $week->voting_ends_at?->toIso8601String(),
            'target_date'               => $week->voting_starts_at?->toIso8601String(),
            'countdown'                 => $countdown,
        ]);
    }

    /**
     * GET /api/v1/spotlight/weeks/running-countdown
     * GET /api/v1/spotlight/running-countdown
     *
     * Returns the currently active/running spotlight week (voting or nominating)
     * with countdown until voting ends or voting starts.
     */
    public function runningCountdown(Request $request): JsonResponse
    {
        $now = now();

        // 1. Try to find active voting week
        $week = SpotlightWeek::query()
            ->where('status', 'voting')
            ->where('voting_starts_at', '<=', $now)
            ->where('voting_ends_at', '>=', $now)
            ->first();

        // 2. If no voting week is active, find the current nominating week currently in progress
        if (! $week) {
            $week = SpotlightWeek::query()
                ->where('status', 'nominating')
                ->where('voting_starts_at', '<=', $now)
                ->first();
        }

        // 3. Fallback: most recent voting week
        if (! $week) {
            $week = SpotlightWeek::where('status', 'voting')
                ->orderBy('voting_starts_at', 'desc')
                ->first();
        }

        if (! $week) {
            return $this->error(null, 'No running spotlight week found.', 404);
        }

        $isVotingOpen = $week->isVotingOpen();
        $isAcceptingApplications = $week->isAcceptingApplications();

        if ($isVotingOpen) {
            $phase = 'voting';
        } elseif ($isAcceptingApplications) {
            $phase = 'nomination';
        } else {
            $phase = $week->status;
        }

        // Target Date Logic:
        // If voting_starts_at is in the future -> target is voting_starts_at
        // If voting_starts_at has passed -> target is voting_ends_at (count down until week ends)
        if ($week->voting_starts_at && $week->voting_starts_at > $now) {
            $targetDate = $week->voting_starts_at;
        } else {
            $targetDate = $week->voting_ends_at;
        }

        $countdown = $this->calculateWeekCountdown($targetDate, $now);

        return $this->success('Running spotlight week retrieved successfully.', [
            'id'                        => $week->id,
            'week_number'               => $week->week_number,
            'year'                      => $week->year,
            'name'                      => "Spotlight Week {$week->week_number} ({$week->year})",
            'status'                    => $week->status,
            'phase'                     => $phase,
            'is_accepting_applications' => $isAcceptingApplications,
            'is_voting_open'            => $isVotingOpen,
            'voting_starts_at'          => $week->voting_starts_at?->toIso8601String(),
            'voting_ends_at'            => $week->voting_ends_at?->toIso8601String(),
            'target_date'               => $targetDate?->toIso8601String(),
            'countdown'                 => $countdown,
        ]);
    }

    /**
     * Calculate difference formatted as days, hours, minutes, seconds.
     */
    private function calculateWeekCountdown(?Carbon $targetDate, Carbon $now): array
    {
        if (! $targetDate || $targetDate <= $now) {
            return [
                'formatted'       => '00 Days : 00 Hours : 00 Minutes : 00 Seconds',
                'formatted_short' => '0d 0h 0m 0s',
            ];
        }

        $diff    = $now->diff($targetDate);
        $days    = (int) $diff->days;
        $hours   = (int) $diff->h;
        $minutes = (int) $diff->i;
        $seconds = (int) $diff->s;

        $paddedDays    = sprintf('%02d', $days);
        $paddedHours   = sprintf('%02d', $hours);
        $paddedMinutes = sprintf('%02d', $minutes);
        $paddedSeconds = sprintf('%02d', $seconds);

        return [
            'formatted'       => "{$paddedDays} Days : {$paddedHours} Hours : {$paddedMinutes} Minutes : {$paddedSeconds} Seconds",
            'formatted_short' => "{$days}d {$hours}h {$minutes}m {$seconds}s",
        ];
    }
}
