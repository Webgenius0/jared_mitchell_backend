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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BusinessSpotlightController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $spotlights = BusinessSpotlight::where('status', '!=', 'draft')
            ->withCount(['likers', 'bookmarkers', 'shares'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);

        return BusinessSpotlightResource::collection($spotlights);
    }

    public function show($id)
    {
        $spotlight = BusinessSpotlight::withCount(['likers', 'bookmarkers', 'shares'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        return new BusinessSpotlightResource($spotlight);
    }

    /**
     * Store a new business spotlight submission.
     */
    public function store(BusinessSpotlightRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle file uploads
            $data = $this->handleFileUploads($request, $data);

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
     */
    public function saveDraft(Request $request)
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

            // Find existing draft by email or create new
            $spotlight = BusinessSpotlight::updateOrCreate(
                ['email' => $request->email, 'status' => 'draft'],
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
     * Get a draft by email.
     */
    public function getDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $spotlight = BusinessSpotlight::where('email', $request->email)
            ->where('status', 'draft')
            ->first();

        if (!$spotlight) {
            return $this->notFound('No draft found for this email.');
        }

        return $this->success(
            'Draft retrieved successfully.',
            new BusinessSpotlightResource($spotlight)
        );
    }

    /**
     * POST /api/business-spotlight/{id}/like
     */
    public function toggleLike($id): \Illuminate\Http\JsonResponse
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
     * POST /api/business-spotlight/{id}/bookmark
     */
    public function toggleBookmark($id): \Illuminate\Http\JsonResponse
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
     * POST /api/business-spotlight/{id}/share
     */
    public function recordShare(Request $request, $id): \Illuminate\Http\JsonResponse
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
     * POST /api/business-spotlight/{id}/update
     *
     * Full update for an existing business spotlight.
     * Only fields that are sent will be updated; omitted fields keep their current values.
     * Photos are replaced only when a new file is uploaded; otherwise the existing path is kept.
     */
    public function update(UpdateBusinessSpotlightRequest $request, $id)
    {
        $spotlight = BusinessSpotlight::findOrFail($id);

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
     * Handle file uploads for the business spotlight.
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

    public function destroy($id)
    {
        $spotlight = BusinessSpotlight::findOrFail($id);

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
}
