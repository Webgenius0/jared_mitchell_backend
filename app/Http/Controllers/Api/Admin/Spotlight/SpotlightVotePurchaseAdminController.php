<?php

namespace App\Http\Controllers\Api\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Services\Spotlight\SpotlightVoteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotlightVotePurchaseAdminController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SpotlightVoteService $voteService,
    ) {}

    /**
     * GET /api/admin/spotlight/vote-purchases
     *
     * List all vote purchase requests with optional filters.
     *
     * @queryParam status string Filter by status: pending, completed, refunded
     * @queryParam nominee_id int Filter by nominee
     */
    public function index(Request $request): JsonResponse
    {
        $query = SpotlightVotePurchase::with([
            'nominee.week',
            'nominee.spotlightable',
            'user',
            'approver',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nominee_id')) {
            $query->where('spotlight_week_nominee_id', $request->nominee_id);
        }

        if ($request->filled('week_id')) {
            $query->whereHas('nominee', function ($q) use ($request) {
                $q->where('spotlight_week_id', $request->week_id);
            });
        }

        $purchases = $query->latest()->paginate(25);

        return $this->success('Vote purchases retrieved.', $purchases);
    }

    /**
     * GET /api/admin/spotlight/vote-purchases/{purchase}
     *
     * View a single purchase request.
     */
    public function show(SpotlightVotePurchase $purchase): JsonResponse
    {
        $purchase->load(['nominee.week', 'nominee.spotlightable', 'user', 'approver']);

        $nominee = $purchase->nominee;

        return $this->success('Purchase details retrieved.', [
            'purchase'        => $purchase,
            'package_details' => SpotlightVotePurchase::packageDetails($purchase->package),
            'nominee' => [
                'id'               => $nominee->id,
                'paid_vote_count'  => $nominee->paid_vote_count,
                'remaining_slots'  => $nominee->remainingPaidVoteSlots(),
                'cap_reached'      => $nominee->hasReachedPaidVoteCap(),
            ],
        ]);
    }

    /**
     * POST /api/admin/spotlight/vote-purchases/{purchase}/approve
     *
     * Approve a pending vote purchase.
     * Votes are immediately credited to the nominee's paid_vote_count.
     *
     * @bodyParam notes string Optional admin notes
     */
    public function approve(Request $request, SpotlightVotePurchase $purchase): JsonResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $admin  = auth('api')->user();
        $result = $this->voteService->approvePurchase($purchase, $admin);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        if ($request->filled('notes')) {
            $purchase->update(['admin_notes' => $request->notes]);
        }

        return $this->success($result['message'], [
            'purchase'        => $purchase->fresh()->load('nominee'),
            'nominee_votes'   => $purchase->nominee->fresh()->only([
                'free_vote_count',
                'paid_vote_count',
                'total_vote_count',
            ]),
        ]);
    }

    /**
     * POST /api/admin/spotlight/vote-purchases/{purchase}/refund
     *
     * Refund an approved purchase — removes votes from the nominee.
     *
     * @bodyParam notes string Optional reason for refund
     */
    public function refund(Request $request, SpotlightVotePurchase $purchase): JsonResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $admin  = auth('api')->user();
        $result = $this->voteService->refundPurchase($purchase, $admin, $request->notes);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'purchase'       => $purchase->fresh()->load('nominee'),
            'nominee_votes'  => $purchase->nominee->fresh()->only([
                'free_vote_count',
                'paid_vote_count',
                'total_vote_count',
            ]),
        ]);
    }

    /**
     * GET /api/admin/spotlight/vote-purchases/pending-count
     *
     * Quick count of pending purchases (for admin dashboard badge).
     */
    public function pendingCount(): JsonResponse
    {
        $count = SpotlightVotePurchase::where('status', 'pending')->count();

        return $this->success('Pending purchase count retrieved.', [
            'pending_count' => $count,
        ]);
    }
}
