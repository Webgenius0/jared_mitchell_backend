<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WinnerArticle;
use App\Models\WinnerArticleMedia;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminWinnerArticleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of winner articles.
     * Filterable by type ('boss_beginning', 'spotlight').
     */
    public function index(Request $request): JsonResponse
    {
        $query = WinnerArticle::with(['media', 'contestant', 'nominee', 'season', 'spotlightWeek']);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $articles = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->success('Winner articles retrieved successfully.', [
            'articles'   => $articles->items(),
            'pagination' => [
                'total'        => $articles->total(),
                'per_page'     => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page'    => $articles->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created winner article with optional multiple image/video uploads.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'                       => ['required', 'string', 'in:boss_beginning,spotlight'],
            'title'                      => ['required', 'string', 'max:255'],
            'content'                    => ['required', 'string'],
            'contestant_id'              => ['nullable', 'integer', 'exists:contestants,id'],
            'spotlight_week_nominee_id'  => ['nullable', 'integer', 'exists:spotlight_week_nominees,id'],
            'season_id'                  => ['nullable', 'integer', 'exists:seasons,id'],
            'spotlight_week_id'          => ['nullable', 'integer', 'exists:spotlight_weeks,id'],
            'is_active'                  => ['sometimes', 'boolean'],
            'media'                      => ['nullable', 'array'],
            'media.*'                    => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        ]);

        $article = WinnerArticle::create([
            'type'                      => $validated['type'],
            'title'                     => $validated['title'],
            'content'                   => $validated['content'],
            'contestant_id'             => $validated['contestant_id'] ?? null,
            'spotlight_week_nominee_id' => $validated['spotlight_week_nominee_id'] ?? null,
            'season_id'                 => $validated['season_id'] ?? null,
            'spotlight_week_id'         => $validated['spotlight_week_id'] ?? null,
            'is_active'                 => $validated['is_active'] ?? true,
        ]);

        if ($request->hasFile('media')) {
            $this->uploadMediaFiles($article, $request->file('media'));
        }

        $article->load(['media', 'contestant', 'nominee', 'season', 'spotlightWeek']);

        return $this->success('Winner article created successfully.', [
            'article' => $article,
        ], 201);
    }

    /**
     * Display the specified winner article.
     */
    public function show(WinnerArticle $article): JsonResponse
    {
        $article->load(['media', 'contestant', 'nominee', 'season', 'spotlightWeek']);

        return $this->success('Winner article retrieved successfully.', [
            'article' => $article,
        ]);
    }

    /**
     * Update the specified winner article.
     */
    public function update(Request $request, WinnerArticle $article): JsonResponse
    {
        $validated = $request->validate([
            'type'                       => ['sometimes', 'string', 'in:boss_beginning,spotlight'],
            'title'                      => ['sometimes', 'string', 'max:255'],
            'content'                    => ['sometimes', 'string'],
            'contestant_id'              => ['nullable', 'integer', 'exists:contestants,id'],
            'spotlight_week_nominee_id'  => ['nullable', 'integer', 'exists:spotlight_week_nominees,id'],
            'season_id'                  => ['nullable', 'integer', 'exists:seasons,id'],
            'spotlight_week_id'          => ['nullable', 'integer', 'exists:spotlight_weeks,id'],
            'is_active'                  => ['sometimes', 'boolean'],
            'media'                      => ['nullable', 'array'],
            'media.*'                    => ['file', 'mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        ]);

        $article->update(array_filter([
            'type'                      => $validated['type'] ?? $article->type,
            'title'                     => $validated['title'] ?? $article->title,
            'content'                   => $validated['content'] ?? $article->content,
            'contestant_id'             => array_key_exists('contestant_id', $validated) ? $validated['contestant_id'] : $article->contestant_id,
            'spotlight_week_nominee_id' => array_key_exists('spotlight_week_nominee_id', $validated) ? $validated['spotlight_week_nominee_id'] : $article->spotlight_week_nominee_id,
            'season_id'                 => array_key_exists('season_id', $validated) ? $validated['season_id'] : $article->season_id,
            'spotlight_week_id'         => array_key_exists('spotlight_week_id', $validated) ? $validated['spotlight_week_id'] : $article->spotlight_week_id,
            'is_active'                 => array_key_exists('is_active', $validated) ? $validated['is_active'] : $article->is_active,
        ], fn ($value) => $value !== null));

        if ($request->hasFile('media')) {
            $this->uploadMediaFiles($article, $request->file('media'));
        }

        $article->load(['media', 'contestant', 'nominee', 'season', 'spotlightWeek']);

        return $this->success('Winner article updated successfully.', [
            'article' => $article,
        ]);
    }

    /**
     * Remove the specified winner article and its media files.
     */
    public function destroy(WinnerArticle $article): JsonResponse
    {
        $article->load('media');

        foreach ($article->media as $m) {
            $cleanPath = preg_replace('#^storage/#', '', $m->file_path);
            Storage::disk('public')->delete($cleanPath);
        }

        $article->delete();

        return $this->success('Winner article deleted successfully.');
    }

    /**
     * Remove a specific media item from an article.
     */
    public function deleteMedia(WinnerArticle $article, WinnerArticleMedia $media): JsonResponse
    {
        if ($media->winner_article_id !== $article->id) {
            return $this->error(null, 'Media item does not belong to this article.', 404);
        }

        $cleanPath = preg_replace('#^storage/#', '', $media->file_path);
        Storage::disk('public')->delete($cleanPath);

        $media->delete();

        return $this->success('Media item deleted successfully.');
    }

    /**
     * Helper to process and upload media files.
     */
    private function uploadMediaFiles(WinnerArticle $article, array $files): void
    {
        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $path = $file->store('winner_articles_media', 'public');
            $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
            $fileExtension = strtolower($file->getClientOriginalExtension());
            $isVideo = str_contains($mimeType ?? '', 'video') || in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm']);

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
