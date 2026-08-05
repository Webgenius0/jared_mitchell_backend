<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Contest\Vote;
use App\Models\Round;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Round-wise business endpoints.
 *
 * GET /api/v1/contest/season-rounds → public: every business in a season, round-wise
 * GET /api/v1/contest/my-rounds     → auth: ONLY the caller's own business(es)
 *
 * my-rounds behaviour:
 *   - Pass ?round_number=2 → returns whether MY business is staying in round 2
 *     or not; if staying, the business's FULL details + contest id are included.
 *   - No ?round_number     → returns my business(es) with their full round-wise
 *     journey (no season/session wrapper).
 *   - The season is resolved automatically (active season, then the caller's
 *     most recent season). ?season_id= is still accepted if needed.
 *
 * Pure read-only endpoints — no existing behaviour is changed.
 */
class RoundWiseBusinessController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/contest/season-rounds
     *
     * Round-wise list of ALL businesses for a season. Shows exactly which
     * businesses reached each round, with their points and rank.
     */
    public function index(Request $request): JsonResponse
    {
        $season = $this->resolveSeason($request);

        if (!$season) {
            return $this->notFound('No season found. Pass ?season_id= or ensure an active season exists.');
        }

        return $this->success('Round-wise businesses retrieved successfully.', [
            'season' => $this->seasonSummary($season),
            'rounds' => $this->buildRounds($season),
        ]);
    }

    /**
     * GET /api/v1/contest/my-rounds
     *
     * Authenticated — returns ONLY the caller's own business(es).
     *
     * With ?round_number=N: tells whether each of my businesses is staying in
     * that round. If it is, the response includes the business's full details
     * and the contest (contestant) id. Ranks are computed against the full field.
     *
     * Without ?round_number: returns my businesses with their complete round-wise
     * journey (no season wrapper).
     */
    public function myBusiness(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $season = $this->resolveMySeason($request, $userId);

        if (!$season) {
            return $this->notFound('No season found for your account.');
        }

        $myContestants = Contestant::where('season_id', $season->id)
            ->where('contestable_type', Business::class)
            ->whereIn('contestable_id', Business::where('user_id', $userId)->pluck('id'))
            ->with(['contestable.media', 'eliminatedInRound', 'currentRound'])
            ->orderBy('contestable_id')
            ->get();

        if ($myContestants->isEmpty()) {
            return $this->success('You are not participating in this season.', [
                'businesses' => [],
            ]);
        }

        // ── Round-number check: is MY business staying in this round? ──────
        if ($request->filled('round_number')) {
            $round = $season->rounds()
                ->where('round_number', $request->integer('round_number'))
                ->first();

            if (!$round) {
                return $this->notFound('Round not found in this season.');
            }

            // Ranked field for this round so points/rank are true positions.
            $roundData = collect($this->buildRounds($season))
                ->firstWhere('id', $round->id);
            $entriesByContestant = collect($roundData['businesses'] ?? [])
                ->keyBy('contestant_id');

            return $this->success('My business round check retrieved successfully.', [
                'round' => [
                    'round_id'     => $round->id,
                    'round_number' => $round->round_number,
                    'title'        => $round->title,
                    'is_active'    => $round->is_active,
                    'starts_at'    => $round->starts_at?->toIso8601String(),
                    'ends_at'      => $round->ends_at?->toIso8601String(),
                ],
                'businesses' => $myContestants
                    ->map(fn ($contestant) => $this->businessInRound(
                        $contestant,
                        $round,
                        $entriesByContestant->get($contestant->id, [])
                    ))
                    ->values()
                    ->all(),
            ]);
        }

        // ── No round_number: full round-wise journey (no season wrapper) ────
        $rounds = $this->buildRounds($season);

        $businesses = [];
        foreach ($myContestants as $contestant) {
            $contestable = $contestant->contestable;

            $journey = [];
            foreach ($rounds as $round) {
                $entry = collect($round['businesses'])->firstWhere('contestant_id', $contestant->id);
                if (!$entry) {
                    continue; // eliminated before this round
                }

                $journey[] = [
                    'round_id'      => $round['id'],
                    'round_number'  => $round['round_number'],
                    'title'         => $round['title'],
                    'is_active'     => $round['is_active'],
                    'advance_limit' => $round['advance_limit'],
                    'starts_at'     => $round['starts_at'],
                    'ends_at'       => $round['ends_at'],
                    'points'        => $entry['points'],
                    'rank'          => $entry['rank'],
                    'status'        => $entry['status'],
                ];
            }

            $businesses[] = [
                'contestant_id'              => $contestant->id,
                'business_id'                => $contestable?->id,
                'business_name'              => $contestable?->getContestantName(),
                'current_status'             => $contestant->status,
                'current_round_number'       => $contestant->currentRound?->round_number,
                'eliminated_in_round_number' => $contestant->eliminatedInRound?->round_number,
                'rounds'                     => $journey,
            ];
        }

        return $this->success('My business round-wise journey retrieved successfully.', [
            'businesses' => $businesses,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function resolveSeason(Request $request): ?Season
    {
        $seasonId = $request->integer('season_id');

        return $seasonId ? Season::find($seasonId) : Season::active();
    }

    /**
     * Resolve the season for the authenticated business owner.
     * 1. Explicit ?season_id= if provided.
     * 2. The active season.
     * 3. The caller's most recent season (so it works even when nothing is active).
     */
    private function resolveMySeason(Request $request, int $userId): ?Season
    {
        $seasonId = $request->integer('season_id');
        if ($seasonId) {
            return Season::find($seasonId);
        }

        $active = Season::active();
        if ($active) {
            return $active;
        }

        $contestant = Contestant::where('contestable_type', Business::class)
            ->whereIn('contestable_id', Business::where('user_id', $userId)->pluck('id'))
            ->latest('entered_at')
            ->first();

        return $contestant?->season;
    }

    private function seasonSummary(Season $season): array
    {
        return [
            'id'           => $season->id,
            'title'        => $season->title,
            'slug'         => $season->slug,
            'status'       => $season->status,
            'contest_type' => $season->contest_type,
        ];
    }

    /**
     * Whether a contestant is staying in the given round, plus full details.
     *
     * $entry is the optional ranked entry (points/rank) for this round.
     */
    private function businessInRound(Contestant $contestant, Round $round, array $entry = []): array
    {
        $eliminatedRound = $contestant->eliminatedInRound;

        // Staying = not eliminated before this round.
        $reached = $contestant->eliminated_in_round_id === null
            || ($eliminatedRound !== null
                && $eliminatedRound->round_number !== null
                && $eliminatedRound->round_number >= $round->round_number);

        // Same status vocabulary as the round-wise lists:
        // competing | advanced | eliminated | not_in_round
        $status = 'not_in_round';
        if ($reached) {
            $status = match (true) {
                $contestant->eliminated_in_round_id === $round->id => 'eliminated',
                $contestant->eliminated_in_round_id !== null      => 'advanced', // eliminated in a later round
                default                                            => 'competing',
            };
        }

        return array_merge($this->businessDetails($contestant), [
            'in_round' => $reached,
            'points'   => $entry['points'] ?? null,
            'rank'     => $entry['rank'] ?? null,
            'status'   => $status,
        ]);
    }

    /**
     * Full business details for a contestant, including the contest id.
     */
    private function businessDetails(Contestant $contestant): array
    {
        $contestable = $contestant->contestable;
        $media = $this->formatMedia($contestable);

        return [
            // Contest / business identifiers
            'contestant_id'            => $contestant->id,
            'business_id'              => $contestable?->id,
            'business_name'            => $contestable?->business_name,
            'slug'                     => $contestable?->slug,

            // Owner
            'owner_founder_name'       => $contestable?->owner_founder_name,

            // Full business details
            'business_status'          => $contestable?->status,
            'story'                    => $contestable?->story,
            'mission'                  => $contestable?->mission,
            'website_social_media'     => $contestable?->website_social_media,
            'community_impact_statement' => $contestable?->community_impact_statement,
            'revenue_stage'            => $contestable?->revenue_stage,
            'why_they_deserve_to_compete' => $contestable?->why_they_deserve_to_compete,
            'total_points'             => (int) ($contestable?->total_points ?? 0),

            // Media / avatar
            'avatar_url'               => $contestant->avatar_url
                ? asset('storage/' . $contestant->avatar_url)
                : ($media['primary_image'] ?? asset('admin/default/user.jpg')),
            'media'                    => $media,

            // Contest progress
            'current_status'           => $contestant->status,
            'current_round_number'     => $contestant->currentRound?->round_number,
            'eliminated_in_round_number' => $contestant->eliminatedInRound?->round_number,
        ];
    }

    private function formatMedia($contestable): array
    {
        $media = [
            'images'        => [],
            'primary_image' => null,
        ];

        if (!$contestable || !$contestable->media || $contestable->media->isEmpty()) {
            $media['primary_image'] = asset('admin/default/user.jpg');

            return $media;
        }

        foreach ($contestable->media as $m) {
            if (blank($m->file_path)) {
                continue;
            }

            $path = preg_replace('#^storage/#', '', $m->file_path);
            $url = filter_var($path, FILTER_VALIDATE_URL)
                ? $path
                : Storage::disk('public')->url($path);

            $media['images'][] = [
                'id'        => $m->id,
                'url'       => $url,
                'file_name' => $m->file_name,
                'mime_type' => $m->mime_type,
            ];
        }

        $media['primary_image'] = $media['images'][0]['url'] ?? asset('admin/default/user.jpg');

        return $media;
    }

    /**
     * Build the round-wise ranked business list for a season.
     *
     * Each round entry has a per-business "status":
     *   - competing  → still in the contest, currently competing
     *   - advanced   → reached this round, but was eliminated in a LATER round
     *   - eliminated → eliminated in THIS round
     */
    private function buildRounds(Season $season): array
    {
        $rounds = $season->rounds()->orderBy('round_number')->get();

        $contestants = Contestant::where('season_id', $season->id)
            ->with(['contestable', 'eliminatedInRound', 'currentRound'])
            ->get();

        // Total vote score per contestant per round (one small query per round).
        $roundVoteTotals = [];
        foreach ($rounds as $round) {
            $roundVoteTotals[$round->id] = Vote::where('round_id', $round->id)
                ->where('votable_type', Contestant::class)
                ->selectRaw('votable_id as contestant_id')
                ->selectRaw('COALESCE(SUM(weight), 0) as total_score')
                ->groupBy('votable_id')
                ->pluck('total_score', 'contestant_id');
        }

        $data = [];
        foreach ($rounds as $round) {
            $businesses = [];

            foreach ($contestants as $contestant) {
                // A business reached this round if it was not eliminated before it.
                $eliminatedRound = $contestant->eliminatedInRound;
                if ($contestant->eliminated_in_round_id !== null
                    && ($eliminatedRound === null
                        || $eliminatedRound->round_number === null
                        || $eliminatedRound->round_number < $round->round_number)
                ) {
                    continue;
                }

                $contestable = $contestant->contestable;

                $points = $round->round_number === 1
                    ? (float) ($contestable->total_points ?? 0)
                    : (float) ($roundVoteTotals[$round->id][$contestant->id] ?? 0);

                $businesses[] = [
                    'contestant'       => $contestant,
                    'contestable'      => $contestable,
                    'contestant_id'    => $contestant->id,
                    'display_name'     => $contestant->display_name,
                    'avatar_url'       => $contestant->avatar_url,
                    'contestable_name' => $contestable ? $contestable->getContestantName() : null,
                    'total_score'      => $points,
                    // Per-round status: eliminated here / advanced past it / still competing.
                    'status'           => $contestant->eliminated_in_round_id === $round->id
                        ? 'eliminated'
                        : ($contestant->eliminated_in_round_id !== null ? 'advanced' : 'competing'),
                ];
            }

            // Deterministic sort: points desc, then contestant id.
            usort($businesses, function ($a, $b) {
                if ($b['total_score'] !== $a['total_score']) {
                    return $b['total_score'] <=> $a['total_score'];
                }

                return $a['contestant_id'] <=> $b['contestant_id'];
            });

            // Assign ranks (ties share the same rank, same as the leaderboard).
            $rank = 1;
            foreach ($businesses as $i => &$entry) {
                $entry['rank'] = ($i > 0 && $entry['total_score'] === $businesses[$i - 1]['total_score'])
                    ? $businesses[$i - 1]['rank']
                    : $rank;
                $rank++;
            }
            unset($entry);

            $data[] = [
                'id'               => $round->id,
                'season_id'        => $round->season_id,
                'round_number'     => $round->round_number,
                'title'            => $round->title,
                'goal'             => $round->goal,
                'advance_limit'    => $round->advance_limit,
                'elimination_rule' => $round->elimination_rule,
                'is_active'        => $round->is_active,
                'starts_at'        => $round->starts_at?->toIso8601String(),
                'ends_at'          => $round->ends_at?->toIso8601String(),
                'voting_ends_at'   => $round->voting_ends_at?->toIso8601String(),
                'business_count'   => count($businesses),
                'businesses'       => array_map(fn ($entry) => $this->formatBusiness($entry), $businesses),
            ];
        }

        return $data;
    }

    private function formatBusiness(array $entry): array
    {
        $contestant = $entry['contestant'];
        $contestable = $entry['contestable'];

        return [
            'contestant_id'                => $entry['contestant_id'],
            'business_id'                  => $contestable?->id ?? null,
            'business_name'                => $entry['contestable_name'],
            'display_name'                 => $entry['display_name'],
            'avatar_url'                   => $contestant->avatar_url
                ? asset('storage/' . $contestant->avatar_url)
                : asset('admin/default/user.jpg'),
            'points'                       => $entry['total_score'],
            'rank'                         => $entry['rank'],
            'status'                       => $entry['status'],
            'current_status'               => $contestant->status,
            'current_round_number'         => $contestant->currentRound?->round_number,
            'eliminated_in_round_number'   => $contestant->eliminatedInRound?->round_number,
        ];
    }
}
