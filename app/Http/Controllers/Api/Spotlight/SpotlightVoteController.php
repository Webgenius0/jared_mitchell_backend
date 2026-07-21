<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
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
     */
    public function pricing(): JsonResponse
    {
        return $this->success('Spotlight vote pricing retrieved.', [
            'packages' => SpotlightVotePurchase::PACKAGES,
            'max_paid_votes' => SpotlightWeek::maxPurchasedVotes(),
            'note' => 'Maximum 100 purchased votes per nominee per week.',
        ]);
    }

    /**
     * POST /api/v1/spotlight/nominees/{nominee}/purchase-votes
     *
     * Spotlight owner requests a paid vote package.
     * Pending admin approval before votes are credited.
     *
     * @bodyParam package string required One of: starter, popular, boost, power
     */
    public function purchaseVotes(Request $request, SpotlightWeekNominee $nominee): JsonResponse
    {
        $validated = $request->validate([
            'package' => ['required', 'string', 'in:starter,popular,boost,power'],
        ]);

        $user = auth('api')->user();

        // Only the nominee owner can purchase votes for their spotlight
        if ($nominee->user_id !== $user->id) {
            return $this->forbidden('You can only purchase votes for your own spotlight.');
        }

        $nominee->load('week');

        $result = $this->voteService->requestVotePurchase($user, $nominee, $validated['package']);

        if (! $result['success']) {
            return $this->error(null, $result['message'], 422);
        }

        return $this->success($result['message'], [
            'purchase' => $result['purchase'],
            'package_details' => SpotlightVotePurchase::packageDetails($validated['package']),
        ], 201);
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
            ->with('approver')
            ->latest()
            ->get();

        return $this->success('Purchase history retrieved.', [
            'nominee_id' => $nominee->id,
            'paid_vote_count' => $nominee->paid_vote_count,
            'remaining_slots' => $nominee->remainingPaidVoteSlots(),
            'cap_reached' => $nominee->hasReachedPaidVoteCap(),
            'purchases' => $purchases,
        ]);
    }
}
