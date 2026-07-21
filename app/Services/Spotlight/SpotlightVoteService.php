<?php

namespace App\Services\Spotlight;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightVote;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpotlightVoteService
{
    public function __construct(
        protected SpotlightVotePurchaseService $purchaseService,
    ) {}

    /**
     * Cast or toggle a free community vote on a nominee.
     * One vote per user per nominee per week. Voting again removes the vote.
     *
     * @return array{success: bool, message: string, action: string, vote_count: int}
     */
    public function castVote(User $user, SpotlightWeekNominee $nominee): array
    {
        // 1. Check voting is open
        if (! $nominee->week->isVotingOpen()) {
            return [
                'success' => false,
                'message' => 'Voting is not currently open for this week.',
                'action' => 'none',
                'vote_count' => $nominee->free_vote_count,
            ];
        }

        // 2. Check if user already voted (toggle off)
        $existing = SpotlightVote::where('spotlight_week_nominee_id', $nominee->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            DB::transaction(function () use ($existing, $nominee) {
                $existing->delete();
                $nominee->decrementFreeVotes();
            });

            Log::info('SpotlightVoteService: Free vote removed', [
                'user_id'     => $user->id,
                'nominee_id'  => $nominee->id,
                'week_id'     => $nominee->spotlight_week_id,
            ]);

            return [
                'success'    => true,
                'message'    => 'Your vote has been removed.',
                'action'     => 'removed',
                'vote_count' => $nominee->fresh()->free_vote_count,
            ];
        }

        // 3. Cast the vote
        DB::transaction(function () use ($user, $nominee) {
            SpotlightVote::create([
                'spotlight_week_nominee_id' => $nominee->id,
                'user_id'                   => $user->id,
            ]);
            $nominee->incrementFreeVotes();
        });

        Log::info('SpotlightVoteService: Free vote cast', [
            'user_id'    => $user->id,
            'nominee_id' => $nominee->id,
            'week_id'    => $nominee->spotlight_week_id,
        ]);

        return [
            'success'    => true,
            'message'    => 'Vote cast successfully.',
            'action'     => 'cast',
            'vote_count' => $nominee->fresh()->free_vote_count,
        ];
    }

    /**
     * Check whether a user has voted for a specific nominee.
     */
    public function hasVoted(User $user, SpotlightWeekNominee $nominee): bool
    {
        return SpotlightVote::where('spotlight_week_nominee_id', $nominee->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Create a paid vote purchase request (delegates to SpotlightVotePurchaseService).
     *
     * @return array{success: bool, message: string, purchase?: SpotlightVotePurchase}
     */
    public function requestVotePurchase(
        User $user,
        SpotlightWeekNominee $nominee,
        string $packageSlug
    ): array {
        $package = SpotlightVotePackage::findBySlug($packageSlug);

        if (! $package) {
            return [
                'success' => false,
                'message' => "Package '{$packageSlug}' is not available.",
            ];
        }

        return $this->purchaseService->requestPurchase($user, $nominee, $package);
    }

    /**
     * Initiate payment for an approved purchase.
     *
     * @return array
     */
    public function initiatePayment(SpotlightVotePurchase $purchase, User $user): array
    {
        return $this->purchaseService->initiatePayment($purchase, $user);
    }

    /**
     * Admin approves a pending purchase.
     *
     * @return array{success: bool, message: string}
     */
    public function approvePurchase(SpotlightVotePurchase $purchase, User $admin): array
    {
        return $this->purchaseService->approvePurchase($purchase, $admin);
    }

    /**
     * Admin refunds a paid purchase.
     *
     * @return array{success: bool, message: string}
     */
    public function refundPurchase(SpotlightVotePurchase $purchase, User $admin, ?string $notes = null): array
    {
        return $this->purchaseService->refundPurchase($purchase, $admin, $notes);
    }

    /**
     * Get a real-time leaderboard for a week's nominees.
     *
     * Returns nominees ordered by total_vote_count descending (highest first).
     * Includes only essential spotlight fields (resolved name, type, location).
     */
    public function getLeaderboard(int $weekId): \Illuminate\Support\Collection
    {
        return SpotlightWeekNominee::where('spotlight_week_id', $weekId)
            ->with('spotlightable', 'user.profile')
            ->orderByDesc('total_vote_count')
            ->orderByDesc('free_vote_count')
            ->get()
            ->map(function ($nominee, $index) {
                $isArtist = $nominee->spotlightable_type === ArtistSpotlight::class;
                $spotlight = $nominee->spotlightable;

                return [
                    'rank' => $index + 1,
                    'nominee_id' => $nominee->id,
                    'spotlight' => $spotlight ? [
                        'id' => $spotlight->id,
                        'type' => $isArtist ? 'artist' : 'business',
                        'name' => $isArtist
                            ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                            : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                        'city' => $spotlight->city ?? null,
                        'state' => $spotlight->state ?? null,
                        'email' => $spotlight->email ?? null,
                        'status'=> $spotlight->status ?? null,
                    ] : null,
                    'owner' => [
                        'id' => $nominee->user->id,
                        'name' => $nominee->user->profile?->name
                            ?? $spotlight?->email
                            ?? $nominee->user?->email
                            ?? '—',
                    ],
                    'free_votes' => $nominee->free_vote_count,
                    'paid_votes' => $nominee->paid_vote_count,
                    'total_votes' => $nominee->total_vote_count,
                    'paid_votes_cap' => SpotlightWeek::maxPurchasedVotes(),
                    'paid_cap_reached' => $nominee->hasReachedPaidVoteCap(),
                    'is_winner' => $nominee->is_winner,
                ];
            });
    }
}
