<?php

namespace App\Http\Resources;

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
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'business_category_id' => $this->business_category_id,
            'owner_name'           => $this->owner_name,
            'business_name'        => $this->business_name,
            'slug'                 => $this->slug,
            'year_founded'         => (int) $this->year_founded,
            'website'              => $this->website,
            'city'                 => $this->city,
            'state'                => $this->state,
            'description'          => $this->description,
            'logo'                 => $this->logo,
            'status'               => $this->status,
            'is_featured'          => (bool) $this->is_featured,
            'category'             => new BusinessCategoryResource($this->whenLoaded('category')),
            'user'                 => new UserResource($this->whenLoaded('user')),
            // Interaction counts
            'total_claps'          => (int) ($this->total_claps ?? 0),
            'total_saves'          => (int) ($this->total_saves ?? 0),
            'total_shares'         => (int) ($this->total_shares ?? 0),
            'total_points'         => (int) ($this->total_points ?? 0),
            // Current user's interaction state (when authenticated)
            'is_clapped'           => $user ? $this->interactions()->where('user_id', $user->id)->where('action_type', 'clap')->exists() : false,
            'is_saved'             => $user ? $this->interactions()->where('user_id', $user->id)->where('action_type', 'save')->exists() : false,
            'is_shared'            => $user ? $this->interactions()->where('user_id', $user->id)->where('action_type', 'share')->exists() : false,
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
