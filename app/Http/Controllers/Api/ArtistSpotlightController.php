<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArtistSpotlightRequest;
use App\Http\Requests\UpdateArtistSpotlightRequest;
use App\Http\Resources\ArtistSpotlightResource;
use App\Models\ArtistSpotlight;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArtistSpotlightController extends Controller
{
    use ApiResponse;

    /**
     * List artist spotlights for the authenticated artist user.
     *
     * Returns all non-draft spotlights owned by the currently authenticated
     * user (artist role), ordered by most recently submitted first.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $spotlights = ArtistSpotlight::where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->withCount(['likers', 'bookmarkers', 'shares'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);

        return $this->success('Artist spotlights retrieved successfully.', [
            'spotlights' => ArtistSpotlightResource::collection($spotlights->items()),
            'pagination' => [
                'total'        => $spotlights->total(),
                'per_page'     => $spotlights->perPage(),
                'current_page' => $spotlights->currentPage(),
                'last_page'    => $spotlights->lastPage(),
            ],
        ]);
    }

    /**
     * Show a single artist spotlight by ID.
     *
     * Only returns published (non-draft) spotlights.
     *
     * @param  int  $id
     * @return ArtistSpotlightResource
     */
    public function show($id)
    {
        $spotlight = ArtistSpotlight::withCount(['likers', 'bookmarkers', 'shares'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        return new ArtistSpotlightResource($spotlight);
    }

    /**
     * Store a new artist spotlight submission.
     *
     * Associates the spotlight with the authenticated artist user and sets
     * the status to "submitted" immediately.
     *
     * @param  ArtistSpotlightRequest  $request
     * @return JsonResponse
     */
    public function store(ArtistSpotlightRequest $request): JsonResponse
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

            $spotlight = ArtistSpotlight::create($data);

            DB::commit();

            return $this->success(
                'Artist spotlight submitted successfully.',
                new ArtistSpotlightResource($spotlight),
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist spotlight submission failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to submit artist spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Save a draft artist spotlight (partial submission).
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
            $spotlight = ArtistSpotlight::updateOrCreate(
                ['user_id' => auth()->id(), 'status' => 'draft'],
                $data
            );

            DB::commit();

            return $this->success(
                'Draft saved successfully.',
                new ArtistSpotlightResource($spotlight),
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist spotlight draft save failed: ' . $e->getMessage());

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
        $spotlight = ArtistSpotlight::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->first();

        if (!$spotlight) {
            return $this->notFound('No draft found.');
        }

        return $this->success(
            'Draft retrieved successfully.',
            new ArtistSpotlightResource($spotlight)
        );
    }

    /**
     * Toggle the like status on an artist spotlight.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function toggleLike($id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = ArtistSpotlight::findOrFail($id);

        $exists = $user->likedArtistSpotlights()->where('artist_spotlight_id', $id)->exists();

        if ($exists) {
            $user->likedArtistSpotlights()->detach($id);
            $message = 'Artist spotlight unliked successfully.';
            $liked = false;
        } else {
            $user->likedArtistSpotlights()->attach($id);
            $message = 'Artist spotlight liked successfully.';
            $liked = true;
        }

        return $this->success($message, ['is_liked' => $liked]);
    }

    /**
     * Toggle the bookmark status on an artist spotlight.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function toggleBookmark($id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = ArtistSpotlight::findOrFail($id);

        $exists = $user->bookmarkedArtistSpotlights()->where('artist_spotlight_id', $id)->exists();

        if ($exists) {
            $user->bookmarkedArtistSpotlights()->detach($id);
            $message = 'Artist spotlight removed from bookmarks.';
            $bookmarked = false;
        } else {
            $user->bookmarkedArtistSpotlights()->attach($id);
            $message = 'Artist spotlight bookmarked successfully.';
            $bookmarked = true;
        }

        return $this->success($message, ['is_bookmarked' => $bookmarked]);
    }

    /**
     * Record a share event for an artist spotlight.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function recordShare(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $spotlight = ArtistSpotlight::findOrFail($id);

        $spotlight->shares()->create([
            'user_id' => $user?->id,
            'platform' => $request->platform,
        ]);

        return $this->success('Artist spotlight share recorded successfully.');
    }

    /**
     * Update an existing artist spotlight.
     *
     * Only the owner (authenticated user who created it) can update.
     * Only fields that are sent will be updated; omitted fields keep their current values.
     * Media files are replaced only when a new file is uploaded.
     *
     * @param  UpdateArtistSpotlightRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateArtistSpotlightRequest $request, $id): JsonResponse
    {
        $spotlight = ArtistSpotlight::findOrFail($id);

        // Ensure the authenticated user owns this spotlight
        if ($spotlight->user_id !== auth()->id()) {
            return $this->error(null, 'You are not authorized to update this spotlight.', 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // --- Headshot ---
            if ($request->hasFile('headshot')) {
                FileHandle::fileDelete($spotlight->headshot_path);
                $path = FileHandle::fileUpload($request->file('headshot'), 'artist-spotlights/headshots');
                if ($path) {
                    $data['headshot_path'] = $path;
                }
            }
            unset($data['headshot']);

            // --- Behind-the-scenes photo ---
            if ($request->hasFile('behind_scenes_photo')) {
                FileHandle::fileDelete($spotlight->behind_scenes_photo_path);
                $path = FileHandle::fileUpload($request->file('behind_scenes_photo'), 'artist-spotlights/behind-scenes');
                if ($path) {
                    $data['behind_scenes_photo_path'] = $path;
                }
            }
            unset($data['behind_scenes_photo']);

            // --- Intro video ---
            if ($request->hasFile('intro_video')) {
                FileHandle::fileDelete($spotlight->intro_video_path);
                $path = FileHandle::fileUpload($request->file('intro_video'), 'artist-spotlights/videos');
                if ($path) {
                    $data['intro_video_path'] = $path;
                }
            }
            unset($data['intro_video']);

            // --- Artwork photos (multiple) ---
            if ($request->hasFile('artwork_photos')) {
                if (!empty($spotlight->artwork_photo_paths)) {
                    foreach ($spotlight->artwork_photo_paths as $oldPath) {
                        FileHandle::fileDelete($oldPath);
                    }
                }
                $paths = [];
                foreach ($request->file('artwork_photos') as $photo) {
                    $path = FileHandle::fileUpload($photo, 'artist-spotlights/artworks');
                    if ($path) {
                        $paths[] = $path;
                    }
                }
                $data['artwork_photo_paths'] = $paths;
            }
            unset($data['artwork_photos']);

            // Remove null values so existing DB values are not overwritten with null
            $data = array_filter($data, fn($v) => $v !== null);

            $spotlight->update($data);

            DB::commit();

            return $this->success(
                'Artist spotlight updated successfully.',
                new ArtistSpotlightResource($spotlight->fresh())
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist spotlight update failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update artist spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Soft-delete an artist spotlight and remove all associated media files.
     *
     * Only the owner (authenticated user who created it) can delete.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $spotlight = ArtistSpotlight::findOrFail($id);

        // Ensure the authenticated user owns this spotlight
        if ($spotlight->user_id !== auth()->id()) {
            return $this->error(null, 'You are not authorized to delete this spotlight.', 403);
        }

        try {
            DB::beginTransaction();

            // Delete all associated media files from storage
            FileHandle::fileDelete($spotlight->headshot_path);
            FileHandle::fileDelete($spotlight->behind_scenes_photo_path);
            FileHandle::fileDelete($spotlight->intro_video_path);

            if (!empty($spotlight->artwork_photo_paths)) {
                foreach ($spotlight->artwork_photo_paths as $path) {
                    FileHandle::fileDelete($path);
                }
            }

            $spotlight->delete();

            DB::commit();

            return $this->success('Artist spotlight deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist spotlight deletion failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete artist spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Handle file uploads for the artist spotlight.
     *
     * @param  Request  $request
     * @param  array  $data
     * @return array
     */
    private function handleFileUploads(Request $request, array $data): array
    {
        $basePath = 'artist-spotlights';

        // Headshot photo
        if ($request->hasFile('headshot')) {
            $path = FileHandle::fileUpload($request->file('headshot'), "{$basePath}/headshots");
            if ($path) {
                $data['headshot_path'] = $path;
            }
        }

        // Behind-the-scenes photo
        if ($request->hasFile('behind_scenes_photo')) {
            $path = FileHandle::fileUpload($request->file('behind_scenes_photo'), "{$basePath}/behind-scenes");
            if ($path) {
                $data['behind_scenes_photo_path'] = $path;
            }
        }

        // Intro video
        if ($request->hasFile('intro_video')) {
            $path = FileHandle::fileUpload($request->file('intro_video'), "{$basePath}/videos");
            if ($path) {
                $data['intro_video_path'] = $path;
            }
        }

        // Artwork photos (multiple)
        if ($request->hasFile('artwork_photos')) {
            $paths = [];
            foreach ($request->file('artwork_photos') as $photo) {
                $path = FileHandle::fileUpload($photo, "{$basePath}/artworks");
                if ($path) {
                    $paths[] = $path;
                }
            }
            if (!empty($paths)) {
                $data['artwork_photo_paths'] = $paths;
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
            'full_legal_name',
            'artist_stage_name',
            'email',
            'phone_number',
            'date_of_birth',
            'city',
            'state',
            'instagram_handle',
            'tiktok_handle',
            'facebook_url',
            'youtube_url',
            'website_portfolio_url',
            'artist_category_id',
            'category_other_description',
            'short_bio',
            'full_artist_story',
            'why_spotlighted',
            'community_message',
            'current_goals',
            'talent_manager_contact',
            'agent_contact',
            'press_kit_url',
            'previous_interviews',
            'awards_recognition',
            'preferred_pronouns',
            'preferred_contact_method',
            'interview_availability',
            'current_step',
            'consent_public_release',
            'consent_ownership_declaration',
            'consent_interview_permission',
        ];
    }
}
