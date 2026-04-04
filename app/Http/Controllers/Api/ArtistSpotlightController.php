<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArtistSpotlightRequest;
use App\Http\Resources\ArtistSpotlightResource;
use App\Models\ArtistSpotlight;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArtistSpotlightController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $spotlights = ArtistSpotlight::where('status', '!=', 'draft')
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);

        return ArtistSpotlightResource::collection($spotlights);
    }

    public function show($id)
    {
        $spotlight = ArtistSpotlight::where('status', '!=', 'draft')
            ->findOrFail($id);

        return new ArtistSpotlightResource($spotlight);
    }   

    /**
     * Store a new artist spotlight submission.
     */
    public function store(ArtistSpotlightRequest $request)
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

            // Only allow fields from the model
            $data = $request->only($this->getAllowedFields());
            $data = $this->handleFileUploads($request, $data);
            $data['status'] = 'draft';
            $data['current_step'] = $request->input('current_step', 1);

            // Find existing draft by email or create new
            $spotlight = ArtistSpotlight::updateOrCreate(
                ['email' => $request->email, 'status' => 'draft'],
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

        $spotlight = ArtistSpotlight::where('email', $request->email)
            ->where('status', 'draft')
            ->first();

        if (!$spotlight) {
            return $this->notFound('No draft found for this email.');
        }

        return $this->success(
            'Draft retrieved successfully.',
            new ArtistSpotlightResource($spotlight)
        );
    }

    /**
     * Handle file uploads for the artist spotlight.
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
