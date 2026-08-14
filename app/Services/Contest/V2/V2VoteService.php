<?php

namespace App\Services\Contest\V2;

use App\Models\Contest\Vote;
use App\Models\Contest\Contestant;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class V2VoteService
{
    /**
     * Submit category-based scores for a contestant in a round.
     * $scores = ['innovation' => 8, 'presentation' => 6, ...]
     */
    public function submitScores(
        User $user,
        Round $round,
        Contestant $contestant,
        array $scores,
    ): array {
        if (!$round->isVotingOpen()) {
            return ['success' => false, 'message' => 'Voting is not currently open for this round.'];
        }

        // Contestant must actually be active in THIS round
        if ($contestant->current_round_id !== $round->id || $contestant->status !== 'active') {
            $hint = $contestant->current_round_id
                ? ' Please vote on round ' . ($contestant->currentRound?->round_number ?? $contestant->current_round_id) . ' (round id ' . $contestant->current_round_id . ').'
                : ' This contestant is not currently competing in any round.';

            return ['success' => false, 'message' => 'This contestant is not active in this round.' . $hint];
        }

        // Prevent self-voting (business owner voting for own business)
        $contestable = $contestant->contestable;
        if ($contestable && method_exists($contestable, 'user_id') === false && isset($contestable->user_id)) {
            if ($contestable->user_id === $user->id) {
                return ['success' => false, 'message' => 'You cannot vote for your own business.'];
            }
        }

        // Prevent double voting for the same business in the same round
        $alreadyVoted = Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->exists();

        if ($alreadyVoted) {
            return ['success' => false, 'message' => 'You have already voted for this business in this round.'];
        }

        $categories = $round->advancement_config['categories'] ?? ['innovation', 'presentation', 'impact'];
        $maxScore   = $round->advancement_config['max_score_per_category'] ?? 10;

        if (empty($categories)) {
            $categories = ['innovation', 'presentation', 'impact'];
        }

        // Cap the number of category scores so the leaderboard (which counts vote
        // rows and sums weights) can't be inflated with an unbounded key count.
        $maxCategories = max(count($categories), 10);
        if (count($scores) > $maxCategories) {
            return ['success' => false, 'message' => "Too many categories. A maximum of {$maxCategories} scores can be submitted per vote."];
        }

        // Validate every submitted score value is numeric and within range.
        foreach ($scores as $category => $value) {
            if (!is_numeric($value) || $value < 1 || $value > $maxScore) {
                return ['success' => false, 'message' => "Score for {$category} must be between 1 and {$maxScore}."];
            }
        }

        // The client may send standard category labels (innovation, presentation,
        // impact, quality, growth) that differ from the labels configured on the
        // round. Accept the submitted labels as-is so voting is never blocked by
        // a label mismatch — only the score values are strictly validated above.

        $votes = DB::transaction(function () use ($user, $round, $contestant, $scores) {
            $saved = [];
            foreach ($scores as $category => $value) {
                $saved[] = Vote::updateOrCreate(
                    [
                        'user_id'      => $user->id,
                        'round_id'     => $round->id,
                        'votable_type' => Contestant::class,
                        'votable_id'   => $contestant->id,
                        'category'     => $category,
                    ],
                    [
                        'vote_type' => 'score_1_10',
                        'weight'    => (float) $value,
                    ]
                );
            }
            return $saved;
        });

        Log::info('Category scores submitted', [
            'user_id' => $user->id,
            'round_id' => $round->id,
            'contestant_id' => $contestant->id,
            'scores' => $scores,
        ]);

        return ['success' => true, 'message' => 'Vote submitted successfully.', 'votes' => $votes];
    }

    public function userScoresForContestant(User $user, Round $round, Contestant $contestant): array
    {
        return Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->pluck('weight', 'category')
            ->toArray();
    }

    public function userVotesInRound(User $user, Round $round)
    {
        return Vote::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->with('votable')
            ->get();
    }
}
