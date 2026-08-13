<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessSpotlightRequest;
use App\Http\Requests\UpdateBusinessSpotlightRequest;
use App\Http\Resources\BusinessSpotlightResource;
use App\Models\BusinessSpotlight;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BusinessSpotlightController extends Controller
{
    use ApiResponse;

    /**
     * List business spotlights for the authenticated boss user.
     *
     * Returns spotlights owned strictly by the currently authenticated
     * user (boss role), ordered by most recently submitted first.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $query = BusinessSpotlight::where('user_id', $userId);

        if ($request->has('status') && !empty($request->input('status'))) {
            $query->where('status', $request->input('status'));
        } elseif (!$request->boolean('include_draft') && !$request->boolean('all')) {
            $query->where('status', '!=', 'draft');
        }

        $spotlights = $query->withCount(['likers', 'bookmarkers', 'shares'])
            ->orderBy('submitted_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success('Business spotlights retrieved successfully.', [
            'spotlights' => BusinessSpotlightResource::collection($spotlights->items()),
            'pagination' => [
                'total' => $spotlights->total(),
                'per_page' => $spotlights->perPage(),
                'current_page' => $spotlights->currentPage(),
                'last_page' => $spotlights->lastPage(),
            ],
        ]);
    }

    /**
     * Show a single business spotlight by ID.
     *
     * Only returns published (non-draft) spotlights.
     *
     * @param  int  $id
     * @return BusinessSpotlightResource
     */
    public function show($id)
    {
        $spotlight = BusinessSpotlight::withCount(['likers', 'bookmarkers', 'shares'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        return new BusinessSpotlightResource($spotlight);
    }

    /**
     * Store a new business spotlight submission.
     *
     * Associates the spotlight with the authenticated boss user and sets
     * the status to "submitted" immediately.
     *
     * @param  BusinessSpotlightRequest  $request
     * @return JsonResponse
     */
    public function store(BusinessSpotlightRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle file uploads
            $data = $this->handleFileUploads($request, $data);

            // Associate with the authenticated user
            $data['user_id'] = auth()->id();

            // Set submission tracking
            $data['status'] = 'submitted';
            $data['current_step'] = 6;
            $data['submitted_at'] = now();

            $spotlight = BusinessSpotlight::create($data);

            DB::commit();

            return $this->success(
                'Business spotlight submitted successfully.',
                new BusinessSpotlightResource($spotlight),
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business spotlight submission failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to submit business spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Save a draft business spotlight (partial submission).
     *
     * Finds or creates a draft for the authenticated user. Only one
     * active draft per user is allowed.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function saveDraft(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'current_step' => 'required|integer|min:1|max:6',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            DB::beginTransaction();

            $data = $request->only($this->getAllowedFields());
            $data = $this->handleFileUploads($request, $data);
            $data['status'] = 'draft';
            $data['current_step'] = $request->input('current_step', 1);
            $data['user_id'] = auth()->id();

            // Find existing draft for this user or create new
            $spotlight = BusinessSpotlight::updateOrCreate(
                ['user_id' => auth()->id(), 'status' => 'draft'],
                $data
            );

            DB::commit();

            return $this->success(
                'Draft saved successfully.',
                new BusinessSpotlightResource($spotlight),
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business spotlight draft save failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to save draft. Please try again.',
                500
            );
        }
    }

    /**
     * Get the current draft for the authenticated user.
     *
     * Returns the most recent draft owned by the authenticated user,
     * or a 404 response if none exists.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getDraft(Request $request): JsonResponse
    {
        $spotlight = BusinessSpotlight::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->first();

        if (!$spotlight) {
            return $this->notFound('No draft found.');
        }

        return $this->success(
            'Draft retrieved successfully.',
            new BusinessSpotlightResource($spotlight)
        );
    }

    /**
     * Toggle the like status on a business spotlight.
     *
     * If the authenticated user has already liked it, the like is removed.
     * Otherwise a new like is created.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function toggleLike($id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = BusinessSpotlight::findOrFail($id);

        $exists = $user->likedBusinessSpotlights()->where('business_spotlight_id', $id)->exists();

        if ($exists) {
            $user->likedBusinessSpotlights()->detach($id);
            $message = 'Business spotlight unliked successfully.';
            $liked = false;
        } else {
            $user->likedBusinessSpotlights()->attach($id);
            $message = 'Business spotlight liked successfully.';
            $liked = true;
        }

        return $this->success($message, ['is_liked' => $liked]);
    }

    /**
     * Toggle the bookmark status on a business spotlight.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function toggleBookmark($id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = BusinessSpotlight::findOrFail($id);

        $exists = $user->bookmarkedBusinessSpotlights()->where('business_spotlight_id', $id)->exists();

        if ($exists) {
            $user->bookmarkedBusinessSpotlights()->detach($id);
            $message = 'Business spotlight removed from bookmarks.';
            $bookmarked = false;
        } else {
            $user->bookmarkedBusinessSpotlights()->attach($id);
            $message = 'Business spotlight bookmarked successfully.';
            $bookmarked = true;
        }

        return $this->success($message, ['is_bookmarked' => $bookmarked]);
    }

    /**
     * Record a share event for a business spotlight.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function recordShare(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = BusinessSpotlight::findOrFail($id);

        $spotlight->shares()->create([
            'user_id' => $user?->id,
            'platform' => $request->platform,
        ]);

        return $this->success('Business spotlight share recorded successfully.');
    }

    /**
     * Update an existing business spotlight.
     *
     * Only the owner (authenticated user who created it) can update.
     * Only fields that are sent will be updated; omitted fields keep their current values.
     * Photos are replaced only when a new file is uploaded.
     *
     * @param  UpdateBusinessSpotlightRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateBusinessSpotlightRequest $request, $id): JsonResponse
    {
        $spotlight = BusinessSpotlight::findOrFail($id);

        // Ensure the authenticated user owns this spotlight
        if ($spotlight->user_id !== auth()->id()) {
            return $this->error(null, 'You are not authorized to update this spotlight.', 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // --- Portrait photo ---
            if ($request->hasFile('portrait_photo')) {
                FileHandle::fileDelete($spotlight->portrait_photo_path);
                $path = FileHandle::fileUpload($request->file('portrait_photo'), 'business-spotlights/portraits');
                if ($path) {
                    $data['portrait_photo_path'] = $path;
                }
            }
            unset($data['portrait_photo']);

            // --- Storefront / workspace photo ---
            if ($request->hasFile('storefront_workspace_photo')) {
                FileHandle::fileDelete($spotlight->storefront_workspace_photo_path);
                $path = FileHandle::fileUpload($request->file('storefront_workspace_photo'), 'business-spotlights/storefronts');
                if ($path) {
                    $data['storefront_workspace_photo_path'] = $path;
                }
            }
            unset($data['storefront_workspace_photo']);

            // --- Team photo ---
            if ($request->hasFile('team_photo')) {
                FileHandle::fileDelete($spotlight->team_photo_path);
                $path = FileHandle::fileUpload($request->file('team_photo'), 'business-spotlights/teams');
                if ($path) {
                    $data['team_photo_path'] = $path;
                }
            }
            unset($data['team_photo']);

            // --- Product / service photos (multiple) ---
            if ($request->hasFile('product_service_photos')) {
                // Delete old product photos
                if (!empty($spotlight->product_service_photo_paths)) {
                    foreach ($spotlight->product_service_photo_paths as $oldPath) {
                        FileHandle::fileDelete($oldPath);
                    }
                }
                $paths = [];
                foreach ($request->file('product_service_photos') as $photo) {
                    $path = FileHandle::fileUpload($photo, 'business-spotlights/products');
                    if ($path) {
                        $paths[] = $path;
                    }
                }
                $data['product_service_photo_paths'] = $paths;
            }
            unset($data['product_service_photos']);

            // Remove null values so existing DB values are not overwritten with null
            $data = array_filter($data, fn($v) => $v !== null);

            $spotlight->update($data);

            DB::commit();

            return $this->success(
                'Business spotlight updated successfully.',
                new BusinessSpotlightResource($spotlight->fresh())
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business spotlight update failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update business spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Soft-delete a business spotlight and remove all associated media files.
     *
     * Only the owner (authenticated user who created it) can delete.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $spotlight = BusinessSpotlight::findOrFail($id);

        // Ensure the authenticated user owns this spotlight
        if ($spotlight->user_id !== auth()->id()) {
            return $this->error(null, 'You are not authorized to delete this spotlight.', 403);
        }

        try {
            DB::beginTransaction();

            // Delete files
            if ($spotlight->portrait_photo_path) {
                FileHandle::fileDelete($spotlight->portrait_photo_path);
            }
            if ($spotlight->storefront_workspace_photo_path) {
                FileHandle::fileDelete($spotlight->storefront_workspace_photo_path);
            }
            if ($spotlight->team_photo_path) {
                FileHandle::fileDelete($spotlight->team_photo_path);
            }
            if ($spotlight->product_service_photo_paths) {
                foreach ($spotlight->product_service_photo_paths as $path) {
                    FileHandle::fileDelete($path);
                }
            }

            $spotlight->delete();

            DB::commit();

            return $this->success('Business spotlight deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business spotlight deletion failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete business spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Handle file uploads for the business spotlight.
     *
     * @param  Request  $request
     * @param  array  $data
     * @return array
     */
    private function handleFileUploads(Request $request, array $data): array
    {
        $basePath = 'business-spotlights';

        // Portrait photo
        if ($request->hasFile('portrait_photo')) {
            $path = FileHandle::fileUpload($request->file('portrait_photo'), "{$basePath}/portraits");
            if ($path) {
                $data['portrait_photo_path'] = $path;
            }
        }

        // Storefront/workspace photo
        if ($request->hasFile('storefront_workspace_photo')) {
            $path = FileHandle::fileUpload($request->file('storefront_workspace_photo'), "{$basePath}/storefronts");
            if ($path) {
                $data['storefront_workspace_photo_path'] = $path;
            }
        }

        // Team photo
        if ($request->hasFile('team_photo')) {
            $path = FileHandle::fileUpload($request->file('team_photo'), "{$basePath}/teams");
            if ($path) {
                $data['team_photo_path'] = $path;
            }
        }

        // Product/service photos (multiple)
        if ($request->hasFile('product_service_photos')) {
            $paths = [];
            foreach ($request->file('product_service_photos') as $photo) {
                $path = FileHandle::fileUpload($photo, "{$basePath}/products");
                if ($path) {
                    $paths[] = $path;
                }
            }
            if (!empty($paths)) {
                $data['product_service_photo_paths'] = $paths;
            }
        }

        return $data;
    }

    /**
     * Get all allowed fields for mass assignment.
     *
     * @return array
     */
    private function getAllowedFields(): array
    {
        return [
            // Step 1 – Business Information
            'business_name',
            'owner_founder_name',
            'business_category',
            'year_founded',
            'business_website',
            'city',
            'state',

            // Step 2 – Business Story
            'business_story',
            'products_services',
            'challenges_overcome',
            'unique_factor',
            'target_customer',

            // Step 3 – Contact Information
            'email',
            'phone_number',
            'best_contact_time',
            'instagram_url',
            'tiktok_url',
            'facebook_url',
            'youtube_url',
            'google_business_profile_url',
            'linkedin_url',
            'fanbase_url',

            // Step 5 – Service Details
            'service_type',

            // Step 6 – Spotlight Consideration
            'why_featured',
            'growth_vision',
            'permission_feature_on_osi',
            'permission_use_submitted_photos',
            'permission_share_business_story',

            // Tracking
            'current_step',
        ];
    }
}
