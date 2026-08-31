<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\WinnerArticle;
use App\Models\WinnerArticleMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class WinnerArticleWebController extends Controller
{
    /**
     * Display winner articles list view or data for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $articles = WinnerArticle::with(['media', 'contestant', 'nominee.spotlightable', 'season', 'spotlightWeek'])->latest();

            if ($request->filled('type')) {
                $articles->where('type', $request->type);
            }

            return DataTables::of($articles)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    $badgeClass = $row->type === 'boss_beginning' ? 'bg-primary' : 'bg-success';
                    $label = $row->type === 'boss_beginning' ? 'Boss Beginning Winner' : 'Spotlight Winner';
                    return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                })
                ->addColumn('winner_info', function ($row) {
                    if ($row->type === 'boss_beginning') {
                        $name = $row->contestant ? e($row->contestant->display_name) : 'Latest Boss Winner';
                        $seasonTitle = $row->season ? e($row->season->title) : null;
                        return '<div><strong>' . $name . '</strong>' . ($seasonTitle ? '<br><small class="text-muted">' . $seasonTitle . '</small>' : '') . '</div>';
                    } else {
                        $nominee = $row->nominee;
                        $name = 'Latest Spotlight Winner';
                        if ($nominee && $nominee->spotlightable) {
                            $spotlight = $nominee->spotlightable;
                            $name = e($spotlight->artist_stage_name ?? $spotlight->business_name ?? $spotlight->full_legal_name ?? $spotlight->owner_founder_name ?? 'Winner Nominee');
                        }
                        $weekInfo = $row->spotlightWeek ? "Week {$row->spotlightWeek->week_number} ({$row->spotlightWeek->year})" : null;
                        return '<div><strong>' . $name . '</strong>' . ($weekInfo ? '<br><small class="text-muted">' . $weekInfo . '</small>' : '') . '</div>';
                    }
                })
                ->editColumn('title', function ($row) {
                    return '<strong>' . e($row->title) . '</strong>';
                })
                ->editColumn('media_count', function ($row) {
                    return '<span class="badge bg-soft-info text-info"><i class="ri-attachment-line me-1"></i>' . $row->media->count() . ' Files</span>';
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-soft-primary edit-article-btn" data-id="' . $row->id . '">
                            <i class="ri-pencil-line"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger delete-article-btn" data-id="' . $row->id . '">
                            <i class="ri-delete-bin-line"></i> Delete
                        </button>
                    ';
                })
                ->rawColumns(['type', 'winner_info', 'title', 'media_count', 'is_active', 'action'])
                ->make(true);
        }

        $seasons = Season::latest()->get();
        $spotlightWeeks = SpotlightWeek::latest()->get();

        // Boss Beginning Winners list
        $bossWinners = Contestant::where('status', 'winner')
            ->with(['season', 'contestable'])
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id'           => $c->id,
                    'season_id'    => $c->season_id,
                    'display_name' => $c->display_name . ($c->season ? " ({$c->season->title})" : ''),
                ];
            });

        // Spotlight Winners list
        $spotlightWinners = SpotlightWeekNominee::where('is_winner', true)
            ->with(['spotlightable', 'week'])
            ->latest()
            ->get()
            ->map(function ($n) {
                $name = 'Nominee #' . $n->id;
                if ($n->spotlightable) {
                    $s = $n->spotlightable;
                    $name = $s->artist_stage_name ?? $s->business_name ?? $s->full_legal_name ?? $s->owner_founder_name ?? $name;
                }
                $weekLabel = $n->week ? " (Week {$n->week->week_number}, {$n->week->year})" : '';
                return [
                    'id'                => $n->id,
                    'spotlight_week_id' => $n->spotlight_week_id,
                    'display_name'      => $name . $weekLabel,
                ];
            });

        // Current/Latest Winners for Auto-Selection
        $defaultBossWinnerId = $bossWinners->first()['id'] ?? null;
        $defaultSpotlightWinnerId = $spotlightWinners->first()['id'] ?? null;

        return view('web.admin.winner_articles.index', compact('seasons', 'spotlightWeeks', 'bossWinners', 'spotlightWinners', 'defaultBossWinnerId', 'defaultSpotlightWinnerId'));
    }

    /**
     * Store new winner article with multiple image/video files.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'                       => ['required', 'string', 'in:boss_beginning,spotlight'],
            'title'                      => ['required', 'string', 'max:255'],
            'content'                    => ['required', 'string'],
            'contestant_id'              => ['nullable', 'integer'],
            'spotlight_week_nominee_id'  => ['nullable', 'integer'],
            'season_id'                  => ['nullable', 'integer'],
            'spotlight_week_id'          => ['nullable', 'integer'],
            'is_active'                  => ['sometimes', 'boolean'],
            'media'                      => ['nullable', 'array'],
            'media.*'                    => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        ]);

        $contestantId = $validated['contestant_id'] ?? null;
        $seasonId = $validated['season_id'] ?? null;
        $spotlightNomineeId = $validated['spotlight_week_nominee_id'] ?? null;
        $spotlightWeekId = $validated['spotlight_week_id'] ?? null;

        // Auto-resolve winner IDs if left empty
        if ($validated['type'] === 'boss_beginning' && !$contestantId) {
            $latestContestant = Contestant::where('status', 'winner')->latest()->first();
            $contestantId = $latestContestant?->id;
            $seasonId = $seasonId ?: $latestContestant?->season_id;
        } elseif ($validated['type'] === 'spotlight' && !$spotlightNomineeId) {
            $latestNominee = SpotlightWeekNominee::where('is_winner', true)->latest()->first();
            $spotlightNomineeId = $latestNominee?->id;
            $spotlightWeekId = $spotlightWeekId ?: $latestNominee?->spotlight_week_id;
        }

        // If contestant_id is provided, auto resolve season_id if missing
        if ($contestantId && !$seasonId) {
            $contestant = Contestant::find($contestantId);
            $seasonId = $contestant?->season_id;
        }

        // If nominee_id is provided, auto resolve week_id if missing
        if ($spotlightNomineeId && !$spotlightWeekId) {
            $nominee = SpotlightWeekNominee::find($spotlightNomineeId);
            $spotlightWeekId = $nominee?->spotlight_week_id;
        }

        $article = WinnerArticle::create([
            'type'                      => $validated['type'],
            'title'                     => $validated['title'],
            'content'                   => $validated['content'],
            'contestant_id'             => $contestantId,
            'spotlight_week_nominee_id' => $spotlightNomineeId,
            'season_id'                 => $seasonId,
            'spotlight_week_id'         => $spotlightWeekId,
            'is_active'                 => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('winner_articles_media', 'public');
                    $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
                    $ext = strtolower($file->getClientOriginalExtension());
                    $isVideo = str_contains($mimeType ?? '', 'video') || in_array($ext, ['mp4', 'mov', 'avi', 'webm']);

                    WinnerArticleMedia::create([
                        'winner_article_id' => $article->id,
                        'file_path'         => $path,
                        'file_name'         => $file->getClientOriginalName(),
                        'mime_type'         => $mimeType,
                        'file_type'         => $isVideo ? 'video' : 'image',
                        'file_size'         => $file->getSize(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Winner article created successfully.',
        ]);
    }

    /**
     * Show single article for modal editing.
     */
    public function show(WinnerArticle $article): JsonResponse
    {
        $article->load(['media']);
        return response()->json([
            'success' => true,
            'data'    => $article,
        ]);
    }

    /**
     * Update existing winner article and attach additional media.
     */
    public function update(Request $request, WinnerArticle $article): JsonResponse
    {
        $validated = $request->validate([
            'type'                       => ['required', 'string', 'in:boss_beginning,spotlight'],
            'title'                      => ['required', 'string', 'max:255'],
            'content'                    => ['required', 'string'],
            'contestant_id'              => ['nullable', 'integer'],
            'spotlight_week_nominee_id'  => ['nullable', 'integer'],
            'season_id'                  => ['nullable', 'integer'],
            'spotlight_week_id'          => ['nullable', 'integer'],
            'is_active'                  => ['sometimes', 'boolean'],
            'media'                      => ['nullable', 'array'],
            'media.*'                    => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        ]);

        $contestantId = $validated['contestant_id'] ?? null;
        $seasonId = $validated['season_id'] ?? null;
        $spotlightNomineeId = $validated['spotlight_week_nominee_id'] ?? null;
        $spotlightWeekId = $validated['spotlight_week_id'] ?? null;

        if ($contestantId && !$seasonId) {
            $contestant = Contestant::find($contestantId);
            $seasonId = $contestant?->season_id;
        }

        if ($spotlightNomineeId && !$spotlightWeekId) {
            $nominee = SpotlightWeekNominee::find($spotlightNomineeId);
            $spotlightWeekId = $nominee?->spotlight_week_id;
        }

        $article->update([
            'type'                      => $validated['type'],
            'title'                     => $validated['title'],
            'content'                   => $validated['content'],
            'contestant_id'             => $contestantId,
            'spotlight_week_nominee_id' => $spotlightNomineeId,
            'season_id'                 => $seasonId,
            'spotlight_week_id'         => $spotlightWeekId,
            'is_active'                 => $request->has('is_active') ? (bool) $request->is_active : false,
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('winner_articles_media', 'public');
                    $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
                    $ext = strtolower($file->getClientOriginalExtension());
                    $isVideo = str_contains($mimeType ?? '', 'video') || in_array($ext, ['mp4', 'mov', 'avi,webm']);

                    WinnerArticleMedia::create([
                        'winner_article_id' => $article->id,
                        'file_path'         => $path,
                        'file_name'         => $file->getClientOriginalName(),
                        'mime_type'         => $mimeType,
                        'file_type'         => $isVideo ? 'video' : 'image',
                        'file_size'         => $file->getSize(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Winner article updated successfully.',
        ]);
    }

    /**
     * Delete winner article and media.
     */
    public function destroy(WinnerArticle $article): JsonResponse
    {
        $article->load('media');

        foreach ($article->media as $m) {
            $cleanPath = preg_replace('#^storage/#', '', $m->file_path);
            Storage::disk('public')->delete($cleanPath);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Winner article deleted successfully.',
        ]);
    }

    /**
     * Delete single media attachment from article.
     */
    public function deleteMedia(WinnerArticle $article, WinnerArticleMedia $media): JsonResponse
    {
        if ($media->winner_article_id !== $article->id) {
            return response()->json(['success' => false, 'message' => 'Media item not found.'], 404);
        }

        $cleanPath = preg_replace('#^storage/#', '', $media->file_path);
        Storage::disk('public')->delete($cleanPath);
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media item deleted successfully.',
        ]);
    }
}
