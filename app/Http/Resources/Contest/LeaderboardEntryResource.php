<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LeaderboardEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contestant = $this['contestant'];
        $contestable = $contestant->contestable ?? null;

        $video1 = null;
        $video2 = null;
        $video3 = null;
        $video4 = null;

        if ($contestant && $contestant->relationLoaded('submissions')) {
            foreach ($contestant->submissions as $submission) {
                if ($submission->round) {
                    $roundNumber = $submission->round->round_number;
                    // usually $submission->media_full_urls is an array, we get the first one for the video
                    $videoUrl = $submission->media_full_urls[0] ?? null;
                    
                    // Round 2 is video 1, Round 3 is video 2, etc. (since videos start at Round 2)
                    if ($roundNumber == 2) $video1 = $videoUrl;
                    elseif ($roundNumber == 3) $video2 = $videoUrl;
                    elseif ($roundNumber == 4) $video3 = $videoUrl;
                    elseif ($roundNumber == 5) $video4 = $videoUrl;
                }
            }
        }

        return [
            'video_2' => $video1,
            'video_3' => $video2,
            'video_4' => $video3,
            'video_5' => $video4,
            'contestant' => [
                'id' => $contestant->id,
                'season_id' => $contestant->season_id,
                'business_id' => $contestable?->id ?? null,
                'display_name' => $contestant->display_name,
                'slug' => $contestant->slug,
                'avatar_url' => $this->formatAvatar($this['avatar_url'] ?? ($contestable ? $contestable->getContestantAvatar() : null)),
                'status' => $contestant->status,

                // You can uncomment the fields below if you need them:
                // 'total_score' => $contestant->total_score,
                // 'current_round_id' => $contestant->current_round_id,
                // 'entered_at' => $contestant->entered_at,
                // 'created_at' => $contestant->created_at,
                // 'updated_at' => $contestant->updated_at,

                'contestable' => $contestable ? [
                    'id' => $contestable->id,
                    'owner_name' => $contestable->owner_name ?? null,
                    'business_name' => $contestable->business_name ?? null,
                    'slug' => $contestable->slug ?? null,
                    'status' => $contestable->status ?? null,
                    'is_featured' => $contestable->is_featured ?? null,
                    // Round-wise points (computed by LeaderboardService from this
                    // round's clap/save/share interactions) — falls back to the
                    // global counter for safety.
                    'total_points' => (int) ($this['total_points'] ?? $contestable->total_points ?? 0),

                    // You can uncomment the fields below if you need them:
                    // 'story' => $contestable->story ?? null,
                    // 'mission' => $contestable->mission ?? null,
                    // 'website_social_media' => $contestable->website_social_media ?? null,
                    // 'community_impact_statement' => $contestable->community_impact_statement ?? null,
                    // 'revenue_stage' => $contestable->revenue_stage ?? null,
                    // 'why_they_deserve_to_compete' => $contestable->why_they_deserve_to_compete ?? null,
                    'total_claps'  => (int) ($this['claps'] ?? $contestable->total_claps ?? 0),
                    'total_saves'  => (int) ($this['saves'] ?? $contestable->total_saves ?? 0),
                    'total_shares' => (int) ($this['shares'] ?? $contestable->total_shares ?? 0),
                    // 'created_at' => $contestable->created_at ?? null,
                    // 'updated_at' => $contestable->updated_at ?? null,
                ] : null,
            ],

            'contestant_id' => $this['contestant_id'],
            'display_name' => $this['display_name'],
            'avatar_url' => $this->formatAvatar($this['avatar_url'] ?? null),
            'contestable_name' => $this['contestable_name'],
            'total_score' => $this['total_score'],
            'votes_count' => $this['votes_count'],
            'avg_score' => $this['avg_score'] ?? null,
            'claps' => (int) ($this['claps'] ?? $contestable->total_claps ?? 0),
            'shares' => (int) ($this['shares'] ?? $contestable->total_shares ?? 0),
            'saves' => (int) ($this['saves'] ?? $contestable->total_saves ?? 0),
            'trend' => $this['trend'] ?? 'neutral',
            'rank' => $this['rank'],
        ];
    }

    /**
     * Convert a storage path or URL to a public avatar URL.
     *
     * Strips a leading 'storage/' (Storage::disk('public')->url() already adds
     * it), keeps absolute URLs as-is and falls back to the default avatar.
     */
    private function formatAvatar(?string $path): string
    {
        if (!$path) {
            return asset('admin/default/user.jpg');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
    }
}
