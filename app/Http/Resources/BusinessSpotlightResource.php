<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BusinessSpotlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Step 1 – Business Information
            'business_name' => $this->business_name,
            'owner_founder_name' => $this->owner_founder_name,
            'business_category' => $this->business_category,
            'year_founded' => $this->year_founded,
            'business_website' => $this->business_website,
            'city' => $this->city,
            'state' => $this->state,

            // Step 2 – Business Story
            'business_story' => $this->business_story,
            'products_services' => $this->products_services,
            'challenges_overcome' => $this->challenges_overcome,
            'unique_factor' => $this->unique_factor,
            'target_customer' => $this->target_customer,

            // Step 3 – Contact Information
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'best_contact_time' => $this->best_contact_time,

            // Social Media Links
            'social_media' => [
                'instagram_url' => $this->instagram_url,
                'tiktok_url' => $this->tiktok_url,
                'facebook_url' => $this->facebook_url,
                'youtube_url' => $this->youtube_url,
                'google_business_profile_url' => $this->google_business_profile_url,
                'linkedin_url' => $this->linkedin_url,
                'fanbase_url' => $this->fanbase_url,
            ],

            // Step 4 – Images (with full URLs)
            'images' => [
                'portrait_photo' => $this->getImageUrl($this->portrait_photo_path),
                'storefront_workspace_photo' => $this->getImageUrl($this->storefront_workspace_photo_path),
                'product_service_photos' => $this->getProductServicePhotoUrls(),
                'team_photo' => $this->getImageUrl($this->team_photo_path),
            ],

            // Step 5 – Service Details
            'service_type' => $this->service_type,
            'service_type_label' => $this->getServiceTypeLabel(),

            // Step 6 – Spotlight Consideration
            'why_featured' => $this->why_featured,
            'growth_vision' => $this->growth_vision,
            'permissions' => [
                'feature_on_osi' => (bool) $this->permission_feature_on_osi,
                'use_submitted_photos' => (bool) $this->permission_use_submitted_photos,
                'share_business_story' => (bool) $this->permission_share_business_story,
            ],

            // Submission tracking
            'status' => $this->status,
            'current_step' => $this->current_step,
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
     * Get the full URL for an image path.
     *
     * Strips the 'storage/' prefix that FileHandle adds so it is not
     * duplicated when Storage::disk('public')->url() prepends it.
     */
    private function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
    }

    /**
     * Get URLs for all product/service photos.
     */
    private function getProductServicePhotoUrls(): array
    {
        if (!$this->product_service_photo_paths) {
            return [];
        }

        return array_map(
            fn($path) => $this->getImageUrl($path),
            $this->product_service_photo_paths
        );
    }

    /**
     * Get human-readable service type label.
     */
    private function getServiceTypeLabel(): string
    {
        return match ($this->service_type) {
            'in_person_only' => 'In-Person Only',
            'online_only' => 'Online Only',
            'both_in_person_and_online' => 'Both In-Person and Online',
            default => 'Unknown',
        };
    }
}
