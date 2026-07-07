<?php

namespace App\Services\Contest;

use App\Models\Contest\Vote;
use App\Models\Contest\Contestant;
use App\Models\Contest\LeaderboardEntry;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteService
{
    /**
     * Cast or toggle a vote from a user on a votable entity for a given round.
     *
     * @return array{success: bool, message: string, vote: ?Vote, action: string}
     */
    public function castVote(
        User      $user,
        Round     $round,
        string    $votableType,
        int       $votableId,
        string    $voteType = 'upvote',
        ?float    $weight = null,
        ?array    $metadata = null,
    ): array {
        // 1. Validate the round is open for voting
        if (!$round->isVotingOpen()) {
            return [
                'success' => false,
                'message' => 'Voting is not currently open for this round.',
                'vote'    => null,
                'action'  => 'none',
            ];
        }

        // 2. Resolve weight from the round's voting strategy
        $weight = $weight ?? $this->resolveWeight($round, $voteType);

        // 3. Find or toggle the existing vote
        $existing = Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->first();

        if ($existing) {
            // Toggle off (remove vote)
            $existing->delete();

            Log::info('Vote removed', [
                'user_id'       => $user->id,
                'round_id'      => $round->id,
                'votable_type'  => $votableType,
                'votable_id'    => $votableId,
            ]);

            return [
                'success' => true,
                'message' => 'Vote removed successfully.',
                'vote'    => null,
                'action'  => 'removed',
            ];
        }

        // 4. Check per-user vote limit for this round
        $userVoteCount = Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->count();

        $maxVotes = $round->advancement_config['max_votes_per_user'] ?? 10;
        if ($userVoteCount >= $maxVotes) {
            return [
                'success' => false,
                'message' => "You can only cast {$maxVotes} votes per round.",
                'vote'    => null,
                'action'  => 'none',
            ];
        }

        // 5. Cast the vote
        $vote = Vote::create([
            'user_id'       => $user->id,
            'round_id'      => $round->id,
            'votable_type'  => $votableType,
            'votable_id'    => $votableId,
            'vote_type'     => $voteType,
            'weight'        => $weight,
            'metadata'      => $metadata,
        ]);

        Log::info('Vote cast', [
            'user_id'       => $user->id,
            'round_id'      => $round->id,
            'votable_type'  => $votableType,
            'votable_id'    => $votableId,
            'vote_type'     => $voteType,
            'weight'        => $weight,
        ]);

        return [
            'success' => true,
            'message' => 'Vote cast successfully.',
            'vote'    => $vote,
            'action'  => 'cast',
        ];
    }

    /**
     * Count total votes for a given entity across all voting strategies.
     */
    public function countVotes(string $votableType, int $votableId, ?int $roundId = null): array
    {
        $query = Vote::where('votable_type', $votableType)
                     ->where('votable_id', $votableId);

        if ($roundId) {
            $query->where('round_id', $roundId);
        }

        $upvotes   = (clone $query)->where('vote_type', 'upvote')->count();
        $downvotes = (clone $query)->where('vote_type', 'downvote')->count();
        $totalRaw  = (clone $query)->count();
        $totalWeighted = (clone $query)->sum('weight');

        return [
            'upvotes'        => $upvotes,
            'downvotes'      => $downvotes,
            'total_raw'      => $totalRaw,
            'total_weighted' => $totalWeighted,
            'net_score'      => $upvotes - $downvotes,
        ];
    }

    /**
     * Check if a user has already voted for a particular entity this round.
     */
    public function hasVoted(User $user, Round $round, string $votableType, int $votableId): bool
    {
        return Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->exists();
    }

    /**
     * Get all votes cast by a user in a round.
     */
    public function userVotesInRound(User $user, Round $round)
    {
        return Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->with('votable')
            ->get();
    }

    /**
     * Resolve vote weight based on the round's voting strategy.
     */
    private function resolveWeight(Round $round, string $voteType): float
    {
        return match ($round->voting_strategy) {
            'weighted'       => $round->advancement_config['vote_weight'] ?? 1.0,
            'judge_scored'   => 2.0, // Judge votes count double
            'single_elimination' => 1.0,
            default          => 1.0, // popular_vote, admin_pick
        };
    }
}
