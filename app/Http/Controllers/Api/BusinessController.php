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
    ) {
    }

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
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
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
        if ($business->user_id !== auth('api')->id()) {
            return $this->error('You are not authorized to view this business.', 403);
        }

        $business->load(['user.profile', 'category', 'media', 'interactions']);

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
        if ($business->user_id !== auth('api')->id()) {
            return $this->error('You are not authorized to update this business.', 403);
        }

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
        if ($business->user_id !== auth('api')->id()) {
            return $this->error('You are not authorized to delete this business.', 403);
        }

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

    /*
    |--------------------------------------------------------------------------
    | User Interactions (auth required)
    |--------------------------------------------------------------------------
    */

    /**
     * POST /api/v1/businesses/{business}/clap
     *
     * Toggle clap (like/unlike). Clap = 1 point.
     */
    public function toggleClap(Request $request, Business $business): JsonResponse
    {
        $user = auth('api')->user();

        $result = $this->businessService->toggleClap(
            $business,
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        $message = $result['is_clapped']
            ? 'Business liked successfully.'
            : 'Business liked removed successfully.';

        return $this->success($message, $result);
    }

    /**
     * POST /api/v1/businesses/{business}/save
     *
     * Toggle save/unsave. Save = 3 points.
     */
    public function toggleSave(Request $request, Business $business): JsonResponse
    {
        $user = auth('api')->user();

        $result = $this->businessService->toggleSave(
            $business,
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        $message = $result['is_saved']
            ? 'Business loved successfully.'
            : 'Business loved removed successfully.';

        return $this->success($message, $result);
    }

    /**
     * POST /api/v1/businesses/{business}/share
     *
     * Toggle share/unshare. Share = 5 points.
     */
    public function toggleShare(Request $request, Business $business): JsonResponse
    {
        $user = auth('api')->user();

        $result = $this->businessService->toggleShare(
            $business,
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        $message = $result['is_shared']
            ? 'Business fired successfully.'
            : 'Business fired removed successfully.';

        return $this->success($message, $result);
    }

    /**
     * GET /api/v1/businesses/{business}/interactions
     *
     * Get the current user's interaction state for a business.
     */
    public function userInteractions(Business $business): JsonResponse
    {
        $user = auth('api')->user();

        $result = $this->businessService->getUserInteractionState($business, $user->id);

        return $this->success(
            'User interactions retrieved successfully.',
            $result
        );
    }
}
