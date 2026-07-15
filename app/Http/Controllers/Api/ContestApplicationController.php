<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContestApplicationRequest;
use App\Models\Business;
use App\Models\ContestApplication;
use App\Models\Contest\Season;
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
     * GET /api/v1/active-round-session
     *
     * Get the currently active season.
     */
    public function activeRoundSession(): JsonResponse
    {
        $season = $this->contestApplicationService->activeSeason();

        if (!$season) {
            return $this->error(null, 'No active season found.', 404);
        }

        return $this->success(
            'Active season retrieved successfully.',
            [
                'id' => $season->id,
                'title' => $season->title,
                'slug' => $season->slug,
                'description' => $season->description,
                'contest_type' => $season->contest_type,
                'is_active' => $season->is_active,
                'status' => $season->status,
                'applications_starts_at' => $season->applications_starts_at,
                'applications_ends_at' => $season->applications_ends_at,
                'starts_at' => $season->starts_at,
                'ends_at' => $season->ends_at,
            ]
        );
    }

    /**
     * POST /api/v1/contest-applications
     *
     * Apply a business to a season.
     */
    public function store(StoreContestApplicationRequest $request): JsonResponse
    {
        $business = Business::findOrFail($request->business_id);
        $season = Season::findOrFail($request->season_id);

        $result = $this->contestApplicationService->apply($business, $season);

        if (!$result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success(
            'Contest application submitted successfully.',
            [
                'id' => $result['application']->id,
                'business_id' => $result['application']->business_id,
                'season_id' => $result['application']->season_id,
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
     * GET /api/v1/contest-applications/session/{season}
     *
     * List all contest applications for a season (admin).
     */
    public function listBySession(Season $season): JsonResponse
    {
        $applications = $this->contestApplicationService->listBySession($season);

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
     * Get approved businesses for a specific season.
     */
    public function approvedBusinesses(Request $request): JsonResponse
    {
        $request->validate([
            'season_id' => 'required|integer|exists:seasons,id',
        ]);

        $seasonId = $request->season_id;
        $perPage = min((int) $request->input('per_page', 100), 100);

        // Fetch approved businesses
        $businesses = Business::select('businesses.*')
            ->join('contest_applications', 'businesses.id', '=', 'contest_applications.business_id')
            ->where('contest_applications.season_id', $seasonId)
            ->where('contest_applications.status', 'approved')
            ->orderByDesc('contest_applications.approved_at')
            ->with(['user.profile', 'category'])
            ->paginate($perPage);

        // No approved businesses found
        if ($businesses->total() === 0) {
            return $this->error(
                null,
                'No approved businesses found for this season.',
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
     * Get a single approved business for a specific season.
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

    /**
     * GET /api/v1/contest/my-contests
     *
     * List all contest applications for the authenticated user's businesses.
     * Includes business details, season info, and the rounds for each season.
     */
    public function myContests(): JsonResponse
    {
        $userId = auth('api')->id();

        $applications = ContestApplication::whereHas('business', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with([
                'business',
                'season' => function ($query) {
                    $query->with([
                        'rounds' => function ($query) {
                            $query->orderBy('round_number');
                        }
                    ]);
                },
            ])
            ->latest()
            ->get();

        $contests = $applications->map(function ($application) {
            $season = $application->season;
            $business = $application->business;

            return [
                'id' => $application->id,
                'status' => $application->status,
                'approved_at' => $application->approved_at,
                'rejected_reason' => $application->rejected_reason,
                'admin_note' => $application->admin_note,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,

                'business' => [
                    'id' => $business?->id,
                    'business_name' => $business?->business_name,
                    'owner_founder_name' => $business?->owner_founder_name,
                ],

                'season' => [
                    'id' => $season?->id,
                    'title' => $season?->title,
                    'slug' => $season?->slug,
                    'status' => $season?->status,
                    'is_active' => $season?->is_active,
                    'rounds' => $season?->rounds?->map(function ($round) {
                        return [
                            'id' => $round->id,
                            'round_number' => $round->round_number,
                            'title' => $round->title,
                            'is_active' => $round->is_active,
                        ];
                    }) ?? [],
                ],
            ];
        });

        return $this->success(
            'My contests retrieved successfully.',
            [
                'contests' => $contests,
            ]
        );
    }
}
