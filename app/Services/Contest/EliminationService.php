<?php

namespace App\Services\Contest;

use App\Models\Contest\Contestant;
use App\Models\Round;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EliminationService
{
    public function __construct(
        protected LeaderboardService $leaderboardService,
        protected ContestantService  $contestantService,
    ) {}

    /**
     * Process the full transition for an ended round.
     *
     * Steps:
     * 1. Calculate leaderboard for the ending round
     * 2. Determine advancement/elimination based on elimination_rule
     * 3. Apply the results (advance winners, eliminate losers)
     */
    // public function processRoundTransition(Round $round): array
    // {
    //     // 1. Get ranked leaderboard
    //     $leaderboard = $this->leaderboardService->getLeaderboard($round);

    //     $totalCount = count($leaderboard);

    //     // 2. Apply the elimination rule to determine who advances/eliminates
    //     $result = $this->applyEliminationRule($round, $leaderboard);

    //     // 3. Execute the transition in a transaction
    //     DB::transaction(function () use ($round, $result) {
    //         // Mark the current round as inactive
    //         $round->update(['is_active' => false]);

    //         // Find the next round
    //         $nextRound = $this->findNextRound($round);

    //         // Advance contestants to the next round
    //         foreach ($result['advanced'] as $data) {
    //             $contestant = Contestant::find($data['contestant_id']);
    //             if ($contestant && $nextRound) {
    //                 $this->contestantService->advanceToRound($contestant, $nextRound);
    //             }
    //         }

    //         // Eliminate contestants
    //         foreach ($result['eliminated'] as $data) {
    //             $contestant = Contestant::find($data['contestant_id']);
    //             if ($contestant) {
    //                 $this->contestantService->eliminate($contestant, $round);
    //             }
    //         }

    //         // If no next round, finalize the season
    //         if (!$nextRound) {
    //             $this->finalizeSeason($round);
    //         }
    //     });

    //     // 4. Log the results
    //     $nextRound = $this->findNextRound($round);

    //     if ($nextRound) {
    //         Log::info('Round transition completed — contestants advanced', [
    //             'from_round'       => $round->id,
    //             'to_round'         => $nextRound->id,
    //             'advanced'         => count($result['advanced']),
    //             'eliminated'       => count($result['eliminated']),
    //             'elimination_rule' => $round->elimination_rule,
    //         ]);
    //     } else {
    //         Log::info('Season final round completed — no next round', [
    //             'round_id'  => $round->id,
    //             'season_id' => $round->season_id,
    //             'finalists' => count($result['advanced']),
    //         ]);
    //     }

    //     return $result;
    // }


    public function processRoundTransition(Round $round): array
    {
        $leaderboard = $this->leaderboardService->getLeaderboard($round);
        $result = $this->applyEliminationRule($round, $leaderboard);

        DB::transaction(function () use ($round, $result) {
            $round->update(['is_active' => false]);

            $nextRound = $this->findNextRound($round);
            if ($nextRound) {
                $nextRound->update(['is_active' => true]);
            }

            foreach ($result['advanced'] as $data) {
                $contestant = Contestant::find($data['contestant_id']);
                if ($contestant && $nextRound) {
                    $this->contestantService->advanceToRound($contestant, $nextRound);
                }
            }

            foreach ($result['eliminated'] as $data) {
                $contestant = Contestant::find($data['contestant_id']);
                if ($contestant) {
                    $this->contestantService->eliminate($contestant, $round);
                }
            }

            // Guard: only auto-finalize when the rule is NOT admin_pick.
            // Pass the ranked finalists (already sorted best → worst) so the
            // winner is the actual top scorer, not an arbitrary DB order.
            if (!$nextRound && $round->elimination_rule !== 'admin_pick') {
                $this->finalizeSeason($round, $result['advanced']);
            }

            // admin_pick + no next round → mark season as "awaiting admin decision"
            if (!$nextRound && $round->elimination_rule === 'admin_pick') {
                $season = $round->season;
                if ($season && $season->status !== 'completed') {
                    $season->update(['status' => 'awaiting_final_review']);
                }

                Log::info('Final round ended — awaiting admin manual scoring', [
                    'round_id'  => $round->id,
                    'season_id' => $round->season_id,
                ]);
            }
        });

        return $result;
    }

    /**
     * Apply the elimination rule to determine who advances and who is eliminated.
     */
    public function applyEliminationRule(Round $round, array $leaderboard): array
    {
        $rule = $round->elimination_rule ?? 'advance_limit';
        $config = $round->advancement_config ?? [];

        // The admin form saves the limit in the dedicated `advance_limit` column,
        // so fall back to it when the config array does not define one. Without
        // this, the limit is silently ignored and every contestant advances.
        $config['advance_limit'] = $config['advance_limit'] ?? $round->advance_limit;

        return match ($rule) {
            'bottom_n'              => $this->eliminateBottomN($leaderboard, $config),
            'top_percent'           => $this->keepTopPercent($leaderboard, $config),
            'score_below_threshold' => $this->eliminateBelowThreshold($leaderboard, $config),
            'single_elimination'    => $this->singleElimination($leaderboard),
            'admin_pick'            => $this->adminPick($leaderboard),
            'all_advance'           => $this->allAdvance($leaderboard),
            default                 => $this->advanceLimit($leaderboard, $config),
        };
    }

    /**
     * Advance top N contestants, handling ties at the cutoff.
     */
    private function advanceLimit(array $leaderboard, array $config): array
    {
        $advanceLimit = $config['advance_limit'] ?? count($leaderboard);
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'strict';

        return $this->splitAtCutoff($leaderboard, $advanceLimit, $tieBreaker);
    }

    private function eliminateBottomN(array $leaderboard, array $config): array
    {
        $eliminateCount = $config['eliminate_count'] ?? 1;
        $advanceLimit   = max(0, count($leaderboard) - $eliminateCount);

        return $this->splitAtCutoff($leaderboard, $advanceLimit, 'strict');
    }

    private function keepTopPercent(array $leaderboard, array $config): array
    {
        $percent      = max(1, min(100, $config['keep_percent'] ?? 50));
        $advanceLimit = max(1, (int) ceil(count($leaderboard) * $percent / 100));
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'strict';

        return $this->splitAtCutoff($leaderboard, $advanceLimit, $tieBreaker);
    }

    private function eliminateBelowThreshold(array $leaderboard, array $config): array
    {
        $threshold = $config['score_threshold'] ?? 0;
        $advanced  = [];
        $eliminated = [];

        foreach ($leaderboard as $entry) {
            $data = [
                'contestant_id'  => $entry['contestant_id'],
                'display_name'   => $entry['display_name'],
                'rank'           => $entry['rank'],
                'score'          => $entry['total_score'],
            ];

            if ($entry['total_score'] >= $threshold) {
                $advanced[] = $data;
            } else {
                $eliminated[] = $data;
            }
        }

        return compact('advanced', 'eliminated');
    }

    private function singleElimination(array $leaderboard): array
    {
        $advanced  = [];
        $eliminated = [];

        foreach ($leaderboard as $i => $entry) {
            $data = [
                'contestant_id'  => $entry['contestant_id'],
                'display_name'   => $entry['display_name'],
                'rank'           => $entry['rank'],
                'score'          => $entry['total_score'],
            ];

            if ($i === 0) {
                $advanced[] = $data;
            } else {
                $eliminated[] = $data;
            }
        }

        return compact('advanced', 'eliminated');
    }

    private function allAdvance(array $leaderboard): array
    {
        $advanced = array_map(fn($e) => [
            'contestant_id'  => $e['contestant_id'],
            'display_name'   => $e['display_name'],
            'rank'           => $e['rank'],
            'score'          => $e['total_score'],
        ], $leaderboard);

        return ['advanced' => $advanced, 'eliminated' => []];
    }

    private function adminPick(array $leaderboard): array
    {
        // All go into a "pending admin" bucket — no auto-advance or elimination
        return ['advanced' => [], 'eliminated' => []];
    }

    /**
     * Split a ranked leaderboard at a cutoff position.
     *
     * Default behaviour ('strict'): exactly $cutoff contestants advance. When
     * contestants tie at the cutoff score, the tie is filled in leaderboard order
     * (score desc → votes desc → contestant id asc), so a tied group never pushes
     * more people through than the configured limit — e.g. advance_limit = 3 with
     * all-equal scores still advances exactly 3.
     *
     * An explicit 'all_tied_advance' config keeps the legacy behaviour of letting
     * every contestant tied at the cutoff advance together. Any other config value
     * (including the old, buggy 'all_tied_eliminate') behaves like 'strict'.
     */
    private function splitAtCutoff(array $leaderboard, int $cutoff, string $tieBreaker = 'strict'): array
    {
        $advanced   = [];
        $eliminated = [];

        if (empty($leaderboard) || $cutoff <= 0) {
            return ['advanced' => [], 'eliminated' => array_values($leaderboard)];
        }

        $cutoff = min($cutoff, count($leaderboard));
        $cutoffScore = $leaderboard[$cutoff - 1]['total_score'] ?? null;

        if ($cutoffScore === null) {
            return ['advanced' => [], 'eliminated' => array_values($leaderboard)];
        }

        // Legacy option: everyone tied at the cutoff advances together.
        if ($tieBreaker === 'all_tied_advance') {
            foreach ($leaderboard as $entry) {
                $data = [
                    'contestant_id' => $entry['contestant_id'],
                    'display_name'  => $entry['display_name'],
                    'rank'          => $entry['rank'],
                    'score'         => $entry['total_score'],
                ];

                if ($entry['total_score'] >= $cutoffScore) {
                    $advanced[] = $data;
                } else {
                    $eliminated[] = $data;
                }
            }

            return compact('advanced', 'eliminated');
        }

        // Strict deterministic cap — fill up to $cutoff in leaderboard order.
        $scoresAbove = 0;
        foreach ($leaderboard as $entry) {
            if ($entry['total_score'] > $cutoffScore) {
                $scoresAbove++;
            }
        }

        $remaining = $cutoff - $scoresAbove;

        foreach ($leaderboard as $entry) {
            $data = [
                'contestant_id' => $entry['contestant_id'],
                'display_name'  => $entry['display_name'],
                'rank'          => $entry['rank'],
                'score'         => $entry['total_score'],
            ];

            if ($entry['total_score'] > $cutoffScore) {
                $advanced[] = $data;
            } elseif ($remaining > 0) {
                $advanced[] = $data;
                $remaining--;
            } else {
                $eliminated[] = $data;
            }
        }

        return compact('advanced', 'eliminated');
    }

    private function findNextRound(Round $currentRound): ?Round
    {
        // sort_order can be null for rounds created without it (e.g. via the
        // admin Round Sessions form), which would break the query comparison.
        $sortOrder = $currentRound->sort_order ?? 0;

        return Round::where('season_id', $currentRound->season_id)
            ->where(function ($q) use ($currentRound, $sortOrder) {
                $q->where('round_number', '>', $currentRound->round_number)
                    ->orWhere('sort_order', '>', $sortOrder);
            })
            ->orderBy('round_number')
            ->orderBy('sort_order')
            ->first();
    }

    private function finalizeSeason(Round $finalRound, array $rankedFinalists = []): void
    {
        $season = $finalRound->season;

        // Guard: If admin has already confirmed a winner for this season, do not reset it.
        if ($season && $season->status === 'completed' && ($season->metadata['winner_confirmed'] ?? false) === true) {
            Log::info('Season final round already completed and winner confirmed — skipping finalizeSeason reset', [
                'round_id'  => $finalRound->id,
                'season_id' => $season->id,
            ]);
            return;
        }

        // Build ranked finalists from the leaderboard list (already sorted best → worst).
        $ranked = [];
        foreach ($rankedFinalists as $entry) {
            $contestant = Contestant::find($entry['contestant_id'] ?? null);
            if ($contestant) {
                $ranked[] = [
                    'contestant' => $contestant,
                    'score'      => $entry['score'] ?? null,
                ];
            }
        }

        // Fallback: any remaining active contestants not covered by the ranked list,
        // ordered by their stored score so the ranking stays deterministic.
        $already = collect($ranked)->pluck('contestant.id')->all();
        $leftover = Contestant::where('season_id', $finalRound->season_id)
            ->where('status', 'active')
            ->whereNotIn('id', $already)
            ->orderByDesc('total_score')
            ->get();

        foreach ($leftover as $contestant) {
            $ranked[] = [
                'contestant' => $contestant,
                'score'      => $contestant->total_score,
            ];
        }

        // The scheduler does NOT decide the winner. All finalists are kept as
        // 'finalist' (no 'winner' status) and the season waits for the admin to
        // confirm the winner from the top 3 — only then does the winner appear
        // in the public API (which reads status = 'winner').
        $candidates = [];

        DB::transaction(function () use ($ranked, $finalRound, &$candidates) {
            $rank = 1;
            foreach ($ranked as $entry) {
                $contestant = $entry['contestant'];
                $score      = $entry['score'] ?? $contestant->total_score;

                $contestant->update([
                    'status'                 => 'finalist',
                    'eliminated_in_round_id' => null,
                    'total_score'            => $score,
                    'metadata'               => array_merge($contestant->metadata ?? [], [
                        'final_rank'  => $rank,
                        'final_score' => $score,
                    ]),
                ]);

                // Snapshot the top 3 candidates so the Winners page shows a
                // stable leaderboard even if votes are later deleted.
                if ($rank <= 3) {
                    $candidates[] = [
                        'contestant_id' => $contestant->id,
                        'display_name'  => $contestant->display_name,
                        'contestable_id'=> $contestant->contestable_id,
                        'rank'          => $rank,
                        'score'         => $score,
                    ];
                }
                $rank++;
            }
        });

        $season = $finalRound->season;
        $metadata = $season->metadata ?? [];
        $metadata['winner_candidates'] = $candidates;
        $metadata['winner_confirmed']  = false;
        unset(
            $metadata['winner_contestant_id'],
            $metadata['winner_business_id'],
            $metadata['winner_confirmed_at']
        );

        $season->update([
            'metadata'   => $metadata,
            'status'     => 'awaiting_final_review',
            'is_active'  => false,
            'ends_at'    => now(),
        ]);

        Log::info('Season final round completed — awaiting admin winner confirmation', [
            'round_id'   => $finalRound->id,
            'season_id'  => $season->id,
            'finalists'  => count($ranked),
            'candidates' => count($candidates),
        ]);
    }

    /**
     * Find all rounds that have ended and need transitions processed.
     */
    public function findRoundsNeedingTransition(): array
    {
        return Round::ended()
            ->orderBy('season_id')
            ->orderBy('round_number')
            ->get()
            ->all();
    }
}
