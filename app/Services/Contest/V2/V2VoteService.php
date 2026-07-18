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
        // if ($contestant->current_round_id !== $round->id || $contestant->status !== 'active') {
        //     return ['success' => false, 'message' => 'This contestant is not active in this round.'];
        // }

        // Prevent self-voting (business owner voting for own business)
        $contestable = $contestant->contestable;
        if ($contestable && method_exists($contestable, 'user_id') === false && isset($contestable->user_id)) {
            if ($contestable->user_id === $user->id) {
                return ['success' => false, 'message' => 'You cannot vote for your own entry.'];
            }
        }

        $categories = $round->advancement_config['categories'] ?? [];
        $maxScore   = $round->advancement_config['max_score_per_category'] ?? 10;

        if (empty($categories)) {
            return ['success' => false, 'message' => 'Voting categories are not configured for this round.'];
        }

        // Validate: every submitted key must be a real category, and every category must have a value
        foreach ($scores as $category => $value) {
            if (!in_array($category, $categories, true)) {
                return ['success' => false, 'message' => "Invalid category: {$category}"];
            }
        }
        foreach ($categories as $category) {
            if (!array_key_exists($category, $scores)) {
                return ['success' => false, 'message' => "Missing score for category: {$category}"];
            }
            $val = $scores[$category];
            if (!is_numeric($val) || $val < 1 || $val > $maxScore) {
                return ['success' => false, 'message' => "Score for {$category} must be between 1 and {$maxScore}."];
            }
        }

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
