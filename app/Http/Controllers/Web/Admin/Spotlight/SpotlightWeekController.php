<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;


class SpotlightWeekController extends Controller
{
    /**
     * Display a listing of spotlight weeks.
     */
    public function index(Request $request)
    {        $stats = [
            'total'      => SpotlightWeek::count(),
            'pending'    => SpotlightWeek::where('status', 'pending')->count(),
            'nominating' => SpotlightWeek::where('status', 'nominating')->count(),
            'voting'     => SpotlightWeek::where('status', 'voting')->count(),
            'completed'  => SpotlightWeek::where('status', 'completed')->count(),
            'cancelled'  => SpotlightWeek::where('status', 'cancelled')->count(),
        ];

        $editWeekId = $request->query('edit');

        if ($request->ajax()) {
            $query = SpotlightWeek::withCount('nominees', 'applications')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('week_label', function ($row) {
                    return 'Week ' . e($row->week_number) . ' (' . e($row->year) . ')';
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending' => 'bg-warning-subtle text-warning',
                        'nominating' => 'bg-info-subtle text-info',
                        'voting' => 'bg-success-subtle text-success',
                        'completed' => 'bg-primary-subtle text-primary',
                        'cancelled' => 'bg-danger-subtle text-danger',
                    ];
                    $class = $map[$row->status] ?? 'bg-secondary-subtle text-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst(e($row->status)) . '</span>';
                })
                ->addColumn('voting_window', function ($row) {
                    $start = $row->voting_starts_at?->format('M d, Y h:i A') ?? '—';
                    $end = $row->voting_ends_at?->format('M d, Y h:i A') ?? '—';
                    return '<span class="text-nowrap">' . e($start) . ' — ' . e($end) . '</span>';
                })
                ->addColumn('nominees_count', function ($row) {
                    return (int) $row->nominees_count;
                })
                ->addColumn('action', function ($row) {
                    $showBtn = '<a href="' . route('admin.spotlight.weeks.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                    $editBtn = '<button class="btn btn-sm btn-soft-primary edit-week" data-id="' . $row->id . '" title="Edit"><i class="ri-pencil-line"></i></button>';
                    $deleteBtn = '
                        <form action="' . route('admin.spotlight.weeks.destroy', $row->id) . '" method="POST" class="d-inline" data-confirm="Delete Week ' . e($row->week_number) . ' (' . e($row->year) . ')? This cannot be undone." data-confirm-type="danger">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                        </form>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['status_badge', 'voting_window', 'action'])
                ->make(true);
        }

        return view('web.admin.spotlight.weeks.index', compact('stats', 'editWeekId'));
    }

    /**
     * Store a newly created spotlight week.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1|max:53',
            'year' => 'required|integer|min:2020|max:2099',
            'voting_starts_at' => 'nullable|date',
            'voting_ends_at' => 'nullable|date|after_or_equal:voting_starts_at',
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        SpotlightWeek::create($validated);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week created successfully.');
    }

    /**
     * Return week data for the edit modal.
     */
    public function edit(SpotlightWeek $week)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $week->id,
                'week_number' => $week->week_number,
                'year' => $week->year,
                'status' => $week->status,
                'voting_starts_at' => $week->voting_starts_at?->format('Y-m-d\TH:i'),
                'voting_ends_at' => $week->voting_ends_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    /**
     * Update an existing spotlight week.
     */
    public function update(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1|max:53',
            'year' => 'required|integer|min:2020|max:2099',
            'voting_starts_at' => 'nullable|date',
            'voting_ends_at' => 'nullable|date|after_or_equal:voting_starts_at',
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $week->update($validated);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week updated successfully.');
    }

    /**
     * Delete a spotlight week.
     */
    public function destroy(SpotlightWeek $week)
    {
        if (in_array($week->status, ['voting', 'completed'])) {
            return redirect()->back()->with('error', 'Cannot delete a week that is in voting or completed status.');
        }

        $week->delete();

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week deleted successfully.');
    }

    /**
     * Quick status change for a spotlight week.
     */
    public function updateStatus(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $current = $week->status;

        // Validate allowed transitions
        $allowed = [
            'pending' => ['nominating', 'cancelled'],
            'nominating' => ['pending', 'voting', 'cancelled'],
            'voting' => ['voting_closed', 'cancelled'],
            'voting_closed' => ['voting', 'completed', 'cancelled'],
            'completed' => [],
            'cancelled' => ['pending'],
        ];

        if (!in_array($newStatus, $allowed[$current] ?? [])) {
            return redirect()->back()->with('error', "Cannot change status from '{$current}' to '{$newStatus}'.");
        }

        // If transitioning to completed, require that a winner is set
        if ($newStatus === 'completed' && !$week->winner_spotlightable_id) {
            return redirect()->back()->with('error', 'Cannot mark as completed without a winner. Use "Announce Winner" instead.');
        }

        $week->update(['status' => $newStatus]);

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', "Week status changed to '{$newStatus}' successfully.");
    }

    /**
     * Show week details with nominees.
     */
    public function show(SpotlightWeek $week)
    {
        $week->load(['nominees.spotlightable', 'nominees.user.profile']);

        $nominations = $week->nominees()->orderByDesc('total_vote_count')->get();

        return view('web.admin.spotlight.weeks.show', compact('week', 'nominations'));
    }

    /**
     * Show applications for a specific week.
     */
    public function applications(SpotlightWeek $week)
    {
        $applications = $week->applications()
            ->with(['spotlightable', 'user.profile'])
            ->latest('applied_at')
            ->paginate(20);

        return view('web.admin.spotlight.weeks.applications', compact('week', 'applications'));
    }

    /**
     * Select nominees (Top N) from applications for a week.
     */
    public function selectNominees(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'nominee_ids'   => 'required|array|min:1',
            'nominee_ids.*' => 'exists:spotlight_applications,id',
        ]);

