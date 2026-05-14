<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtistCategoryResource;
use App\Models\ArtistCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ArtistCategoryController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/artist-categories
     *
     * Returns all active artist categories.
     */
    public function index(): JsonResponse
    {
        $categories = ArtistCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(
            'Artist categories retrieved successfully.',
            ArtistCategoryResource::collection($categories)
        );
    }
}
