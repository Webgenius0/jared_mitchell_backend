<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ContestApplication;
use App\Models\Contest\Season;
use App\Services\Contest\AiReviewService;
use Illuminate\Support\Facades\DB;

class ContestApplicationService
{
    public function __construct(
        protected AiReviewService $aiReviewService,
    ) {}

    /**
     * Get the currently active season.
     */
    public function activeSeason(): ?Season
    {
        return Season::active();
    }

    /**
     * Apply a business to a season.
     *
     * AI review runs synchronously immediately after creation.
     *
     * Returns ['success' => true, 'application' => ContestApplication, 'ai_review' => AiReview|null]
     * or     ['success' => false, 'message' => '...']
     */
    public function apply(Business $business, Season $season): array
    {
        // 1. Business must belong to the authenticated user
        if ($business->user_id !== auth('api')->id()) {
            return ['success' => false, 'message' => 'You can only apply your own business.'];
        }

        // 2. Business must be active
        if ($business->status !== 'active') {
            return ['success' => false, 'message' => 'Only active businesses can apply for a contest.'];
        }

        // 3. Business must not already have an application for this season
        $existingApplication = ContestApplication::where('business_id', $business->id)
            ->where('season_id', $season->id)
            ->first();

        if ($existingApplication) {
            return ['success' => false, 'message' => 'This business has already applied to this season.'];
        }

        // 4. Season must be accepting applications
        if (!$season->canApply()) {
            return ['success' => false, 'message' => 'This season is not currently accepting applications.'];
        }

        // 5. Season must not have reached the contestant cap
        $maxContestants = $season->configuration['max_contestants'] ?? 100;
        $approvedCount = ContestApplication::where('season_id', $season->id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $maxContestants) {
            return ['success' => false, 'message' => "This season has reached the maximum of {$maxContestants} contestants."];
        }

        // 6. Create the application
        $application = DB::transaction(function () use ($business, $season) {
            return ContestApplication::create([
                'business_id' => $business->id,
                'season_id'   => $season->id,
                'status'      => 'pending',
            ]);
        });

        // 7. Run synchronous AI review (skip if AI is not configured)
        $aiReview = null;
        if (app(\App\Services\AiService::class)->isConfigured()) {
            try {
                $aiReview = $this->aiReviewService->review($application);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('AI review failed, flagged for admin', [
                    'application_id' => $application->id,
                    'error'          => $e->getMessage(),
                ]);
                $application->update(['status' => 'needs_review']);
            }
        }

        return [
            'success'     => true,
            'application' => $application->fresh(['business', 'season']),
            'ai_review'   => $aiReview,
        ];
    }

    /**
     * Withdraw a contest application.
     */
    public function withdraw(ContestApplication $application): array
    {
        // Only the business owner can withdraw
        if ($application->business->user_id !== auth('api')->id()) {
            return ['success' => false, 'message' => 'You can only withdraw your own application.'];
        }

        if ($application->status !== 'pending') {
            return ['success' => false, 'message' => 'Only pending applications can be withdrawn.'];
        }

        // Delete the record so the business can re-apply later
        $application->delete();

        return ['success' => true, 'application' => null, 'message' => 'Application withdrawn successfully.'];
    }

    /**
     * List contest applications for the authenticated user.
     */
    public function myApplications()
    {
        return ContestApplication::whereHas('business', function ($query) {
            $query->where('user_id', auth('api')->id());
        })->with(['business', 'season'])->latest()->get();
    }

    /**
     * Show a single contest application.
     */
    public function show(ContestApplication $application): ContestApplication
    {
        return $application->load(['business', 'season', 'approver']);
    }

    /**
     * List all contest applications for a season (admin/management).
     */
    public function listBySession(Season $season)
    {
        return ContestApplication::where('season_id', $season->id)
            ->with(['business.user.profile', 'approver'])
            ->latest()
            ->paginate(15);
    }

    /**
     * Approve a contest application (admin).
     */
    public function approve(ContestApplication $application): array
    {
        // Check the contestant cap before approving
        $maxContestants = $application->season->configuration['max_contestants'] ?? 100;
        $approvedCount = ContestApplication::where('season_id', $application->season_id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $maxContestants) {
            return ['success' => false, 'message' => "This season has reached the maximum of {$maxContestants} contestants."];
        }

        $application->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('api')->id(),
        ]);

        return ['success' => true, 'application' => $application->fresh(['business', 'season', 'approver'])];
    }

    /**
     * Reject a contest application (admin).
     */
    public function reject(ContestApplication $application, ?string $note = null): array
    {
        $application->update([
            'status'     => 'rejected',
            'admin_note' => $note,
        ]);

        return ['success' => true, 'application' => $application->fresh(['business', 'season', 'approver'])];
    }
}
