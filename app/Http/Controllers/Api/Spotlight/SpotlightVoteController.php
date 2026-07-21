<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Services\Spotlight\SpotlightVoteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotlightVoteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SpotlightVoteService $voteService,
    ) {}

    /**
     * POST /api/v1/spotlight/nominees/{nominee}/vote
     *
     * Cast or toggle a free community vote on a nominee.
     * One vote per user per nominee per week.
     * Calling again removes the vote (toggle).
     */
    public function vote(Request $request, SpotlightWeekNominee $nominee): JsonResponse
    {
        $user = auth()->user();

        // Eager load week relationship for isVotingOpen() check
        $nominee->load('week');

        $result = $this->voteService->castVote($user, $nominee);

        $status = $result['success'] ? 200 : 422;

        return $result['success']
            ? $this->success($result['message'], [
                'action' => $result['action'],
                'vote_count' => $result['vote_count'],
                'has_voted'  => $result['action'] === 'cast',
            ])
            : $this->error(null, $result['message'], $status);
    }

    /**
     * GET /api/v1/spotlight/nominees/{nominee}/vote/check
     *
     * Check whether the authenticated user has voted for this nominee.
     */
    public function check(SpotlightWeekNominee $nominee): JsonResponse
    {
        $user      = auth('api')->user();
        $hasVoted  = $this->voteService->hasVoted($user, $nominee);

        return $this->success('Vote status retrieved.', [
            'nominee_id' => $nominee->id,
            'has_voted'  => $hasVoted,
        ]);
    }

    /**
     * GET /api/v1/spotlight/votes/pricing
     *
     * Return the paid vote package pricing (public, no auth).
     * Uses the dynamic vote packages from the database.
     */
    public function pricing(): JsonResponse
    {
        $packages = SpotlightVotePackage::active()
            ->ordered()
            ->get()
            ->map(function ($pkg) {
                return [
                    'id'          => $pkg->id,
                    'name'        => $pkg->name,
                    'slug'        => $pkg->slug,
                    'votes_count' => $pkg->votes_count,
                    'price'       => (float) $pkg->price,
                    'label'       => $pkg->label,
                ];
            });

        return $this->success('Spotlight vote pricing retrieved.', [
            'packages'       => $packages,
            'max_paid_votes' => SpotlightWeek::maxPurchasedVotes(),
            'note'           => 'Maximum 100 purchased votes per nominee per week.',
        ]);
    }

    /**
     * POST /api/v1/spotlight/nominees/{nominee}/purchase-votes
     *
     * Spotlight owner requests a paid vote package.
     * Creates a pending purchase request (admin must approve first).
     *
     * @bodyParam package_slug string required Slug of the package (e.g. starter, popular, boost, power)
     */
    public function purchaseVotes(Request $request, SpotlightWeekNominee $nominee): JsonResponse
    {
        $validated = $request->validate([
            'package_slug' => ['required', 'string'],
        ]);

        $user = auth('api')->user();

        // Only the nominee owner can purchase votes for their spotlight
        if ($nominee->user_id !== $user->id) {
            return $this->forbidden('You can only purchase votes for your own spotlight.');
        }

        // Verify the package exists and is active
        $package = SpotlightVotePackage::findBySlug($validated['package_slug']);
        if (! $package) {
            return $this->error(null, "Package '{$validated['package_slug']}' is not available.", 422);
        }

        $nominee->load('week');

        $result = $this->voteService->requestVotePurchase($user, $nominee, $package->slug);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'purchase' => [
                'id' => $result['purchase']->id,
                'status' => $result['purchase']->status,
                'package' => $package->slug,
                'package_name' => $package->name,
                'votes_count' => $result['purchase']->votes_count,
                'amount_paid' => (float) $result['purchase']->amount_paid,
                'created_at' => $result['purchase']->created_at,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/spotlight/purchases/{purchase}/pay
     *
     * Create a Stripe Checkout session for an approved vote purchase.
     * The user must own this purchase and it must be in 'approved' status.
     */
    public function pay(SpotlightVotePurchase $purchase): JsonResponse
    {
        $user = auth('api')->user();

        $result = $this->voteService->initiatePayment($purchase, $user);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'purchase_id' => $purchase->id,
            'checkout_url' => $result['checkout_url'],
            'session_id' => $result['session_id'],
        ]);
    }

    /**
     * GET /api/v1/spotlight/purchases/{purchase}
     *
     * Get details of a specific purchase.
     */
    public function showPurchase(SpotlightVotePurchase $purchase): JsonResponse
    {
        $user = auth('api')->user();

        if ($purchase->user_id !== $user->id) {
            return $this->forbidden('You can only view your own purchases.');
        }

        $purchase->load(['package', 'nominee.spotlightable', 'nominee.week']);

        return $this->success('Purchase details retrieved.', [
            'purchase' => $this->formatPurchase($purchase),
        ]);
    }

    /**
     * GET /api/v1/spotlight/nominees/{nominee}/purchases
     *
     * Get vote purchase history for a nominee (owner only).
     */
    public function myPurchases(SpotlightWeekNominee $nominee): JsonResponse
    {
        $user = auth('api')->user();

        if ($nominee->user_id !== $user->id) {
            return $this->forbidden('You can only view purchases for your own spotlight.');
        }

        $purchases = $nominee->votePurchases()
            ->with(['package', 'approver'])
            ->latest()
            ->get()
            ->map(function ($p) {
                return $this->formatPurchase($p);
            });

        return $this->success('Purchase history retrieved.', [
            'nominee_id'       => $nominee->id,
            'paid_vote_count'  => $nominee->paid_vote_count,
            'remaining_slots'  => $nominee->remainingPaidVoteSlots(),
            'cap_reached'      => $nominee->hasReachedPaidVoteCap(),
            'purchases'        => $purchases,
        ]);
    }

    /**
     * GET /api/v1/spotlight/my-pending-purchases
     *
     * Get all pending purchases for the authenticated user (across all nominees).
     * Useful for showing "you have X purchases awaiting admin approval" on dashboard.
     */
    public function myPendingPurchases(): JsonResponse
    {
        $user = auth('api')->user();

        $purchases = SpotlightVotePurchase::where('user_id', $user->id)
            ->whereIn('status', [
                SpotlightVotePurchase::STATUS_PENDING,
                SpotlightVotePurchase::STATUS_APPROVED,
            ])
            ->with(['package', 'nominee.spotlightable', 'nominee.week'])
            ->latest()
            ->get()
            ->map(function ($p) {
                return $this->formatPurchase($p);
            });

        return $this->success('Pending purchases retrieved.', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Format a purchase for API response.
     */
    private function formatPurchase($purchase): array
    {
        $nominee = $purchase->nominee;
        $spotlightable = $nominee?->spotlightable;
        $isArtist = $spotlightable && \App\Models\ArtistSpotlight::class === $nominee?->spotlightable_type;

        $canPay = $purchase->isPayable();
        $canCancel = in_array($purchase->status, [
            SpotlightVotePurchase::STATUS_PENDING,
            SpotlightVotePurchase::STATUS_APPROVED,
        ]);

        return [
            'id'                    => $purchase->id,
            'status'                => $purchase->status,
            'package_slug'          => $purchase->package,
            'package_name'          => $purchase->package?->name ?? $purchase->package,
            'votes_count'           => $purchase->votes_count,
            'amount_paid'           => (float) $purchase->amount_paid,
            'can_pay'               => $canPay,
            'can_cancel'            => $canCancel,
            'stripe_checkout_url'   => $canPay && $purchase->stripe_checkout_session_id
                ? route('api.spotlight.purchases.pay', $purchase->id)
                : null,
            'nominee' => $nominee ? [
                'id'              => $nominee->id,
                'spotlight_name'  => $spotlightable
                    ? ($isArtist
                        ? ($spotlightable->artist_stage_name ?? $spotlightable->full_legal_name)
                        : ($spotlightable->business_name ?? $spotlightable->owner_founder_name))
                    : '—',
                'spotlight_type'  => $isArtist ? 'artist' : 'business',
                'week_status'     => $nominee->week?->status,
                'voting_open'     => $nominee->week?->isVotingOpen(),
            ] : null,
            'approved_at'  => $purchase->approved_at?->toISOString(),
            'paid_at'      => $purchase->paid_at?->toISOString(),
            'created_at'   => $purchase->created_at?->toISOString(),
        ];
    }
}
