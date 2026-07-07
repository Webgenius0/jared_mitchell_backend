<?php

namespace App\Services\Contest;

use App\Events\Contest\ContestantsAdvanced;
use App\Events\Contest\ContestantsEliminated;
use App\Events\Contest\RoundEnded;
use App\Models\Contest\Contestant;
use App\Models\Contest\LeaderboardEntry;
use App\Models\Contest\RoundTransition;
use App\Models\Round;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EliminationService
{
    const CUTOFF_TIE_BREAKER_STRATEGIES = ['all_tied_advance', 'all_tied_eliminate', 'admin_review'];

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
     * 4. Cache the new round's leaderboard
     * 5. Log the transition
     */
    public function processRoundTransition(Round $round): RoundTransition
    {
        // 1. Ensure leaderboard is calculated
        $this->leaderboardService->calculateLeaderboard($round);

        // 2. Find the next round
        $nextRound = $this->findNextRound($round);

        // 3. Get ranked leaderboard entries
        $leaderboard = LeaderboardEntry::forRound($round->id)
            ->ranked()
            ->get();

        $totalCount = $leaderboard->count();

        // 4. Apply the elimination rule to determine who advances/eliminates
        $result = $this->applyEliminationRule($round, $leaderboard);

        // 5. Execute the transition in a transaction
        $transition = DB::transaction(function () use (
            $round, $nextRound, $leaderboard, $totalCount, $result
        ) {
            // Mark the current round as inactive
            $round->update(['is_active' => false]);

            // Advance contestants to the next round
            foreach ($result['advanced'] as $contestantData) {
                $contestant = Contestant::find($contestantData['id']);
                if ($contestant && $nextRound) {
                    $this->contestantService->advanceToRound($contestant, $nextRound);
                }
            }

            // Eliminate contestants
            foreach ($result['eliminated'] as $contestantData) {
                $contestant = Contestant::find($contestantData['id']);
                if ($contestant) {
                    $this->contestantService->eliminate($contestant, $round);
                }
            }

            // Record the transition
            $transition = RoundTransition::create([
                'from_round_id'        => $round->id,
                'to_round_id'          => $nextRound?->id,
                'season_id'            => $round->season_id,
                'status'               => 'completed',
                'elimination_rule'     => $round->elimination_rule ?? 'advance_limit',
                'transition_config'    => $round->advancement_config,
                'total_contestants'    => $totalCount,
                'advanced_count'       => count($result['advanced']),
                'eliminated_count'     => count($result['eliminated']),
                'advanced_contestants' => $result['advanced'],
                'eliminated_contestants' => $result['eliminated'],
                'processed_at'         => now(),
            ]);

            return $transition;
        });

        // 6. Activate the next round (starts the voting/submission period)
        if ($nextRound) {
            $this->activateNextRound($nextRound);

            ContestantsAdvanced::dispatch($transition, $result['advanced']);
            ContestantsEliminated::dispatch($transition, $result['eliminated']);

            Log::info('Round transition completed — contestants advanced', [
                'from_round'          => $round->id,
                'to_round'            => $nextRound->id,
                'advanced'            => count($result['advanced']),
                'eliminated'          => count($result['eliminated']),
                'elimination_rule'    => $round->elimination_rule,
            ]);
        } else {
            // Season has ended — mark remaining contestants as finalists/winners
            $this->finalizeSeason($round);

            Log::info('Season final round completed — no next round', [
                'round_id'      => $round->id,
                'season_id'     => $round->season_id,
                'finalists'     => count($result['advanced']),
            ]);
        }

        // 7. Pre-calculate the next round's leaderboard for cache warming
        if ($nextRound) {
            $this->leaderboardService->calculateLeaderboard($nextRound);
        }

        // 8. Fire the round ended event
        RoundEnded::dispatch($round);

        return $transition;
    }

    /**
     * Apply the elimination rule to determine who advances and who is eliminated.
     *
     * @return array{advanced: array, eliminated: array}
     */
    public function applyEliminationRule(Round $round, $leaderboard): array
    {
        $rule = $round->elimination_rule ?? 'advance_limit';
        $config = $round->advancement_config ?? [];

        return match ($rule) {
            'bottom_n'             => $this->eliminateBottomN($leaderboard, $config),
            'top_percent'          => $this->keepTopPercent($leaderboard, $config),
            'score_below_threshold'=> $this->eliminateBelowThreshold($leaderboard, $config),
            'single_elimination'   => $this->singleElimination($leaderboard),
            'admin_pick'           => $this->adminPick($leaderboard, $config),
            'all_advance'          => $this->allAdvance($leaderboard),
            default                => $this->advanceLimit($leaderboard, $config), // advance_limit
        };
    }

    /**
     * Advance top N contestants (most common — Shark Tank style).
     * Tie-breaking: if contestants at the cutoff have the same score,
     * use the configured tie-breaker strategy.
     */
    private function advanceLimit($leaderboard, array $config): array
    {
        $advanceLimit = $config['advance_limit'] ?? $leaderboard->count();
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'all_tied_advance';

        return $this->splitAtCutoff($leaderboard, $advanceLimit, $tieBreaker);
    }

    /**
     * Eliminate the bottom N contestants.
     */
    private function eliminateBottomN($leaderboard, array $config): array
    {
        $eliminateCount = $config['eliminate_count'] ?? 1;
        $advanceLimit   = max(0, $leaderboard->count() - $eliminateCount);

        return $this->splitAtCutoff($leaderboard, $advanceLimit, 'all_tied_advance');
    }

    /**
     * Keep only the top X% of contestants.
     */
    private function keepTopPercent($leaderboard, array $config): array
    {
        $percent      = max(1, min(100, $config['keep_percent'] ?? 50));
        $advanceLimit = max(1, (int) ceil($leaderboard->count() * $percent / 100));
        $tieBreaker   = $config['cutoff_tie_breaker'] ?? 'all_tied_advance';

        return $this->splitAtCutoff($leaderboard, $advanceLimit, $tieBreaker);
    }

    /**
     * Eliminate all contestants below a score threshold.
     */
    private function eliminateBelowThreshold($leaderboard, array $config): array
    {
        $threshold = $config['score_threshold'] ?? 0;
        $advanced  = [];
        $eliminated = [];

        foreach ($leaderboard as $entry) {
            $data = [
                'id'           => $entry->contestant_id,
                'contestant_id'=> $entry->contestant_id,
                'display_name' => $entry->snapshot['display_name'] ?? 'Unknown',
                'rank'         => $entry->rank,
                'score'        => $entry->total_score,
            ];

            if ($entry->total_score >= $threshold) {
                $advanced[] = $data;
            } else {
                $eliminated[] = $data;
            }
        }

        return compact('advanced', 'eliminated');
    }

    /**
     * Single elimination — only the winner remains, everyone else is eliminated.
     */
    private function singleElimination($leaderboard): array
    {
        $advanced  = [];
        $eliminated = [];

        foreach ($leaderboard as $i => $entry) {
            $data = [
                'id'           => $entry->contestant_id,
                'contestant_id'=> $entry->contestant_id,
                'display_name' => $entry->snapshot['display_name'] ?? 'Unknown',
                'rank'         => $entry->rank,
                'score'        => $entry->total_score,
            ];

            if ($i === 0) {
                $advanced[] = $data;
            } else {
                $eliminated[] = $data;
            }
        }

        return compact('advanced', 'eliminated');
    }

    /**
     * All contestants advance (no elimination this round).
     */
    private function allAdvance($leaderboard): array
    {
        $advanced = [];
        foreach ($leaderboard as $entry) {
            $advanced[] = [
                'id'           => $entry->contestant_id,
                'contestant_id'=> $entry->contestant_id,
                'display_name' => $entry->snapshot['display_name'] ?? 'Unknown',
                'rank'         => $entry->rank,
                'score'        => $entry->total_score,
            ];
        }

        return ['advanced' => $advanced, 'eliminated' => []];
    }

    /**
     * No automatic elimination — mark all for admin decision.
     */
    private function adminPick($leaderboard, array $config): array
    {
        // All go into a "pending admin" bucket — no auto-advance or elimination
        return [
            'advanced'   => [],
            'eliminated' => [],
        ];
    }

    /**
     * Split the leaderboard at a cutoff position, handling ties.
     *
     * Strategy:
     * 1. Contestants with score > cutoffScore always advance.
     * 2. Contestants with score == cutoffScore form the "tie zone".
     * 3. Tie breaker determines tie zone fate:
     *    - all_tied_advance: ALL tie zone contestants advance, rest eliminated
     *    - all_tied_eliminate: tie zone eliminated, fill remaining slots from below
     *    - admin_review: only scoresAbove advance, tie zone and below eliminated
     */
    private function splitAtCutoff($leaderboard, int $cutoff, string $tieBreaker = 'all_tied_advance'): array
    {
        $advanced   = [];
        $eliminated = [];

        $cutoffIndex = max(0, min($cutoff - 1, $leaderboard->count() - 1));
        $cutoffEntry = $leaderboard[$cutoffIndex] ?? null;
        $cutoffScore = $cutoffEntry?->total_score;

        if ($cutoffScore === null) {
            return ['advanced' => [], 'eliminated' => []];
        }

        // Separate entries by score relative to cutoff
        $scoresAbove = 0;
        $tieZone = [];  // entries with score == cutoffScore
        $belowZone = []; // entries with score < cutoffScore

        foreach ($leaderboard as $i => $entry) {
            $data = [
                'id'           => $entry->contestant_id,
                'contestant_id'=> $entry->contestant_id,
                'display_name' => $entry->snapshot['display_name'] ?? 'Unknown',
                'rank'         => $entry->rank,
                'score'        => $entry->total_score,
            ];

            if ($entry->total_score > $cutoffScore) {
                $advanced[] = $data;
                $scoresAbove++;
            } elseif ($entry->total_score === $cutoffScore) {
                $tieZone[] = $data;
            } else {
                $belowZone[] = $data;
            }
        }

        // Check if a tie actually exists at the boundary
        // A tie exists when there are MORE entries with cutoffScore than can fit
        // in the remaining slots after scoresAbove
        $remainingSlots = $cutoff - $scoresAbove;
        $hasTie = count($tieZone) > 1 && count($tieZone) > $remainingSlots;

        if ($hasTie) {
            // A tie exists at the cutoff — apply tie breaker
            match ($tieBreaker) {
                'all_tied_advance' => [
                    // All tied contestants advance, rest eliminated
                    array_push($advanced, ...$tieZone),
                    array_push($eliminated, ...$belowZone),
                ],
                'all_tied_eliminate' => [
                    // Tied contestants eliminated, fill slots from below
                    array_push($eliminated, ...$tieZone),
                    $fillCount = min(count($belowZone), $remainingSlots),
                    array_push($advanced, ...array_slice($belowZone, 0, $fillCount)),
                    array_push($eliminated, ...array_slice($belowZone, $fillCount)),
                ],
                default => [
                    // admin_review: only scoresAbove advance
                    array_push($eliminated, ...$tieZone),
                    array_push($eliminated, ...$belowZone),
                ],
            };
        } else {
            // No tie — advance the tie zone (it fits within remaining slots),
            // eliminate the below zone
            array_push($advanced, ...$tieZone);
            array_push($eliminated, ...$belowZone);
        }

        return compact('advanced', 'eliminated');
    }

    /**
     * Find the next round after the current one in the same season.
     */
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

    /**
     * Activate the next round so it starts accepting submissions/votes.
     */
    private function activateNextRound(Round $nextRound): void
    {
        $nextRound->update([
            'is_active'  => true,
            'starts_at'  => $nextRound->starts_at ?? now(),
        ]);

        Log::info('Next round activated', [
            'round_id'  => $nextRound->id,
            'round_number' => $nextRound->round_number,
        ]);
    }

    /**
     * When there is no next round, mark remaining active contestants as finalists/winners.
     */
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
                    'eliminated_in_round_id'=> null, // They weren't eliminated
                ]);
                $rank++;
            }
        });

        // Also update the season status
        $finalRound->season()->update([
            'status'   => 'completed',
            'ends_at'  => now(),
        ]);

        Log::info('Season finalized', [
            'season_id'         => $finalRound->season_id,
            'winner_count'      => $activeContestants->count(),
        ]);
    }

    /**
     * Find all rounds that have ended and need transitions processed.
     */
    public function findRoundsNeedingTransition(): array
    {
        $rounds = Round::ended()->get()->filter(function (Round $round) {
            // Skip rounds that already have a successful transition
            $existingTransition = RoundTransition::where('from_round_id', $round->id)
                ->where('status', 'completed')
                ->exists();

            return !$existingTransition;
        })->values()->all();

        return $rounds;
    }
}
