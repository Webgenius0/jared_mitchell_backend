<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Services\BusinessService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BusinessService $businessService
    ) {}

    /**
     * GET /api/v1/businesses
     *
     * List all businesses with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $businesses = $this->businessService->list($request->all());

        return $this->success(
            'Businesses retrieved successfully.',
            [
                'businesses' => BusinessResource::collection($businesses),
                'pagination' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page'     => $businesses->perPage(),
                    'total'        => $businesses->total(),
                    'last_page'    => $businesses->lastPage(),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/businesses/{business}
     *
     * Get a single business by ID.
     */
    public function show(Business $business): JsonResponse
    {
        $business->load(['user.profile', 'category']);

        return $this->success(
            'Business retrieved successfully.',
            new BusinessResource($business)
        );
    }

    /**
     * POST /api/v1/businesses
     *
     * Create a new business.
     */
    public function store(StoreBusinessRequest $request): JsonResponse
    {
        $business = $this->businessService->create($request->validated());

        return $this->success(
            'Business created successfully.',
            new BusinessResource($business),
            201
        );
    }

    /**
     * PUT /api/v1/businesses/{business}
     *
     * Update an existing business.
     */
    public function update(UpdateBusinessRequest $request, Business $business): JsonResponse
    {
        $business = $this->businessService->update($business, $request->validated());

        return $this->success(
            'Business updated successfully.',
            new BusinessResource($business)
        );
    }

    /**
     * DELETE /api/v1/businesses/{business}
     *
     * Soft delete a business.
     */
    public function destroy(Business $business): JsonResponse
    {
        $this->businessService->delete($business);

        return $this->success('Business deleted successfully.');
    }

    /**
     * PATCH /api/v1/businesses/{business}/toggle-status
     *
     * Toggle business status between active and inactive.
     */
    public function toggleStatus(Business $business): JsonResponse
    {
        $business = $this->businessService->toggleStatus($business);

        return $this->success(
            'Business status toggled successfully.',
            new BusinessResource($business)
        );
    }

    /**
     * PATCH /api/v1/businesses/{business}/terminate
     *
     * Terminate a business.
     */
    public function terminate(Business $business): JsonResponse
    {
        $business = $this->businessService->terminate($business);

        return $this->success(
            'Business terminated successfully.',
            new BusinessResource($business)
        );
    }
}
