<?php

namespace App\Services\Contest;

use App\Contracts\Contestable;
use App\Models\Contest\AiReview;
use App\Models\Contest\Contestant;
use App\Models\Round;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ContestantService
{
    /**
     * Create a Contestant record from an approved application.
     */
    // public function createFromApplication(Contestant $application, AiReview $review): Contestant
    // {
    //     return DB::transaction(function () use ($application, $review) {
    //         $business = $application->business;

    //         if (!$business) {
    //             throw new \RuntimeException('Cannot create contestant: no business found for application #' . $application->id);
    //         }

    //         $season    = $application->season;
    //         $firstRound = $season->rounds()->orderBy('sort_order')->orderBy('id')->first();

    //         if (!$firstRound) {
    //             throw new \RuntimeException('Cannot create contestant: no rounds defined for season #' . $season->id);
    //         }

    //         $displayName = $business instanceof Contestable
    //             ? $business->getContestantName()
    //             : ($business->business_name ?? $business->id);

    //         $contestant = Contestant::create([
    //             'season_id'         => $season->id,
    //             'contestable_type'  => get_class($business),
    //             'contestable_id'    => $business->id,
    //             'display_name'      => $displayName,
    //             'slug'              => Str::slug($displayName) . '-' . Str::random(6),
    //             'avatar_url'        => $business instanceof Contestable ? $business->getContestantAvatar() : null,
    //             'status'            => 'active',
    //             'current_round_id'  => $firstRound->id,
    //             'entered_at'        => now(),
    //             'metadata'          => [
    //                 'application_id' => $application->id,
    //                 'ai_review_id'   => $review->id,
    //                 'ai_score'       => $review->score,
    //                 'ai_confidence'  => $review->confidence,
    //             ],
    //         ]);

    //         // Update the application to link it to the contestant
    //         $application->update([
    //             'status'      => 'approved',
    //             'approved_at' => now(),
    //         ]);

    //         Log::info('Contestant created from application', [
    //             'application_id' => $application->id,
    //             'contestant_id'  => $contestant->id,
    //             'display_name'   => $displayName,
    //         ]);

    //         return $contestant;
    //     });
    // }

    public function createFromApplication(Contestant $application, AiReview $review): Contestant
    {
        return DB::transaction(function () use ($application, $review) {
            $business = $application->business;   // now correct — ContestApplication->business()
            $season   = $application->season;      // now correct — ContestApplication->season()

            if (!$business) {
                throw new RuntimeException('Cannot create contestant: no business found for application #' . $application->id);
            }

            $firstRound = $season->rounds()->orderBy('round_number')->first(); // round_number use koro, sort_order na (consistent)

            if (!$firstRound) {
                throw new RuntimeException('Cannot create contestant: no rounds defined for season #' . $season->id);
            }

            $displayName = $business instanceof Contestable
                ? $business->getContestantName()
                : ($business->business_name ?? (string) $business->id);

            $contestant = Contestant::create([
                'season_id'        => $season->id,
                'contestable_type' => get_class($business),
                'contestable_id'   => $business->id,
                'display_name'     => $displayName,
                'slug'             => Str::slug($displayName) . '-' . Str::random(6),
                'avatar_url'       => $business instanceof Contestable ? $business->getContestantAvatar() : null,
                'status'           => 'active',
                'current_round_id' => $firstRound->id,
                'entered_at'       => now(),
                'metadata'         => [
                    'application_id' => $application->id,
                    'ai_review_id'   => $review->id,
                    'ai_score'       => $review->score,
                    'ai_confidence'  => $review->confidence,
                ],
            ]);

            $application->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            Log::info('Contestant created from application', [
                'application_id' => $application->id,
                'contestant_id'  => $contestant->id,
            ]);

            return $contestant;
        });
    }

    /**
     * Get active contestants for a given season.
     */
    public function activeContestants(int $seasonId)
    {
        return Contestant::where('season_id', $seasonId)
            ->active()
            ->with('contestable')
            ->orderBy('total_score', 'desc')
            ->get();
    }

    /**
     * Advance a contestant to the next round.
     */
    public function advanceToRound(Contestant $contestant, Round $round): void
    {
        $contestant->advanceToRound($round);

        Log::info('Contestant advanced to round', [
            'contestant_id' => $contestant->id,
            'round_id'      => $round->id,
            'round_number'  => $round->round_number,
        ]);
    }

    /**
     * Eliminate a contestant.
     */
    public function eliminate(Contestant $contestant, ?Round $inRound = null): void
    {
        $contestant->eliminate($inRound);

        Log::info('Contestant eliminated', [
            'contestant_id' => $contestant->id,
            'round_id'      => $inRound?->id ?? $contestant->current_round_id,
        ]);
    }
}
