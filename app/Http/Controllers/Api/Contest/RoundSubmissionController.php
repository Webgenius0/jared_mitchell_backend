<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\Contest\RoundSubmission;
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
     * Submit or update a submission for a contestant in the round.
     *
     * The contestant is resolved automatically from the authenticated user's own
     * records (or the businesses they own). Pass an optional contestant_id to
     * submit for a SPECIFIC one of your businesses when you own several in the
     * same round. Submitting for someone else's contestant is rejected.
     *
     * @bodyParam contestant_id int optional The contestant to submit for (must be your own)
     * @bodyParam title string optional Submission title
     * @bodyParam description string optional Submission description/pitch
     * @bodyParam media_files[] file required One or more video files (mp4, mov, avi, mkv) — max 100MB each
     */
    public function store(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'contestant_id' => 'nullable|integer|exists:contestants,id',
            'title'         => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:10000',
            'media_files'   => 'required|array|min:1|max:10',
            'media_files.*' => 'file|mimes:mp4,mov,avi,mkv|max:102400', // max 100MB per video
        ]);

        $user       = auth('api')->user();
        $contestant = $this->resolveContestantForRound(
            $user,
            $round,
            $validated['contestant_id'] ?? null
        );

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
            contestant:  $contestant,
            round:       $round,
            title:       $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            mediaFiles:  $request->file('media_files'),
        );

        return $this->success('Submission saved successfully.', [
            'submission' => $submission->fresh(),
        ], 201);
    }

    /**
     * POST /api/v1/contest/rounds/{round}/submissions/draft
     *
     * Save a draft submission (not yet submitted).
     *
     * @bodyParam title string Submission title
     * @bodyParam description string Submission description/pitch
     * @bodyParam media_files[] file Array of media files (images, videos, etc.) to upload
     */
    public function saveDraft(Request $request, Round $round): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:10000',
            'media_files' => 'nullable|array|max:10',
            'media_files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,pdf|max:102400',
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
            contestant:  $contestant,
            round:       $round,
            title:       $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            mediaFiles:  $request->hasFile('media_files') ? $request->file('media_files') : null,
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
            'submission' => $submission->load(['contestant.contestable']),
        ]);
    }

    /**
     * GET /api/v1/contest/rounds/{round}/submissions/{submission}
     *
     * Get the details of a specific submission.
     */
    public function show(Round $round, RoundSubmission $submission): JsonResponse
    {
        if ($submission->round_id !== $round->id) {
            return $this->error(null, 'Submission does not belong to this round.', 404);
        }

        return $this->success('Submission retrieved successfully.', [
            'submission' => $submission->load(['contestant.contestable']),
        ]);
    }

    /**
     * POST /api/v1/contest/rounds/{round}/submissions/{submission}
     *
     * Update an existing submission. Only the owner can update.
     * Use POST (not PUT/PATCH) so multipart/form-data file uploads work correctly.
     *
     * @bodyParam title string optional Submission title
     * @bodyParam description string Submission description/pitch
     * @bodyParam media_files[] file New video files to replace existing ones
     */
    public function update(Request $request, Round $round, RoundSubmission $submission): JsonResponse
    {
        if ($submission->round_id !== $round->id) {
            return $this->error(null, 'Submission does not belong to this round.', 404);
        }

        $user = auth('api')->user();

        // The submission's contestant must belong to the caller and still be an
        // active contestant in this round. Checking the submission's OWN
        // contestant works even when the user owns several businesses in the
        // same round — it never picks the wrong one.
        $submissionContestant = $submission->contestant;
        $canUpdate = $submissionContestant
            && $submissionContestant->current_round_id === $round->id
            && $submissionContestant->status === 'active'
            && $this->ownsContestant($user, $submissionContestant);

        if (!$canUpdate) {
            return $this->error(null, 'You are not authorized to update this submission.', 403);
        }

        if (!$round->isSubmissionOpen()) {
            return $this->error(null, 'Submissions are not currently open for this round.', 422);
        }

        $validated = $request->validate([
            'title'         => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:10000',
            'media_files'   => 'nullable|array|max:10',
            'media_files.*' => 'file|mimes:mp4,mov,avi,mkv|max:102400',
        ]);

        $updated = $this->submissionService->updateSubmission(
            submission:  $submission,
            title:       $validated['title'] ?? null,
            description: $validated['description'] ?? null,
            mediaFiles:  $request->hasFile('media_files') ? $request->file('media_files') : null,
        );

        return $this->success('Submission updated successfully.', [
            'submission' => $updated->fresh()->load(['contestant.contestable']),
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
     *
     * When $contestantId is provided, ONLY that contestant is considered, and it
     * must belong to the user (the user themselves, or a business the user owns).
     * Otherwise it searches the user's own contestant records AND the contestant
     * records of businesses the user owns.
     */
    private function resolveContestantForRound($user, Round $round, ?int $contestantId = null): ?Contestant
    {
        $query = Contestant::where('current_round_id', $round->id)->active();

        if ($contestantId) {
            $query->where('id', $contestantId);
        } else {
            // 1. User themselves, or 2. any business the user owns.
            $query->where(function ($q) use ($user) {
                $q->where('contestable_type', $user->getMorphClass())
                    ->where('contestable_id', $user->id)
                    ->orWhere(function ($b) use ($user) {
                        $b->where('contestable_type', 'App\\Models\\Business')
                            ->whereIn('contestable_id', Business::where('user_id', $user->id)->pluck('id'));
                    });
            });
        }

        $contestant = $query->first();

        // Ownership safety check — never allow submitting for someone else's entry.
        if ($contestant && $contestantId && !$this->ownsContestant($user, $contestant)) {
            return null;
        }

        return $contestant;
    }

    /**
     * Whether the given contestant belongs to the user (the user themselves,
     * or one of the businesses the user owns).
     */
    private function ownsContestant($user, Contestant $contestant): bool
    {
        if ($contestant->contestable_type === $user->getMorphClass()) {
            return $contestant->contestable_id === $user->id;
        }

        return $contestant->contestable_type === 'App\\Models\\Business'
            && Business::where('user_id', $user->id)
                ->where('id', $contestant->contestable_id)
                ->exists();
    }
}
