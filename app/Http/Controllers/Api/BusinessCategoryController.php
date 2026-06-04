<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessCategoryResource;
use App\Models\BusinessCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessCategoryController extends Controller
{
        use ApiResponse;

    /**
     * GET /api/v1/artist-categories
     *
     * Returns all active artist categories.
     */
    public function index(): JsonResponse
    {
        $categories = BusinessCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(
            'Business categories retrieved successfully.',
            BusinessCategoryResource::collection($categories)
        );
    }
}
