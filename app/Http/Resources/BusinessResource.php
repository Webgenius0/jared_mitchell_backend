<?php

namespace App\Http\Resources;

use App\Models\BusinessInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth('api')->user();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_name' => $this->business_name,
            'slug' => $this->slug,
            'owner_founder_name' => $this->owner_founder_name,
            'story' => $this->story,
            'mission' => $this->mission,
            'website_social_media' => $this->website_social_media,
            'community_impact_statement' => $this->community_impact_statement,
            'revenue_stage' => $this->revenue_stage,
            'why_they_deserve_to_compete' => $this->why_they_deserve_to_compete,
            'media' => BusinessMediaResource::collection($this->whenLoaded('media')),
            'status' => $this->status,
            // 'user' => new UserResource($this->whenLoaded('user')),
            // Interaction counts
            'total_claps'  => (int) ($this->total_claps ?? 0),
            'total_saves'  => (int) ($this->total_saves ?? 0),
            'total_shares' => (int) ($this->total_shares ?? 0),
            'total_points' => (int) ($this->total_points ?? 0),
            // Current user's interaction state (when authenticated)
            'is_clapped' => $user ? BusinessInteraction::where('business_id', $this->id)->where('user_id', $user->id)->where('action_type', 'clap')->exists() : false,
            'is_saved'   => $user ? BusinessInteraction::where('business_id', $this->id)->where('user_id', $user->id)->where('action_type', 'save')->exists() : false,
            'is_shared'  => $user ? BusinessInteraction::where('business_id', $this->id)->where('user_id', $user->id)->where('action_type', 'share')->exists() : false,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
