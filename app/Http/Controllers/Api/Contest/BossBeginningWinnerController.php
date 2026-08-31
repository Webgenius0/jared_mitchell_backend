<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Contest\RoundSubmission;
use App\Models\Contest\Season;
use App\Models\WinnerArticle;
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
        // Find the most recent season that has a confirmed winner.
        // The winner is set on the contestant (status = 'winner') when the admin
        // confirms it from the Winners page, so we key off that instead of a
        // single season status string — a season can be left in either
        // 'completed' or 'awaiting_final_review' depending on the flow that
        // finalized it.
        $season = Season::whereIn('status', ['completed', 'awaiting_final_review'])
            ->whereHas('contestants', function ($query) {
                $query->where('status', 'winner');
            })
            ->orderByDesc('updated_at')
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
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$winner) {
            return $this->error(null, 'No winner found for the current season.', 404);
        }

        $adminArticles = WinnerArticle::where('type', 'boss_beginning')
            ->where('is_active', true)
            ->with('media')
            ->latest()
            ->get()
            ->map(function ($article) {
                return [
                    'id'         => $article->id,
                    'title'      => $article->title,
                    'content'    => $article->content,
                    'created_at' => $article->created_at?->toIso8601String(),
                    'media'      => $article->media->map(function ($m) {
                        return [
                            'id'        => $m->id,
                            'url'       => $m->url,
                            'file_name' => $m->file_name,
                            'file_type' => $m->file_type,
                            'mime_type' => $m->mime_type,
                            'file_size' => $m->file_size,
                        ];
                    })->values(),
                ];
            })->values();

        return $this->success('Current Boss Beginnings winner retrieved successfully.', [
            'winner'         => $this->formatWinnerData($winner, $season),
            'admin_articles' => $adminArticles,
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

        // Find all winners (contestants with status 'winner') whose season was
        // finalized in the last 6 months. Same as currentWinner — key off the
        // winner contestant, not a single season status string.
        $winners = Contestant::where('status', 'winner')
            ->whereHas('season', function ($query) use ($sixMonthsAgo) {
                $query->whereIn('status', ['completed', 'awaiting_final_review'])
                    ->where(function ($q) use ($sixMonthsAgo) {
                        $q->where('ends_at', '>=', $sixMonthsAgo)
                          ->orWhereNull('ends_at')
                          ->orWhere('updated_at', '>=', $sixMonthsAgo);
                    });
            })
            ->with([
                'contestable',
                'contestable.media',
                'season',
                'currentRound',
            ])
            ->orderByDesc(
                Contestant::select('updated_at')
                    ->from('seasons')
                    ->whereColumn('seasons.id', 'contestants.season_id')
                    ->limit(1)
            )
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
        $showcase = $winner->metadata['showcase'] ?? [];
        $excludedMediaIds = $showcase['excluded_media_ids'] ?? [];

        $hasCustomShowcase = !empty($showcase['title']) || !empty($showcase['description']) || !empty($showcase['media']) || !empty($excludedMediaIds);

        $title = $showcase['title'] ?? $winner->display_name;
        $defaultDescription = $contestable?->story ?? $contestable?->why_they_deserve_to_compete;
        $description = $showcase['description'] ?? $defaultDescription;

        // 1. Business Profile Media
        $businessMedia = [];
        if ($contestable && $contestable->media && $contestable->media->count() > 0) {
            foreach ($contestable->media as $m) {
                $id = 'biz_' . $m->id;
                if (in_array($id, $excludedMediaIds, true)) {
                    continue;
                }
                $filePath = $m->file_path;
                $mimeType = $m->mime_type ?? '';
                $isVectorOrVideo = str_contains($mimeType, 'video') || preg_match('#\.(mp4|mov|avi|webm)$#i', $filePath);
                $businessMedia[] = [
                    'id'        => $id,
                    'file_path' => asset('storage/' . preg_replace('#^storage/#', '', $filePath)),
                    'file_name' => $m->file_name ?? basename($filePath),
                    'mime_type' => $mimeType,
                    'type'      => $isVectorOrVideo ? 'video' : 'image',
                    'source'    => 'Business Profile',
                ];
            }
        }

        // 2. Contest Round Submission Media
        $submissionMedia = [];
        $submissions = RoundSubmission::where('contestant_id', $winner->id)->with('round')->get();
        foreach ($submissions as $sub) {
            if (!empty($sub->media_urls)) {
                foreach ($sub->media_urls as $idx => $urlPath) {
                    $id = 'sub_' . $sub->id . '_' . $idx;
                    if (in_array($id, $excludedMediaIds, true)) {
                        continue;
                    }
                    $cleanPath = preg_replace('#^storage/#', '', $urlPath);
                    $isVectorOrVideo = preg_match('#\.(mp4|mov|avi|webm)$#i', $cleanPath);
                    $roundTitle = $sub->round ? "Round {$sub->round->round_number}" : 'Submission';
                    $submissionMedia[] = [
                        'id'        => $id,
                        'file_path' => asset('storage/' . $cleanPath),
                        'file_name' => basename($cleanPath),
                        'mime_type' => $isVectorOrVideo ? 'video/mp4' : 'image/jpeg',
                        'type'      => $isVectorOrVideo ? 'video' : 'image',
                        'source'    => $roundTitle,
                    ];
                }
            }
        }

        // 3. Admin Custom Uploaded Media
        $customMedia = !empty($showcase['media'])
            ? array_map(function ($m) {
                $filePath = $m['file_path'] ?? '';
                $mimeType = $m['mime_type'] ?? '';
                $isVectorOrVideo = ($m['type'] ?? '') === 'video' || str_contains($mimeType, 'video') || preg_match('#\.(mp4|mov|avi|webm)$#i', $filePath);
                return [
                    'id'        => $m['id'] ?? null,
                    'file_path' => isset($m['file_path']) ? asset('storage/' . preg_replace('#^storage/#', '', $m['file_path'])) : null,
                    'file_name' => $m['file_name'] ?? null,
                    'mime_type' => $m['mime_type'] ?? null,
                    'type'      => $isVectorOrVideo ? 'video' : 'image',
                    'source'    => 'Admin Uploaded',
                ];
            }, $showcase['media'])
            : [];

        $allMedia = array_values(array_merge($businessMedia, $submissionMedia, $customMedia));

        // Resolve avatar URL with fallbacks
        $avatarUrl = null;
        if (!empty($winner->avatar_url)) {
            $avatarUrl = str_starts_with($winner->avatar_url, 'http')
                ? $winner->avatar_url
                : asset('storage/' . preg_replace('#^storage/#', '', $winner->avatar_url));
        }

        if (!$avatarUrl && $contestable) {
            if (isset($contestable->user) && $contestable->user?->profile?->avatar_url) {
                $avatarUrl = $contestable->user->profile->avatar_url;
            } elseif (isset($contestable->owner) && $contestable->owner?->profile?->avatar_url) {
                $avatarUrl = $contestable->owner->profile->avatar_url;
            }
        }

        if (!$avatarUrl && $contestable) {
            if (!empty($contestable->avatar_url)) {
                $avatarUrl = str_starts_with($contestable->avatar_url, 'http')
                    ? $contestable->avatar_url
                    : asset('storage/' . preg_replace('#^storage/#', '', $contestable->avatar_url));
            } elseif (method_exists($contestable, 'getContestantAvatar') && $contestable->getContestantAvatar()) {
                $path = $contestable->getContestantAvatar();
                $avatarUrl = asset('storage/' . preg_replace('#^storage/#', '', $path));
            }
        }

        if (!$avatarUrl && !empty($businessMedia)) {
            $firstImage = array_values(array_filter($businessMedia, fn($m) => ($m['type'] ?? '') === 'image'));
            if (!empty($firstImage)) {
                $avatarUrl = $firstImage[0]['file_path'];
            }
        }

        if (!$avatarUrl) {
            $avatarUrl = asset('admin/default/user.jpg');
        }

        return [
            'id'           => $winner->id,
            'display_name' => $winner->display_name,
            'slug'         => $winner->slug,
            'avatar_url'   => $avatarUrl,
            'status'       => $winner->status,
            'total_score'  => (float) $winner->total_score,
            'title'        => $title,
            'description'  => $description,
            'entered_at'   => $winner->entered_at?->toIso8601String(),
            'created_at'   => $winner->created_at?->toIso8601String(),

            // Admin Custom Showcase Details
            'showcase' => [
                'has_custom_info'    => $hasCustomShowcase,
                'title'              => $title,
                'description'        => $description,
                'custom_media'       => $customMedia,
                'excluded_media_ids' => $excludedMediaIds,
            ],

            // Business / contestable entity details
            'contestable' => $contestable ? [
                'id'                         => $contestable->id,
                'type'                       => get_class($contestable),
                'business_name'              => $contestable->business_name ?? null,
                'owner_founder_name'         => $contestable->owner_founder_name ?? null,
                'slug'                       => $contestable->slug ?? null,
                'story'                      => $description,
                'mission'                    => $contestable->mission ?? null,
                'website_social_media'       => $contestable->website_social_media ?? null,
                'community_impact_statement' => $contestable->community_impact_statement ?? null,
                'revenue_stage'              => $contestable->revenue_stage ?? null,
                'why_they_deserve_to_compete'=> $contestable->why_they_deserve_to_compete ?? null,
                'status'                     => $contestable->status ?? null,
                'total_claps'                => (int) ($contestable->total_claps ?? 0),
                'total_saves'                => (int) ($contestable->total_saves ?? 0),
                'total_shares'               => (int) ($contestable->total_shares ?? 0),
                'total_points'               => (int) ($contestable->total_points ?? 0),
                'media'                      => $allMedia,
            ] : null,

            // Season information
            'season' => [
                'id'           => $season->id,
                'title'        => $season->title,
                'slug'         => $season->slug,
                'contest_type' => $season->contest_type,
                'status'       => $season->status,
                'starts_at'    => $season->starts_at?->toIso8601String(),
                'ends_at'      => $season->ends_at?->toIso8601String(),
                'is_active'    => $season->is_active,
            ],
        ];
    }
}