        if (!in_array($week->status, ['pending', 'nominating'])) {
            return redirect()->back()->with('error', 'This week is not accepting nominee selection.');
        }

        DB::transaction(function () use ($week, $validated) {
            // Mark applications as selected
            SpotlightApplication::whereIn('id', $validated['nominee_ids'])
                ->update(['status' => 'selected', 'reviewed_at' => now(), 'reviewed_by' => auth('admin')->id()]);

            // Reject the rest
            $week->applications()->whereNotIn('id', $validated['nominee_ids'])
                ->where('status', 'pending')
                ->update(['status' => 'rejected', 'reviewed_at' => now(), 'reviewed_by' => auth('admin')->id()]);

            // Create nominee records for selected applications (skip if already a nominee)
            $applications = SpotlightApplication::whereIn('id', $validated['nominee_ids'])->get();
            $existingSpotlightableIds = $week->nominees()
                ->whereIn('spotlightable_type', $applications->pluck('spotlightable_type')->unique())
                ->whereIn('spotlightable_id', $applications->pluck('spotlightable_id'))
                ->pluck('spotlightable_id')
                ->toArray();

            foreach ($applications as $app) {
                if (in_array($app->spotlightable_id, $existingSpotlightableIds)) {
                    continue;
                }
                $week->nominees()->create([
                    'spotlightable_type' => $app->spotlightable_type,
                    'spotlightable_id' => $app->spotlightable_id,
                    'user_id' => $app->user_id,
                    'free_vote_count' => 0,
                    'paid_vote_count' => 0,
                    'total_vote_count' => 0,
                ]);
            }

            // Move week to voting status
            $week->update(['status' => 'voting']);
        });

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Nominees selected and voting opened successfully.');
    }

    /**
     * Force close voting for a week.
     */
    public function closeVoting(SpotlightWeek $week)
    {
        if ($week->status !== 'voting') {
            return redirect()->back()->with('error', 'Voting is not currently open for this week.');
        }

        $week->update([
            'status' => 'voting_closed',
            'voting_ends_at' => now(),
        ]);

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Voting closed successfully.');
    }

    /**
     * Announce the winner for a week.
     */
    public function announceWinner(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'nominee_id' => 'required|exists:spotlight_week_nominees,id',
        ]);

        // Ensure the nominee belongs to this week
        $nominee = $week->nominees()->findOrFail($validated['nominee_id']);

        DB::transaction(function () use ($week, $nominee) {
            // Mark previous winner as not winner
            $week->nominees()->where('is_winner', true)->update(['is_winner' => false]);

            // Mark this nominee as winner
            $nominee->update(['is_winner' => true]);

            // Update week with winner info
            $week->update([
                'status' => 'completed',
                'winner_spotlightable_type' => $nominee->spotlightable_type,
                'winner_spotlightable_id' => $nominee->spotlightable_id,
                'announced_at' => now(),
            ]);

            // Update rank for all nominees
            $this->updateRanks($week);
        });

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Winner announced successfully.');
    }

    /**
     * Cancel a spotlight week.
     */
    public function cancel(SpotlightWeek $week)
    {
        if (in_array($week->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'This week cannot be cancelled.');
        }

        $week->update(['status' => 'cancelled']);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week cancelled successfully.');
    }

    /**
     * Update ranks for all nominees in a week based on total votes.
     */
    private function updateRanks(SpotlightWeek $week): void
    {
        $nominees = $week->nominees()->orderByDesc('total_vote_count')->get();
        $rank = 1;
        foreach ($nominees as $nominee) {
            $nominee->update(['rank' => $rank]);
            $rank++;
        }
    }

    /**
     * Confirm (or change) the winner for a spotlight week.
     * Idempotent — calling it again with a different nominee switches the winner.
     */
    public function confirmWinner(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'nominee_id' => 'required|exists:spotlight_week_nominees,id',
        ]);

        $nominee = $week->nominees()->findOrFail($validated['nominee_id']);

        DB::transaction(function () use ($week, $nominee) {
            // Mark previous winner(s) as not winner
            $week->nominees()->where('is_winner', true)->update(['is_winner' => false]);

            // Mark this nominee as winner
            $nominee->update(['is_winner' => true]);

            // Update week with winner info
            $week->update([
                'status' => 'completed',
                'winner_spotlightable_type' => $nominee->spotlightable_type,
                'winner_spotlightable_id' => $nominee->spotlightable_id,
                'announced_at' => now(),
            ]);

            // Update rank for all nominees
            $this->updateRanks($week);
        });

        $spotlight = $nominee->spotlightable;
        $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
        $name = $isArtist
            ? ($spotlight?->artist_stage_name ?? $spotlight?->full_legal_name ?? '#' . $nominee->id)
            : ($spotlight?->business_name ?? $spotlight?->brand_name ?? '#' . $nominee->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$name} confirmed as the winner for Week {$week->week_number} ({$week->year}).",
            ]);
        }

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', "{$name} confirmed as the winner successfully.");
    }

    /**
     * Get showcase details for a nominee in a week.
     */
    public function getShowcase(SpotlightWeek $week, SpotlightWeekNominee $nominee): JsonResponse
    {
        if ((int) $nominee->spotlight_week_id !== (int) $week->id) {
            return response()->json(['success' => false, 'message' => 'Nominee does not belong to this week.'], 422);
        }

        $nominee->load(['spotlightable', 'user.profile']);
        $spotlight = $nominee->spotlightable;
        $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;

        $metadata = $week->metadata ?? [];
        $showcases = $metadata['showcases'] ?? [];
        $showcase = $showcases[$nominee->id] ?? [];
        $excludedMediaIds = $showcase['excluded_media_ids'] ?? [];

        $displayName = $isArtist
            ? ($spotlight?->artist_stage_name ?? $spotlight?->full_legal_name ?? '#' . $nominee->id)
            : ($spotlight?->business_name ?? $spotlight?->owner_founder_name ?? '#' . $nominee->id);

        $defaultDescription = $isArtist
            ? ($spotlight?->short_bio ?? $spotlight?->full_artist_story ?? '')
            : ($spotlight?->business_story ?? $spotlight?->products_services ?? '');

        // Gather original submitted media
        $originalMedia = [];
        if ($spotlight) {
            if ($isArtist) {
                if ($spotlight->headshot_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->headshot_path);
                    $originalMedia[] = [
                        'id'          => 'orig_headshot',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'image/jpeg',
                        'type'        => 'image',
                        'source'      => 'Headshot',
                        'is_excluded' => in_array('orig_headshot', $excludedMediaIds, true),
                    ];
                }
                if ($spotlight->artwork_photo_paths && is_array($spotlight->artwork_photo_paths)) {
                    foreach ($spotlight->artwork_photo_paths as $idx => $path) {
                        $cleanPath = preg_replace('#^storage/#', '', $path);
                        $id = 'orig_artwork_' . $idx;
                        $originalMedia[] = [
                            'id'          => $id,
                            'file_path'   => asset('storage/' . $cleanPath),
                            'file_name'   => basename($cleanPath),
                            'mime_type'   => 'image/jpeg',
                            'type'        => 'image',
                            'source'      => 'Artwork Photo ' . ($idx + 1),
                            'is_excluded' => in_array($id, $excludedMediaIds, true),
                        ];
                    }
                }
                if ($spotlight->behind_scenes_photo_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->behind_scenes_photo_path);
                    $originalMedia[] = [
                        'id'          => 'orig_behind_scenes',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'image/jpeg',
                        'type'        => 'image',
                        'source'      => 'Behind Scenes Photo',
                        'is_excluded' => in_array('orig_behind_scenes', $excludedMediaIds, true),
                    ];
                }
                if ($spotlight->intro_video_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->intro_video_path);
                    $originalMedia[] = [
                        'id'          => 'orig_intro_video',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'video/mp4',
                        'type'        => 'video',
                        'source'      => 'Intro Video',
                        'is_excluded' => in_array('orig_intro_video', $excludedMediaIds, true),
                    ];
                }
            } else {
                if ($spotlight->portrait_photo_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->portrait_photo_path);
                    $originalMedia[] = [
                        'id'          => 'orig_portrait',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'image/jpeg',
                        'type'        => 'image',
                        'source'      => 'Portrait Photo',
                        'is_excluded' => in_array('orig_portrait', $excludedMediaIds, true),
                    ];
                }
                if ($spotlight->product_service_photo_paths && is_array($spotlight->product_service_photo_paths)) {
                    foreach ($spotlight->product_service_photo_paths as $idx => $path) {
                        $cleanPath = preg_replace('#^storage/#', '', $path);
                        $id = 'orig_product_' . $idx;
                        $originalMedia[] = [
                            'id'          => $id,
                            'file_path'   => asset('storage/' . $cleanPath),
                            'file_name'   => basename($cleanPath),
                            'mime_type'   => 'image/jpeg',
                            'type'        => 'image',
                            'source'      => 'Product/Service Photo ' . ($idx + 1),
                            'is_excluded' => in_array($id, $excludedMediaIds, true),
                        ];
                    }
                }
                if ($spotlight->storefront_workspace_photo_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->storefront_workspace_photo_path);
                    $originalMedia[] = [
                        'id'          => 'orig_storefront',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'image/jpeg',
                        'type'        => 'image',
                        'source'      => 'Storefront Workspace Photo',
                        'is_excluded' => in_array('orig_storefront', $excludedMediaIds, true),
                    ];
                }
                if ($spotlight->team_photo_path) {
                    $cleanPath = preg_replace('#^storage/#', '', $spotlight->team_photo_path);
                    $originalMedia[] = [
                        'id'          => 'orig_team',
                        'file_path'   => asset('storage/' . $cleanPath),
                        'file_name'   => basename($cleanPath),
                        'mime_type'   => 'image/jpeg',
                        'type'        => 'image',
                        'source'      => 'Team Photo',
                        'is_excluded' => in_array('orig_team', $excludedMediaIds, true),
                    ];
                }
            }
        }

        // Custom uploaded media
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
                'nominee_id'         => $nominee->id,
                'display_name'       => $displayName,
                'title'              => $showcase['title'] ?? $displayName,
                'description'        => $showcase['description'] ?? $defaultDescription,
                'custom_media'       => $customMedia,
                'original_media'     => $originalMedia,
                'excluded_media_ids' => $excludedMediaIds,
            ],
        ]);
    }

    /**
     * Toggle exclude/hide status for an original media item in showcase.
     */
    public function toggleExcludeMedia(Request $request, SpotlightWeek $week, SpotlightWeekNominee $nominee): JsonResponse
    {
        $validated = $request->validate([
            'media_id' => 'required|string',
        ]);

        $metadata = $week->metadata ?? [];
        $showcases = $metadata['showcases'] ?? [];
        $showcase = $showcases[$nominee->id] ?? [];
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
        $showcases[$nominee->id] = $showcase;
        $metadata['showcases'] = $showcases;
        $week->metadata = $metadata;
        $week->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $this->getShowcase($week, $nominee)->getData()->data,
        ]);
    }

    /**
     * Update showcase details (title, description, and upload new media).
     */
    public function updateShowcase(Request $request, SpotlightWeek $week, SpotlightWeekNominee $nominee): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'media_files'   => 'nullable|array',
            'media_files.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm|max:51200',
        ]);

        $metadata = $week->metadata ?? [];
        $showcases = $metadata['showcases'] ?? [];
        $showcase = $showcases[$nominee->id] ?? [];

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
                $path = $file->store('spotlight_showcase_media', 'public');
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

        $showcases[$nominee->id] = $showcase;
        $metadata['showcases'] = $showcases;
        $week->metadata = $metadata;
        $week->save();

        return response()->json([
            'success' => true,
            'message' => 'Showcase details updated successfully.',
            'data'    => $this->getShowcase($week, $nominee)->getData()->data,
        ]);
    }

    /**
     * Delete a specific custom media item from the showcase.
     */
    public function deleteShowcaseMedia(SpotlightWeek $week, SpotlightWeekNominee $nominee, int $mediaIndex): JsonResponse
    {
        $metadata = $week->metadata ?? [];
        $showcases = $metadata['showcases'] ?? [];
        $showcase = $showcases[$nominee->id] ?? [];
        $media    = $showcase['media'] ?? [];

        if (isset($media[$mediaIndex])) {
            $fileToDelete = $media[$mediaIndex]['file_path'] ?? null;
            if ($fileToDelete) {
                Storage::disk('public')->delete(preg_replace('#^storage/#', '', $fileToDelete));
            }

            unset($media[$mediaIndex]);
            $showcase['media'] = array_values($media);
            $showcases[$nominee->id] = $showcase;
            $metadata['showcases'] = $showcases;
            $week->metadata = $metadata;
            $week->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Custom media deleted successfully.',
        ]);
    }
}

