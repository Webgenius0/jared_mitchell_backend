<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoChannelResource;
use App\Models\VideoChannel;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoChannelApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/video-channels
     *
     * Returns video channels grouped by category or filtered by ?category=xxx
     * All videos strictly follow the admin-defined order (order ASC, id ASC).
     */
    public function index(Request $request): JsonResponse
    {
        $categoryParam = $request->query('category');

        if ($categoryParam && array_key_exists($categoryParam, VideoChannel::CATEGORIES)) {
            $videos = VideoChannel::where('category', $categoryParam)
                ->where('is_active', true)
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return $this->success(
                VideoChannel::CATEGORIES[$categoryParam] . ' videos retrieved successfully.',
                VideoChannelResource::collection($videos)
            );
        }

        // Return all categories with their respective ordered videos
        $allVideos = VideoChannel::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $result = [];
        foreach (VideoChannel::CATEGORIES as $key => $label) {
            $categoryVideos = $allVideos->where('category', $key)->values();
            $result[$key] = [
                'category_key'   => $key,
                'category_label' => $label,
                'videos'         => VideoChannelResource::collection($categoryVideos),
            ];
        }

        return $this->success(
            'Video channel videos retrieved successfully.',
            $result
        );
    }

    /**
     * GET /api/v1/video-channels/{category}
     *
     * Returns ordered videos for a specific category.
     */
    public function getByCategory(string $category): JsonResponse
    {
        if (!array_key_exists($category, VideoChannel::CATEGORIES)) {
            return $this->error(null, 'Invalid video category specified.', 404);
        }

        $videos = VideoChannel::where('category', $category)
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return $this->success(
            VideoChannel::CATEGORIES[$category] . ' videos retrieved successfully.',
            VideoChannelResource::collection($videos)
        );
    }
}
