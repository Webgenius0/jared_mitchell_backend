<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContestApplicationRequest;
use App\Models\Business;
use App\Models\ContestApplication;
use App\Models\RoundSession;
use App\Services\ContestApplicationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContestApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ContestApplicationService $contestApplicationService
    ) {
    }

    /**
     * GET /api/v1/contest-applications/active-round-session
     *
     * Get the currently active round session.
     */
    public function activeRoundSession(): JsonResponse
    {
        $roundSession = $this->contestApplicationService->activeRoundSession();

        if (!$roundSession) {
            return $this->error(null, 'No active round session found.', 404);
        }

        return $this->success(
            'Active round session retrieved successfully.',
            [
                'id' => $roundSession->id,
                'title' => $roundSession->title,
                'slug' => $roundSession->slug,
                'description' => $roundSession->description,
                'is_active' => $roundSession->is_active,
                'starts_at' => $roundSession->starts_at,
                'ends_at' => $roundSession->ends_at,
            ]
        );
    }

    /**
     * POST /api/v1/contest-applications
     *
     * Apply a business to a round session.
     */
    public function store(StoreContestApplicationRequest $request): JsonResponse
    {
        $business = Business::findOrFail($request->business_id);
        $roundSession = RoundSession::findOrFail($request->round_session_id);

        $result = $this->contestApplicationService->apply($business, $roundSession);

        if (!$result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success(
            'Contest application submitted successfully.',
            [
                'id' => $result['application']->id,
                'business_id' => $result['application']->business_id,
                'round_session_id' => $result['application']->round_session_id,
                'status' => $result['application']->status,
                'created_at' => $result['application']->created_at,
            ],
            201
        );
    }

    /**
     * POST /api/v1/contest-applications/{application}/withdraw
     *
     * Withdraw a contest application.
     */
    public function withdraw(ContestApplication $application): JsonResponse
    {
        $result = $this->contestApplicationService->withdraw($application);

        if (!$result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'] ?? 'Contest application withdrawn successfully.');
    }

    /**
     * GET /api/v1/contest-applications/my
     *
     * List the authenticated user's contest applications.
     */
    public function myApplications(): JsonResponse
    {
        $applications = $this->contestApplicationService->myApplications();

        return $this->success(
            'My contest applications retrieved successfully.',
            ['applications' => $applications]
        );
    }

    /**
     * GET /api/v1/contest-applications/{application}
     *
     * Show a single contest application.
     */
    public function show(ContestApplication $application): JsonResponse
    {
        $application = $this->contestApplicationService->show($application);

        return $this->success(
            'Contest application retrieved successfully.',
            $application
        );
    }

    /**
     * GET /api/v1/contest-applications/session/{roundSession}
     *
     * List all contest applications for a round session (admin).
     */
    public function listBySession(RoundSession $roundSession): JsonResponse
    {
        $applications = $this->contestApplicationService->listBySession($roundSession);

        return $this->success(
            'Contest applications retrieved successfully.',
            [
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                    'last_page' => $applications->lastPage(),
                ],
            ]
        );
    }

    /**
     * PATCH /api/v1/contest-applications/{application}/approve
     *
     * Approve a contest application (admin).
     */
    public function approve(ContestApplication $application): JsonResponse
    {
        $result = $this->contestApplicationService->approve($application);

        if (!$result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success(
            'Contest application approved successfully.',
            [
                'id' => $result['application']->id,
                'status' => $result['application']->status,
                'approved_at' => $result['application']->approved_at,
                'approved_by' => $result['application']->approved_by,
            ]
        );
    }

    /**
     * PATCH /api/v1/contest-applications/{application}/reject
     *
     * Reject a contest application (admin).
     */
    public function reject(Request $request, ContestApplication $application): JsonResponse
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $result = $this->contestApplicationService->reject(
            $application,
            $request->input('admin_note')
        );

        return $this->success(
            'Contest application rejected successfully.',
            [
                'id' => $result['application']->id,
                'status' => $result['application']->status,
                'admin_note' => $result['application']->admin_note,
            ]
        );
    }

    /**
     * GET /api/v1/contest-applications/approved
     *
     * Get approved businesses for a specific round session.
     */
    public function approvedBusinesses(Request $request): JsonResponse
    {
        $request->validate([
            'round_session_id' => 'required|integer',
        ]);

        $roundSessionId = $request->round_session_id;
        $perPage = min((int) $request->input('per_page', 100), 100);

        // Check if round session exists
        $roundSession = RoundSession::find($roundSessionId);

        if (!$roundSession) {
            return $this->error(
                null,
                'Round session not found.',
                404
            );
        }

        // Fetch approved businesses
        $businesses = Business::select('businesses.*')
            ->join('contest_applications', 'businesses.id', '=', 'contest_applications.business_id')
            ->where('contest_applications.round_session_id', $roundSessionId)
            ->where('contest_applications.status', 'approved')
            ->orderByDesc('contest_applications.approved_at')
            ->with(['user.profile', 'category'])
            ->paginate($perPage);

        // No approved businesses found
        if ($businesses->total() === 0) {
            return $this->error(
                null,
                'No approved businesses found for this round session.',
                404
            );
        }

        return $this->success(
            'Approved businesses retrieved successfully.',
            [
                'businesses' => \App\Http\Resources\BusinessResource::collection($businesses),
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
     * GET /api/v1/contest-applications/approved/{id}
     *
     * Get a single approved business for a specific round session.
     */
    public function showApprovedBusiness($id): JsonResponse
    {
        $business = Business::with(['user.profile', 'category'])
            ->find($id);

        if (!$business) {
            return $this->error(
                null,
                'Business not found.',
                404
            );
        }

        return $this->success(
            'Business retrieved successfully.',
            [
                'business' => new \App\Http\Resources\BusinessResource($business),
            ]
        );
    }
}
