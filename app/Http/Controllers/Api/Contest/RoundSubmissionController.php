<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\Round;
use App\Services\Contest\RoundSubmissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundSubmissionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoundSubmissionService $submissionService
    ) {}


    /**
     * POST /api/v1/contest/rounds/{round}/submissions
     *
     * Submit or update a submission for the authenticated user's contestant record.
     *
     * @bodyParam title string required Submission title
     * @bodyParam description string Submission description/pitch
     * @bodyParam media_urls array Array of uploaded file URLs
     */
    public function store(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'media_urls' => 'nullable|array',
            'media_urls.*' => 'nullable|string|max:2048',
        ]);

        $user = auth('api')->user();
        $contestant = $this->resolveContestantForRound($user, $round);

        if (!$contestant) {
            return $this->error(
                null,
                'You are not an active contestant in this round.',
                403
            );
        }

        if (!$round->isSubmissionOpen()) {
            return $this->error(
                null,
                'Submissions are not currently open for this round.',
                422
            );
        }

        $submission = $this->submissionService->submit(
            contestant: $contestant,
            round: $round,
            title: $validated['title'],
            description: $validated['description'] ?? null,
            mediaUrls: $validated['media_urls'] ?? null,
        );

        return $this->success('Submission saved successfully.', [
            'submission' => $submission->fresh(),
        ], 201);
    }

    /**
     * POST /api/v1/contest/rounds/{round}/submissions/draft
     *
     * Save a draft submission (not yet submitted).
     */
    public function saveDraft(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:10000',
            'media_urls'  => 'nullable|array',
            'media_urls.*' => 'nullable|string|max:2048',
        ]);

        $user       = auth('api')->user();
        $contestant = $this->resolveContestantForRound($user, $round);

        if (!$contestant) {
            return $this->error(
                null,
                'You are not an active contestant in this round.',
                403
            );
        }
        $submission = $this->submissionService->saveDraft(
            contestant: $contestant,
            round: $round,
            title: $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            mediaUrls: $validated['media_urls'] ?? null,
        );

        return $this->success('Draft saved successfully.', [
            'submission' => $submission->fresh(),
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/submissions/my
     *
     * Get the authenticated user's current submission for a round.
     */
    public function mySubmission(Round $round): JsonResponse
    {
        $user       = auth('api')->user();
        $contestant = $this->resolveContestantForRound($user, $round);

        if (!$contestant) {
            return $this->error(null, 'You are not a contestant in this round.', 404);
        }
        $submission = $this->submissionService->getSubmission($contestant, $round);

        if (!$submission) {
            return $this->error(null, 'No submission found for this round.', 404);
        }

        return $this->success('Submission retrieved successfully.', [
            'submission' => $submission,
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/submissions
     *
     * Get all submissions for a round (public).
     */
    public function index(Round $round): JsonResponse
    {
        $submissions = $this->submissionService->submissionsForRound($round);

        return $this->success('Submissions retrieved successfully.', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Resolve the current contestant for a user in a given round.
     * Searches through the user's own contestant records AND
     * contestant records of businesses the user owns.
     */
    private function resolveContestantForRound($user, Round $round): ?Contestant
    {
        // 1. Check if the user themselves is a contestant
        $contestant = Contestant::where('current_round_id', $round->id)
            ->where('contestable_type', $user->getMorphClass())
            ->where('contestable_id', $user->id)
            ->active()
            ->first();

        if ($contestant) {
            return $contestant;
        }

        // 2. Check if any of the user's businesses are contestants
        $businessIds = Business::where('user_id', $user->id)->pluck('id');

        if ($businessIds->isNotEmpty()) {
            $contestant = Contestant::where('current_round_id', $round->id)
                ->where('contestable_type', 'App\\Models\\Business')
                ->whereIn('contestable_id', $businessIds)
                ->active()
                ->first();
        }

        return $contestant;
    }
}
