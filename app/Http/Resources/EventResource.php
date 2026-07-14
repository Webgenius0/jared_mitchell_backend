<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'venue_name' => $this->venue_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'hosted_by' => $this->hosted_by,
            'cover_image_url' => $this->cover_image_path ? asset('/' . $this->cover_image_path) : null,
            'promo_video_url' => $this->promo_video_path ? asset('/' . $this->promo_video_path) : null,
            'event_type' => $this->event_type,
            'is_featured' => (bool) $this->is_featured,
            'like_count' => (int) $this->like_count,
            'ticket_url' => $this->ticket_url,
            'tickets_available' => (bool) $this->tickets_available,
            'status' => $this->status,
            'ticket_tiers' => $this->whenLoaded('ticketTiers'),
            'event_media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'event_id' => $media->event_id,
                        'full_url' => asset($media->file_path),
                        'created_at' => $media->created_at,
                    ];
                });
            }),
            'event_artists' => $this->whenLoaded('artists', function () {
                return $this->artists->map(function ($artist) {
                    return [
                        'id' => $artist->id,
                        'name' => $artist->profile->name ?? '',
                        'photo' => $artist->profile->avatar ? asset('/' . $artist->profile->avatar) : asset('admin/default/user.jpg'),
                        'designation' => $artist->profile->tagline ?? $artist->artistCategory?->name ?? 'Artist',
                    ];
                });
            }),
            // Interaction counts
            'likes_count' => (int) ($this->likers_count ?? 0),
            'bookmarks_count' => (int) ($this->bookmarkers_count ?? 0),
            'shares_count' => (int) ($this->shares_count ?? 0),
            'is_liked' => auth('api')->check() ? $this->likers()->where('user_id', auth('api')->id())->exists() : false,
            'is_bookmarked' => auth('api')->check() ? $this->bookmarkers()->where('user_id', auth('api')->id())->exists() : false,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
