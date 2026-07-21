<?php

namespace App\Services\Spotlight;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightVote;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpotlightVoteService
{
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
     * Create a paid vote purchase request (pending admin approval).
     *
     * @return array{success: bool, message: string, purchase?: SpotlightVotePurchase}
     */
    public function requestVotePurchase(
        User $user,
        SpotlightWeekNominee $nominee,
        string $package
    ): array {
        // 1. Validate the week is still open
        if (! $nominee->week->isVotingOpen()) {
            return [
                'success' => false,
                'message' => 'Voting is not currently open. You cannot purchase votes.',
            ];
        }

        // 2. Validate the package
        $packageDetails = SpotlightVotePurchase::packageDetails($package);
        if (! $packageDetails) {
            return [
                'success' => false,
                'message' => "Invalid package '{$package}'. Choose: starter, popular, boost, or power.",
            ];
        }

        // 3. Check cap: would adding these votes exceed 100?
        $potentialPendingVotes = SpotlightVotePurchase::where('spotlight_week_nominee_id', $nominee->id)
            ->whereIn('status', ['pending', 'completed'])
            ->sum('votes_count');

        $remainingSlots = SpotlightWeek::maxPurchasedVotes() - $potentialPendingVotes;

        if ($remainingSlots <= 0) {
            return [
                'success' => false,
                'message' => 'Maximum support reached for this nominee. No more votes can be purchased.',
            ];
        }

        if ($packageDetails['votes'] > $remainingSlots) {
            return [
                'success' => false,
                'message' => "This package adds {$packageDetails['votes']} votes but only {$remainingSlots} slots remain (100-vote cap).",
            ];
        }

        // 4. Create the purchase request
        $purchase = SpotlightVotePurchase::create([
            'spotlight_week_nominee_id' => $nominee->id,
            'user_id'                   => $user->id,
            'package'                   => $package,
            'votes_count'               => $packageDetails['votes'],
            'amount_paid'               => $packageDetails['price'],
            'status'                    => 'pending',
        ]);

        Log::info('SpotlightVoteService: Vote purchase requested', [
            'user_id'     => $user->id,
            'nominee_id'  => $nominee->id,
            'package'     => $package,
            'votes_count' => $packageDetails['votes'],
            'amount'      => $packageDetails['price'],
        ]);

        return [
            'success'  => true,
            'message'  => "Purchase request submitted for {$packageDetails['votes']} vote(s) at \${$packageDetails['price']}. Pending admin approval.",
            'purchase' => $purchase,
        ];
    }

    /**
     * Admin approves a vote purchase — credits votes to the nominee.
     *
     * @return array{success: bool, message: string}
     */
    public function approvePurchase(SpotlightVotePurchase $purchase, User $admin): array
    {
        if (! $purchase->isPending()) {
            return [
                'success' => false,
                'message' => "Purchase is already '{$purchase->status}'.",
            ];
        }

        $nominee = $purchase->nominee;

        // Re-check cap before crediting
        if ($nominee->paid_vote_count + $purchase->votes_count > SpotlightWeek::maxPurchasedVotes()) {
            return [
                'success' => false,
                'message' => 'Approving this purchase would exceed the 100-vote cap for this nominee.',
            ];
        }

        DB::transaction(function () use ($purchase, $nominee, $admin) {
            $purchase->update([
                'status'      => 'completed',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $nominee->addPaidVotes($purchase->votes_count);
        });

        Log::info('SpotlightVoteService: Vote purchase approved', [
            'purchase_id' => $purchase->id,
            'nominee_id'  => $nominee->id,
            'votes_added' => $purchase->votes_count,
            'admin_id'    => $admin->id,
        ]);

        return [
            'success' => true,
            'message' => "{$purchase->votes_count} vote(s) credited to the nominee.",
        ];
    }

    /**
     * Admin refunds a vote purchase — removes credited votes.
     *
     * @return array{success: bool, message: string}
     */
    public function refundPurchase(SpotlightVotePurchase $purchase, User $admin, ?string $notes = null): array
    {
        if (! $purchase->isCompleted()) {
            return [
                'success' => false,
                'message' => "Can only refund 'completed' purchases. Current status: {$purchase->status}",
            ];
        }

        $nominee = $purchase->nominee;

        DB::transaction(function () use ($purchase, $nominee, $admin, $notes) {
            $purchase->update([
                'status'      => 'refunded',
                'approved_by' => $admin->id,
                'admin_notes' => $notes,
            ]);

            $nominee->removePaidVotes($purchase->votes_count);
        });

        Log::info('SpotlightVoteService: Vote purchase refunded', [
            'purchase_id'    => $purchase->id,
            'nominee_id'     => $nominee->id,
            'votes_removed'  => $purchase->votes_count,
            'admin_id'       => $admin->id,
        ]);

        return [
            'success' => true,
            'message' => "{$purchase->votes_count} vote(s) removed from nominee (refunded).",
        ];
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
