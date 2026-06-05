<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ContestApplication;
use App\Models\RoundSession;
use Illuminate\Support\Facades\DB;

class ContestApplicationService
{
    /**
     * Apply a business to a round session.
     *
     * Returns ['success' => true, 'application' => ContestApplication]
     * or     ['success' => false, 'message' => '...']
     */
    public function apply(Business $business, RoundSession $roundSession): array
    {
        // 1. Business must belong to the authenticated user
        if ($business->user_id !== auth('api')->id()) {
            return ['success' => false, 'message' => 'You can only apply your own business.'];
        }

        // 2. Business must be active
        if ($business->status !== 'active') {
            return ['success' => false, 'message' => 'Only active businesses can apply for a contest.'];
        }

        // 3. Business must not already have an application (unique constraint on business_id)
        $existingApplication = ContestApplication::where('business_id', $business->id)->first();

        if ($existingApplication) {
            return ['success' => false, 'message' => 'This business has already been applied to a contest.'];
        }

        // 4. Round session must be active
        if (!$roundSession->is_active) {
            return ['success' => false, 'message' => 'This round session is not currently active.'];
        }

        // 5. Round session must not have reached the 100-business cap
        $approvedCount = ContestApplication::where('round_session_id', $roundSession->id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= 100) {
            return ['success' => false, 'message' => 'This round session has reached the maximum of 100 approved businesses.'];
        }

        // 6. Create the application
        $application = DB::transaction(function () use ($business, $roundSession) {
            return ContestApplication::create([
                'business_id'      => $business->id,
                'round_session_id' => $roundSession->id,
                'status'           => 'pending',
            ]);
        });

        return ['success' => true, 'application' => $application];
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
        })->with(['business', 'roundSession'])->latest()->get();
    }

    /**
     * Show a single contest application.
     */
    public function show(ContestApplication $application): ContestApplication
    {
        return $application->load(['business', 'roundSession', 'approver']);
    }

    /**
     * List all contest applications for a round session (admin/management).
     */
    public function listBySession(RoundSession $roundSession)
    {
        return ContestApplication::where('round_session_id', $roundSession->id)
            ->with(['business.user.profile', 'approver'])
            ->latest()
            ->paginate(15);
    }

    /**
     * Approve a contest application (admin).
     */
    public function approve(ContestApplication $application): array
    {
        // Check the 100-business cap before approving
        $approvedCount = ContestApplication::where('round_session_id', $application->round_session_id)
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= 100) {
            return ['success' => false, 'message' => 'This round session has reached the maximum of 100 approved businesses.'];
        }

        $application->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth('api')->id(),
        ]);

        return ['success' => true, 'application' => $application->fresh(['business', 'roundSession', 'approver'])];
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

        return ['success' => true, 'application' => $application->fresh(['business', 'roundSession', 'approver'])];
    }
}
