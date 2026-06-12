<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SpotlightController extends Controller
{
    use ApiResponse;

    /**
     * Get a random artist spotlight and a random business spotlight.
     * Only returns category, title, description, and images.
     *
     * GET /api/v1/spotlight
     */
    public function index(): JsonResponse
    {
        // 1. Fetch random Artist Spotlight (preferring approved/featured status)
        $artist = ArtistSpotlight::with('category')->whereIn('status', ['approved', 'featured'])->inRandomOrder()->first()
            ?? ArtistSpotlight::with('category')->where('status', '!=', 'draft')->inRandomOrder()->first()
            ?? ArtistSpotlight::with('category')->inRandomOrder()->first();

        // 2. Fetch random Business Spotlight (preferring approved/featured status)
        $business = BusinessSpotlight::whereIn('status', ['approved', 'featured'])->inRandomOrder()->first()
            ?? BusinessSpotlight::where('status', '!=', 'draft')->inRandomOrder()->first()
            ?? BusinessSpotlight::inRandomOrder()->first();

        // 3. Format Artist Spotlight Data
        $artistData = null;
        if ($artist) {
            $artistCategory = $artist->category?->name
                ?? $artist->category_other_description
                ?? 'Other';

            $artistTitle = $artist->artist_stage_name ?: $artist->full_legal_name;

            // Collect all non-empty images
            $artistImages = [];
            if ($artist->headshot_path) {
                $artistImages[] = $this->formatImageUrl($artist->headshot_path);
            }
            if ($artist->artwork_photo_paths && is_array($artist->artwork_photo_paths)) {
                foreach ($artist->artwork_photo_paths as $path) {
                    if ($path) {
                        $artistImages[] = $this->formatImageUrl($path);
                    }
                }
            }
            if ($artist->behind_scenes_photo_path) {
                $artistImages[] = $this->formatImageUrl($artist->behind_scenes_photo_path);
            }

            $artistData = [
                'id' => $artist->id,
                'category' => $artistCategory,
                'title' => $artistTitle,
                'description' => $artist->short_bio,
                'images' => $artistImages
            ];
        }
        // 4. Format Business Spotlight Data
        $businessData = null;
        if ($business) {
            $businessCategory = $business->business_category ?? 'Business';
            $businessTitle = $business->business_name;

            // Collect all non-empty images
            $businessImages = [];
            if ($business->portrait_photo_path) {
                $businessImages[] = $this->formatImageUrl($business->portrait_photo_path);
            }
            if ($business->storefront_workspace_photo_path) {
                $businessImages[] = $this->formatImageUrl($business->storefront_workspace_photo_path);
            }
            if ($business->product_service_photo_paths && is_array($business->product_service_photo_paths)) {
                foreach ($business->product_service_photo_paths as $path) {
                    if ($path) {
                        $businessImages[] = $this->formatImageUrl($path);
                    }
                }
            }
            if ($business->team_photo_path) {
                $businessImages[] = $this->formatImageUrl($business->team_photo_path);
            }

            $businessData = [
                'id' => $business->id,
                'category' => $businessCategory,
                'title' => $businessTitle,
                'description' => $business->business_story,
                'images' => $businessImages
            ];
        }

        return $this->success('Spotlight data retrieved successfully.', [
            'artist' => $artistData,
            'business' => $businessData,
        ]);
    }

    /**
     * Format the image path/URL.
     */
    private function formatImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
