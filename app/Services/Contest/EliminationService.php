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

            // Guard: only auto-finalize when the rule is NOT admin_pick
            if (!$nextRound && $round->elimination_rule !== 'admin_pick') {
                $this->finalizeSeason($round);
            }

            // admin_pick + no next round → mark season as "awaiting admin decision"
            if (!$nextRound && $round->elimination_rule === 'admin_pick') {
                $round->season()->update(['status' => 'awaiting_final_review']);

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
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'all_tied_advance';

        return $this->splitAtCutoff($leaderboard, $advanceLimit, $tieBreaker);
    }

    private function eliminateBottomN(array $leaderboard, array $config): array
    {
        $eliminateCount = $config['eliminate_count'] ?? 1;
        $advanceLimit   = max(0, count($leaderboard) - $eliminateCount);

        return $this->splitAtCutoff($leaderboard, $advanceLimit, 'all_tied_advance');
    }

    private function keepTopPercent(array $leaderboard, array $config): array
    {
        $percent      = max(1, min(100, $config['keep_percent'] ?? 50));
        $advanceLimit = max(1, (int) ceil(count($leaderboard) * $percent / 100));
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'all_tied_advance';

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

    private function splitAtCutoff(array $leaderboard, int $cutoff, string $tieBreaker = 'all_tied_advance'): array
    {
        $advanced   = [];
        $eliminated = [];

        $cutoffIndex = max(0, min($cutoff - 1, count($leaderboard) - 1));
        $cutoffEntry = $leaderboard[$cutoffIndex] ?? null;
        $cutoffScore = $cutoffEntry['total_score'] ?? null;

        if ($cutoffScore === null) {
            return ['advanced' => [], 'eliminated' => []];
        }

        $scoresAbove = 0;
        $tieZone = [];
        $belowZone = [];

        foreach ($leaderboard as $entry) {
            $data = [
                'contestant_id'  => $entry['contestant_id'],
                'display_name'   => $entry['display_name'],
                'rank'           => $entry['rank'],
                'score'          => $entry['total_score'],
            ];

            if ($entry['total_score'] > $cutoffScore) {
                $advanced[] = $data;
                $scoresAbove++;
            } elseif ($entry['total_score'] === $cutoffScore) {
                $tieZone[] = $data;
            } else {
                $belowZone[] = $data;
            }
        }

        $remainingSlots = $cutoff - $scoresAbove;
        $hasTie = count($tieZone) > 1 && count($tieZone) > $remainingSlots;

        if ($hasTie) {
            match ($tieBreaker) {
                'all_tied_advance' => [
                    array_push($advanced, ...$tieZone),
                    array_push($eliminated, ...$belowZone),
                ],
                'all_tied_eliminate' => [
                    array_push($eliminated, ...$tieZone),
                    $fillCount = min(count($belowZone), $remainingSlots),
                    array_push($advanced, ...array_slice($belowZone, 0, $fillCount)),
                    array_push($eliminated, ...array_slice($belowZone, $fillCount)),
                ],
                default => [
                    array_push($eliminated, ...$tieZone),
                    array_push($eliminated, ...$belowZone),
                ],
            };
        } else {
            array_push($advanced, ...$tieZone);
            array_push($eliminated, ...$belowZone);
        }

        return compact('advanced', 'eliminated');
    }

    private function findNextRound(Round $currentRound): ?Round
    {
        return Round::where('season_id', $currentRound->season_id)
            ->where(function ($q) use ($currentRound) {
                $q->where('round_number', '>', $currentRound->round_number)
                    ->orWhere('sort_order', '>', $currentRound->sort_order);
            })
            ->orderBy('round_number')
            ->orderBy('sort_order')
            ->first();
    }

    private function finalizeSeason(Round $finalRound): void
    {
        $activeContestants = Contestant::where('season_id', $finalRound->season_id)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($activeContestants, $finalRound) {
            $rank = 1;
            foreach ($activeContestants as $contestant) {
                $status = match ($rank) {
                    1    => 'winner',
                    2    => 'runner_up',
                    3    => 'finalist',
                    default => 'finalist',
                };

                $contestant->update([
                    'status'                => $status,
                    'eliminated_in_round_id' => null,
                ]);
                $rank++;
            }
        });

        $finalRound->season()->update([
            'status'   => 'completed',
            'ends_at'  => now(),
        ]);
    }

    /**
     * Find all rounds that have ended and need transitions processed.
     */
    public function findRoundsNeedingTransition(): array
    {
        return Round::ended()->get()->all();
    }
}
