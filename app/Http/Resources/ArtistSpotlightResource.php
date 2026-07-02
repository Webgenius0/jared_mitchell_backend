<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistSpotlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Identification
            'full_legal_name' => $this->full_legal_name,
            'artist_stage_name' => $this->artist_stage_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'city' => $this->city,
            'state' => $this->state,

            // Social Media
            'social_media' => [
                'instagram_handle' => $this->instagram_handle,
                'tiktok_handle' => $this->tiktok_handle,
                'facebook_url' => $this->facebook_url,
                'youtube_url' => $this->youtube_url,
                'website_portfolio_url' => $this->website_portfolio_url,
            ],

            // Category
            'category' => new ArtistCategoryResource($this->whenLoaded('category')),
            'artist_category_id' => $this->artist_category_id,
            'category_other_description' => $this->category_other_description,

            // Story
            'short_bio' => $this->short_bio,
            'full_artist_story' => $this->full_artist_story,
            'why_spotlighted' => $this->why_spotlighted,
            'community_message' => $this->community_message,
            'current_goals' => $this->current_goals,

            // Media
            'media' => [
                'headshot' => $this->getImageUrl($this->headshot_path),
                'artwork_photos' => $this->getArtworkPhotoUrls(),
                'behind_scenes_photo' => $this->getImageUrl($this->behind_scenes_photo_path),
                'intro_video' => $this->getImageUrl($this->intro_video_path),
            ],

            // Consent
            'consent' => [
                'public_release' => (bool) $this->consent_public_release,
                'ownership_declaration' => (bool) $this->consent_ownership_declaration,
                'interview_permission' => (bool) $this->consent_interview_permission,
            ],

            // Optional Info
            'talent_manager_contact' => $this->talent_manager_contact,
            'agent_contact' => $this->agent_contact,
            'press_kit_url' => $this->press_kit_url,
            'previous_interviews' => $this->previous_interviews,
            'awards_recognition' => $this->awards_recognition,
            'preferred_pronouns' => $this->preferred_pronouns,
            'preferred_contact_method' => $this->preferred_contact_method,
            'interview_availability' => $this->interview_availability,

            // Tracking
            'status' => $this->status,
            'current_step' => (int) $this->current_step,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewer_notes' => $this->reviewer_notes,
            'reviewed_by' => $this->whenLoaded('reviewer', function () {
                return [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Interaction counts
            'likes_count' => (int) ($this->likers_count ?? 0),
            'bookmarks_count' => (int) ($this->bookmarkers_count ?? 0),
            'shares_count' => (int) ($this->shares_count ?? 0),
            'is_liked' => auth('api')->check() ? $this->likers()->where('user_id', auth('api')->id())->exists() : false,
            'is_bookmarked' => auth('api')->check() ? $this->bookmarkers()->where('user_id', auth('api')->id())->exists() : false,
        ];
    }

    /**
     * Get the full URL for a media path.
     */
    private function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // FileHandle::fileUpload() returns a path like 'storage/uploads/...'.
        // asset() converts that to the correct full URL without double-prefixing.
        return asset($path);
    }

    /**
     * Get URLs for all artwork photos.
     */
    private function getArtworkPhotoUrls(): array
    {
        if (!$this->artwork_photo_paths) {
            return [];
        }

        return array_map(
            fn($path) => $this->getImageUrl($path),
            (array) $this->artwork_photo_paths
        );
    }
}
