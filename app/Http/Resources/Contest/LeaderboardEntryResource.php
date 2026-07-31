<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return [
            'contestant' => [
                'id' => $contestant->id,
                'season_id' => $contestant->season_id,
                'business_id' => $contestable?->id ?? null,
                'display_name' => $contestant->display_name,
                'slug' => $contestant->slug,
                'avatar_url' => $contestant->avatar_url ? asset('storage/' . $contestant->avatar_url) : asset('admin/default/user.jpg'),
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
                    'total_points' => $contestable->total_points ?? null,

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
            'avatar_url' => $this['avatar_url'],
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
}
