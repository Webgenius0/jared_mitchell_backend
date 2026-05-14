<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->profile->name ?? '',
            'username' => $this->profile->username ?? '',
            'biography' => $this->profile->biography ?? '',
            'tagline' => $this->profile->tagline ?? '',
            'avatar' => $this->profile->avatar_url ?? asset('admin/default/user.jpg'),
            'category' => [
                'id' => $this->artistCategory->id ?? null,
                'name' => $this->artistCategory->name ?? 'Uncategorized',
                'slug' => $this->artistCategory->slug ?? '',
            ],
            // Interaction counts (placeholders for now)
            'likes_count' => 0,
            'bookmarks_count' => 0,
            'shares_count' => 0,
            'is_liked' => false,
            'is_bookmarked' => false,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
