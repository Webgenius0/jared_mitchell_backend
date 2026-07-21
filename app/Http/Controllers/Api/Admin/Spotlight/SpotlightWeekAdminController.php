<?php

namespace App\Http\Controllers\Api\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Services\Spotlight\SpotlightWeekService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotlightWeekAdminController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SpotlightWeekService $weekService,
    ) {}

    /**
     * GET /api/admin/spotlight/weeks
     *
     * List all spotlight weeks with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SpotlightWeek::withCount(['applications', 'nominees']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $weeks = $query->latest('voting_starts_at')->paginate(20);

        return $this->success('Spotlight weeks retrieved.', $weeks);
    }

    /**
     * POST /api/admin/spotlight/weeks
     *
     * Manually create a new spotlight week.
     *
     * @bodyParam voting_starts_at string required ISO datetime (e.g. "2026-07-27 00:00:00")
     * @bodyParam voting_ends_at   string required ISO datetime (e.g. "2026-08-02 23:59:59")
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'voting_starts_at' => ['required', 'date'],
            'voting_ends_at'   => ['required', 'date', 'after:voting_starts_at'],
        ]);

        $week = $this->weekService->createWeek(
            votingStartsAt:  Carbon::parse($validated['voting_starts_at']),
            votingEndsAt:    Carbon::parse($validated['voting_ends_at']),
        );

        return $this->success('Spotlight week created.', ['week' => $week], 201);
    }

    /**
     * GET /api/admin/spotlight/weeks/{week}
     *
     * Show a spotlight week with applications and nominees.
     */
    public function show(SpotlightWeek $week): JsonResponse
    {
        $week->load([
            'applications.spotlightable',
            'applications.user',
            'nominees.spotlightable',
            'nominees.user',
        ]);

        return $this->success('Spotlight week details retrieved.', ['week' => $week]);
    }

    /**
     * GET /api/admin/spotlight/weeks/{week}/applications
     *
     * List all pending applications for a week (for admin to review and pick Top 12).
     */
    public function applications(SpotlightWeek $week): JsonResponse
    {
        $applications = SpotlightApplication::where('spotlight_week_id', $week->id)
            ->with('spotlightable', 'user')
            ->orderBy('status')
            ->orderBy('applied_at')
            ->paginate(50);

        return $this->success('Applications retrieved.', [
            'week'          => $week->only(['id', 'status', 'week_number', 'year']),
            'applications'  => $applications,
            'pending_count' => SpotlightApplication::where('spotlight_week_id', $week->id)
                ->where('status', 'pending')->count(),
        ]);
    }

    /**
     * POST /api/admin/spotlight/weeks/{week}/select-nominees
     *
     * Admin selects Top 12 nominees from the applications.
     * This opens voting immediately.
     *
     * @bodyParam application_ids array required Array of SpotlightApplication IDs (max 12)
     */
    public function selectNominees(Request $request, SpotlightWeek $week): JsonResponse
    {
        $validated = $request->validate([
            'application_ids'   => ['required', 'array', 'min:1', 'max:12'],
            'application_ids.*' => ['integer', 'exists:spotlight_applications,id'],
        ]);

        $result = $this->weekService->selectNominees($week, $validated['application_ids']);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'nominees' => $result['nominees'],
        ]);
    }

    /**
     * POST /api/admin/spotlight/weeks/{week}/close-voting
     *
     * Manually close voting for a week (admin override, e.g. if scheduler missed it).
     */
    public function closeVoting(SpotlightWeek $week): JsonResponse
    {
        $result = $this->weekService->closeVoting($week);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'winner'      => $result['winner'],
            'leaderboard' => $result['leaderboard'],
        ]);
    }

    /**
     * POST /api/admin/spotlight/weeks/{week}/announce-winner
     *
     * Mark the winner as officially announced.
     * Sets announced_at timestamp (used to show winner badge on homepage, archive, etc.).
     */
    public function announceWinner(SpotlightWeek $week): JsonResponse
    {
        $result = $this->weekService->announceWinner($week);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message']);
    }

    /**
     * PATCH /api/admin/spotlight/weeks/{week}/cancel
     *
     * Cancel a week (no valid nominees, admin discretion, etc.).
     */
    public function cancel(Request $request, SpotlightWeek $week): JsonResponse
    {
        if ($week->status === 'completed') {
            return $this->error(null, 'Cannot cancel a completed week.', 422);
        }

        $week->update(['status' => 'cancelled']);

        return $this->success('Spotlight week cancelled.');
    }
}
