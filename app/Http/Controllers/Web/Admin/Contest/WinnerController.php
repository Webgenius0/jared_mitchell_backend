<?php

namespace App\Http\Controllers\Web\Admin\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Contest\RoundSubmission;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Services\Contest\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class WinnerController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboardService,
    ) {}

    /**
     * Winners page — shows the top 3 finalists of every season's final round.
     * The scheduler only prepares finalists; the admin confirms the winner here.
     *
     * AJAX (DataTables) requests return the table data; normal requests render the page.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->data($request);
        }

        $seasons = Season::orderByDesc('id')->get();

        return view('web.admin.winners.index', compact('seasons'));
    }

    /**
     * DataTables source: top 3 candidates of each season's final round.
     */
    public function data(Request $request)
    {
        $seasonId = $request->integer('season_id') ?: null;
        $roundId  = $request->integer('round_id') ?: null;

        $seasons = Season::query()
            ->when($seasonId, fn ($q) => $q->where('id', $seasonId))
            ->orderByDesc('id')
            ->get();

        $rows = [];

        foreach ($seasons as $season) {
            $finalRound = $season->rounds()
                ->orderByDesc('round_number')
                ->orderByDesc('sort_order')
                ->first();

            if (!$finalRound) {
                continue;
            }

            // Only final rounds are listed on this page.
            if ($roundId && (int) $finalRound->id !== (int) $roundId) {
                continue;
            }

            $candidates = $this->candidatesForFinalRound($season, $finalRound);

            if (empty($candidates)) {
                continue;
            }

            // A winner exists when a candidate actually carries the winner status.
            $hasWinner = collect($candidates)->contains(fn ($c) => $c['status'] === 'winner');

            // Confirming/choosing a winner requires at least 1 finalist.
            $canConfirm = count($candidates) >= 1;

            foreach ($candidates as $candidate) {
                $rows[] = [
                    'season_id'     => $season->id,
                    'season'        => $season->title,
                    'round_id'      => $finalRound->id,
                    'round'         => 'Round ' . $finalRound->round_number . ' — ' . $finalRound->title,
                    'business'      => $candidate['display_name'],
                    'avatar_url'    => $candidate['avatar_url'],
                    'points'        => (float) $candidate['score'],
                    'rank'          => (int) $candidate['rank'],
                    'status'        => $candidate['status'],
                    'is_winner'     => $candidate['status'] === 'winner',
                    'contestant_id' => $candidate['contestant_id'],
                    'has_winner'    => $hasWinner,
                    'can_confirm'   => $canConfirm,
                ];
            }
        }

        return DataTables::of(collect($rows))
            ->addIndexColumn()
            ->editColumn('business', fn (array $row) => $this->businessCell($row))
            ->editColumn('points', fn (array $row) => '<strong>' . number_format($row['points'], 2) . '</strong>')
            ->editColumn('rank', fn (array $row) => $this->rankBadge($row['rank']))
            ->editColumn('status', fn (array $row) => $this->statusBadge($row['status']))
            ->editColumn('action', fn (array $row) => $this->actionCell($row))
            ->rawColumns(['business', 'points', 'rank', 'status', 'action'])
            ->make(true);
    }

    /**
     * JSON list of rounds for a season (used by the dependent round filter).
     */
    public function rounds(Request $request): JsonResponse
    {
        $seasonId = $request->integer('season_id');

        if (!$seasonId) {
            return response()->json([]);
        }

        $season = Season::find($seasonId);

        if (!$season) {
            return response()->json([]);
        }

        $finalRoundId = $season->rounds()
            ->orderByDesc('round_number')
            ->orderByDesc('sort_order')
            ->value('id');

        return response()->json(
            $season->rounds()
                ->orderBy('round_number')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Round $round) => [
                    'id'           => $round->id,
                    'round_number' => $round->round_number,
                    'title'        => $round->title,
                    'is_final'     => (int) $round->id === (int) $finalRoundId,
                ])
        );
    }

    /**
     * Confirm (or change) the winner among the final round's top 3.
     * Idempotent — calling it again with a different finalist simply switches
     * the winner. The confirmed winner is the one that shows in the public API.
     */
    public function confirmWinner(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'contestant_id' => 'required|integer|exists:contestants,id',
        ]);

        $season = $round->season;

        $finalRound = $season->rounds()
            ->orderByDesc('round_number')
            ->orderByDesc('sort_order')
            ->first();

        if (!$finalRound || (int) $finalRound->id !== (int) $round->id) {
            return response()->json([
                'success' => false,
                'message' => 'A winner can only be confirmed from the final round of the season.',
            ], 422);
        }

        // Do not allow confirming a winner while the final round is still running.
        $seasonSettled = in_array($season->status, ['awaiting_final_review', 'completed']);
        if (!$seasonSettled && !$finalRound->hasEnded()) {
            return response()->json([
                'success' => false,
                'message' => 'The final round has not ended yet. The winner can only be confirmed after the round is finalized.',
            ], 422);
        }

        $candidates = $this->candidatesForFinalRound($season, $finalRound);

        if (count($candidates) < 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least 1 finalist is required before a winner can be confirmed.',
            ], 422);
        }

        $ids = collect($candidates)->pluck('contestant_id')->all();

        if (!in_array((int) $validated['contestant_id'], $ids, true)) {
            return response()->json([
                'success' => false,
                'message' => 'This contestant is not among the top 3 finalists of the final round.',
            ], 422);
        }

        $winner = null;

        DB::transaction(function () use ($candidates, $validated, $season, &$winner) {
            // Reset any existing winner/runner_up status for this season first
            Contestant::where('season_id', $season->id)
                ->whereIn('status', ['winner', 'runner_up'])
                ->update([
                    'status'     => 'finalist',
                    'updated_at' => now(),
                ]);

            // Selected finalist → winner; best-ranked non-selected → runner up.
            $selectedIndex = null;
            $runnerUpIndex = null;
            $bestRank      = PHP_INT_MAX;

            foreach ($candidates as $index => $candidate) {
                if ((int) $candidate['contestant_id'] === (int) $validated['contestant_id']) {
                    $selectedIndex = $index;
                }
            }

            foreach ($candidates as $index => $candidate) {
                if ($index === $selectedIndex) {
                    continue;
                }
                if ((int) $candidate['rank'] < $bestRank) {
                    $bestRank      = (int) $candidate['rank'];
                    $runnerUpIndex = $index;
                }
            }

            foreach ($candidates as $index => $candidate) {
                $contestant = Contestant::find($candidate['contestant_id']);
                if (!$contestant) {
                    continue;
                }

                if ($index === $selectedIndex) {
                    $status = 'winner';
                    $winner = $contestant;
                } else {
                    $status = $index === $runnerUpIndex ? 'runner_up' : 'finalist';
                }

                $contestant->status                 = $status;
                $contestant->eliminated_in_round_id = null;
                $contestant->updated_at             = now();
                $contestant->save();
            }

            $metadata = $season->metadata ?? [];

            // Refresh the stored snapshot so the Winners page stays in sync.
            $metadata['winner_candidates'] = array_map(
                fn ($candidate, $index) => [
                    'contestant_id' => $candidate['contestant_id'],
                    'display_name'  => $candidate['display_name'],
                    'rank'          => $index + 1,
                    'score'         => $candidate['score'],
                ],
                $candidates,
                array_keys($candidates)
            );
            $metadata['winner_confirmed']     = true;
            $metadata['winner_contestant_id'] = (int) $validated['contestant_id'];
            $metadata['winner_business_id']   = $winner?->contestable_id;
            $metadata['winner_confirmed_at']  = now()->toISOString();

            $season->metadata  = $metadata;
            $season->status    = 'completed';
            $season->is_active = false;
            $season->ends_at   = now();
            $season->updated_at = now();
            $season->save();
        });

        return response()->json([
            'success' => true,
            'message' => $winner
                ? "{$winner->display_name} confirmed as the winner of {$season->title}."
                : 'Winner confirmed successfully.',
        ]);
    }

    /**
     * Top 3 candidates for a season's final round.
     *
     * Resolution order:
     * 1. The snapshot stored by the scheduler (stable even if votes change later).
     * 2. A live leaderboard for the final round.
     * 3. Existing winner/runner_up/finalist contestants of the season (covers
     *    seasons that were finalized before the confirmation flow existed).
     *
     * @return array<int, array{contestant_id:int, display_name:string, score:float, rank:int, status:string, avatar_url:?string}>
     */
    private function candidatesForFinalRound(Season $season, Round $finalRound): array
    {
        // 1. Stored snapshot.
        $stored = $season->metadata['winner_candidates'] ?? null;

        if (is_array($stored) && count($stored) > 0) {
            $candidates = [];

            foreach ($stored as $entry) {
                $contestant = Contestant::with('contestable')->find($entry['contestant_id'] ?? null);

                $candidates[] = [
                    'contestant_id' => (int) ($entry['contestant_id'] ?? 0),
                    'display_name'  => $contestant?->display_name ?? ($entry['display_name'] ?? '—'),
                    'score'         => (float) ($entry['score'] ?? 0),
                    'rank'          => (int) ($entry['rank'] ?? 1),
                    'status'        => $contestant?->status ?? 'finalist',
                    'avatar_url'    => $contestant?->avatar_url,
                ];
            }

            return $candidates;
        }

        // 2. Live leaderboard (seasons whose final round has not been finalized yet).
        $top3 = array_slice($this->leaderboardService->getLeaderboard($finalRound), 0, 3);

        if (count($top3) > 0) {
            return collect($top3)->map(function (array $entry) {
                $contestant = Contestant::with('contestable')->find($entry['contestant_id']);

                return [
                    'contestant_id' => (int) $entry['contestant_id'],
                    'display_name'  => $entry['display_name'] ?? $contestant?->display_name ?? '—',
                    'score'         => (float) ($entry['total_score'] ?? 0),
                    'rank'          => (int) ($entry['rank'] ?? 1),
                    'status'        => $contestant?->status ?? 'active',
                    'avatar_url'    => $entry['avatar_url'] ?? $contestant?->avatar_url,
                ];
            })->all();
        }

        // 3. Existing finalist-status contestants (legacy seasons or fallback).
        return Contestant::where('season_id', $season->id)
            ->whereIn('status', ['winner', 'runner_up', 'finalist', 'active', 'eliminated'])
            ->orderByDesc('total_score')
            ->take(3)
            ->get()
            ->map(fn (Contestant $contestant, int $index) => [
                'contestant_id' => $contestant->id,
                'display_name'  => $contestant->display_name,
                'score'         => (float) $contestant->total_score,
                'rank'          => $index + 1,
                'status'        => $contestant->status,
                'avatar_url'    => $contestant->avatar_url,
            ])
            ->all();
    }

    /**
     * Business cell: avatar + name.
     */
    private function businessCell(array $row): string
    {
        $avatar = $row['avatar_url']
            ? '<img src="' . e($row['avatar_url']) . '" class="rounded me-2" style="width:34px;height:34px;object-fit:cover;" alt="">'
            : '<span class="avatar-initials rounded me-2 d-inline-flex align-items-center justify-content-center bg-light text-muted" style="width:34px;height:34px;font-size:13px;"><i class="ri-store-2-line"></i></span>';

        $name = '<strong>' . e($row['business']) . '</strong>';

        if ($row['is_winner']) {
            $name .= ' <i class="ri-trophy-fill text-warning fs-15" title="Winner"></i>';
        }

        return '<div class="d-flex align-items-center">' . $avatar . $name . '</div>';
    }

    /**
     * Rank cell: 1st/2nd/3rd badge.
     */
    private function rankBadge(int $rank): string
    {
        $map = [
            1 => 'bg-warning-subtle text-warning',
            2 => 'bg-secondary-subtle text-secondary',
            3 => 'bg-danger-subtle text-danger',
        ];

        $label = match ($rank) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $rank . 'th',
        };

        $class = $map[$rank] ?? 'bg-light text-muted';

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }

    /**
     * Status cell: winner / runner up / finalist badge.
     */
    private function statusBadge(string $status): string
    {
        $map = [
            'winner'    => ['bg-success-subtle text-success', '🏆 Winner'],
            'runner_up' => ['bg-info-subtle text-info', 'Runner Up'],
            'finalist'  => ['bg-secondary-subtle text-secondary', 'Finalist'],
            'active'    => ['bg-primary-subtle text-primary', 'Active'],
        ];

        [$class, $label] = $map[$status] ?? ['bg-light text-muted', ucfirst($status)];

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }

    /**
     * Action cell: confirm / change winner button + edit showcase button.
     */
    private function actionCell(array $row): string
    {
        $showcaseBtn = '<button type="button" class="btn btn-sm btn-soft-warning edit-showcase-btn ms-1"
                    data-contestant-id="' . $row['contestant_id'] . '"
                    data-business="' . e($row['business']) . '"
                    title="Edit Showcase Title, Description & Media">
                    <i class="ri-edit-box-line me-1"></i>Edit Showcase
                </button>';

        if (!$row['can_confirm']) {
            return $showcaseBtn;
        }

        $label = $row['has_winner'] ? 'Change Winner' : 'Confirm Winner';
        $class = $row['has_winner'] ? 'btn-soft-info' : 'btn-soft-success';
        $icon  = $row['has_winner'] ? 'ri-refresh-line' : 'ri-check-double-line';

        $confirmBtn = '<button type="button" class="btn btn-sm ' . $class . ' confirm-winner-btn"
                    data-round-id="' . $row['round_id'] . '"
                    data-contestant-id="' . $row['contestant_id'] . '"
                    data-business="' . e($row['business']) . '">
                    <i class="' . $icon . ' me-1"></i>' . $label . '
                </button>';

        return '<div class="d-inline-flex align-items-center gap-1">' . $confirmBtn . $showcaseBtn . '</div>';
    }

    /**
     * Get showcase details for a contestant.
     */
    public function getShowcase(Contestant $contestant): JsonResponse
    {
        $contestable = $contestant->contestable;
        $showcase = $contestant->metadata['showcase'] ?? [];
        $excludedMediaIds = $showcase['excluded_media_ids'] ?? [];

        // 1. Business Profile Media
        $businessMedia = [];
        if ($contestable && $contestable->media && $contestable->media->count() > 0) {
            foreach ($contestable->media as $m) {
                $filePath = $m->file_path;
                $mimeType = $m->mime_type ?? '';
                $isVectorOrVideo = str_contains($mimeType, 'video') || preg_match('#\.(mp4|mov|avi|webm)$#i', $filePath);
                $businessMedia[] = [
                    'id'          => 'biz_' . $m->id,
                    'file_path'   => asset('storage/' . preg_replace('#^storage/#', '', $filePath)),
                    'file_name'   => $m->file_name ?? basename($filePath),
                    'mime_type'   => $mimeType,
                    'type'        => $isVectorOrVideo ? 'video' : 'image',
                    'source'      => 'Business Profile',
                    'is_excluded' => in_array('biz_' . $m->id, $excludedMediaIds, true),
                ];
            }
        }

        // 2. Contest Round Submission Media
        $submissionMedia = [];
        $submissions = RoundSubmission::where('contestant_id', $contestant->id)->with('round')->get();
        foreach ($submissions as $sub) {
            if (!empty($sub->media_urls)) {
                foreach ($sub->media_urls as $idx => $urlPath) {
                    $cleanPath = preg_replace('#^storage/#', '', $urlPath);
                    $isVectorOrVideo = preg_match('#\.(mp4|mov|avi|webm)$#i', $cleanPath);
                    $roundTitle = $sub->round ? "Round {$sub->round->round_number}" : 'Submission';
                    $id = 'sub_' . $sub->id . '_' . $idx;
                    $submissionMedia[] = [
                        'id'          => $id,
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => $isVectorOrVideo ? 'video/mp4' : 'image/jpeg',
                        'type'        => $isVectorOrVideo ? 'video' : 'image',
                        'source'      => $roundTitle,
                        'is_excluded' => in_array($id, $excludedMediaIds, true),
                    ];
                }
            }
        }

        $allOriginalMedia = array_merge($businessMedia, $submissionMedia);

        // 3. Custom Uploaded Media by Admin
        $customMedia = ! empty($showcase['media'])
            ? array_map(function ($m, $index) {
                $filePath = $m['file_path'] ?? '';
                $mimeType = $m['mime_type'] ?? '';
                $isVectorOrVideo = ($m['type'] ?? '') === 'video' || str_contains($mimeType, 'video') || preg_match('#\.(mp4|mov|avi|webm)$#i', $filePath);
                return [
                    'index'     => $index,
                    'id'        => $m['id'] ?? ('sc_' . $index),
                    'file_path' => asset('storage/' . preg_replace('#^storage/#', '', $filePath)),
                    'file_name' => $m['file_name'] ?? 'file',
                    'mime_type' => $mimeType,
                    'type'      => $isVectorOrVideo ? 'video' : 'image',
                    'source'    => 'Admin Uploaded',
                    'is_custom' => true,
                ];
            }, $showcase['media'], array_keys($showcase['media']))
            : [];

        return response()->json([
            'success' => true,
            'data'    => [
                'contestant_id'      => $contestant->id,
                'display_name'       => $contestant->display_name,
                'title'              => $showcase['title'] ?? $contestant->display_name,
                'description'        => $showcase['description'] ?? ($contestable?->story ?? ''),
                'custom_media'       => $customMedia,
                'original_media'     => $allOriginalMedia,
                'excluded_media_ids' => $excludedMediaIds,
            ],
        ]);
    }

    /**
     * Toggle exclude/hide status for an original media item.
     */
    public function toggleExcludeMedia(Request $request, Contestant $contestant): JsonResponse
    {
        $validated = $request->validate([
            'media_id' => 'required|string',
        ]);

        $metadata = $contestant->metadata ?? [];
        $showcase = $metadata['showcase'] ?? [];
        $excluded = $showcase['excluded_media_ids'] ?? [];

        $mediaId = $validated['media_id'];

        if (in_array($mediaId, $excluded, true)) {
            $excluded = array_values(array_diff($excluded, [$mediaId]));
            $message = 'Media restored to showcase.';
        } else {
            $excluded[] = $mediaId;
            $message = 'Media hidden from showcase.';
        }

        $showcase['excluded_media_ids'] = array_values(array_unique($excluded));
        $metadata['showcase'] = $showcase;
        $contestant->metadata = $metadata;
        $contestant->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $this->getShowcase($contestant)->getData()->data,
        ]);
    }

    /**
     * Update showcase details (title, description, and upload new media).
     */
    public function updateShowcase(Request $request, Contestant $contestant): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'media_files'   => 'nullable|array',
            'media_files.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ]);

        $metadata = $contestant->metadata ?? [];
        $showcase = $metadata['showcase'] ?? [];

        if ($request->has('title')) {
            $showcase['title'] = $validated['title'];
        }

        if ($request->has('description')) {
            $showcase['description'] = $validated['description'];
        }

        // Handle uploaded media files
        if ($request->hasFile('media_files')) {
            $uploadedMedia = $showcase['media'] ?? [];

            foreach ($request->file('media_files') as $file) {
                $path = $file->store('showcase_media', 'public');
                $mimeType = $file->getClientMimeType();
                $isVectorOrVideo = str_contains($mimeType, 'video');

                $uploadedMedia[] = [
                    'id'        => uniqid('sc_'),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'type'      => $isVectorOrVideo ? 'video' : 'image',
                    'created_at'=> now()->toISOString(),
                ];
            }

            $showcase['media'] = array_values($uploadedMedia);
        }

        $metadata['showcase'] = $showcase;
        $contestant->metadata = $metadata;
        $contestant->save();

        return response()->json([
            'success' => true,
            'message' => 'Winner showcase information updated successfully.',
            'data'    => $this->getShowcase($contestant)->getData()->data,
        ]);
    }

    /**
     * Delete a specific custom media item from the showcase.
     */
    public function deleteShowcaseMedia(Contestant $contestant, int $mediaIndex): JsonResponse
    {
        $metadata = $contestant->metadata ?? [];
        $showcase = $metadata['showcase'] ?? [];
        $media    = $showcase['media'] ?? [];

        if (isset($media[$mediaIndex])) {
            $fileToDelete = $media[$mediaIndex]['file_path'] ?? null;
            if ($fileToDelete) {
                Storage::disk('public')->delete(preg_replace('#^storage/#', '', $fileToDelete));
            }

            unset($media[$mediaIndex]);
            $showcase['media'] = array_values($media);

            $metadata['showcase'] = $showcase;
            $contestant->metadata = $metadata;
            $contestant->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully.',
        ]);
    }
}
