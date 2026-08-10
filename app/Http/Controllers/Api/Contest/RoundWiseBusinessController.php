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
 *   - Pass ?round_id=58 (or ?round_number=2) → returns whether MY business is
 *     staying in that round or not; if staying, the business's FULL details +
 *     contest id are included. Passing a round id in round_number also works.
 *   - No round param      → returns my business(es) with their full round-wise
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
     * Rounds are only treated as "not open" when they are genuinely future
     * rounds: not active, not started yet, and no contestant assigned or
     * eliminated in them. Completed and currently-running rounds (e.g. round
     * 57 complete / 58 running) keep returning their businesses.
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

        // ── Round check: is MY business staying in this round? ─────────────
        if ($request->filled('round_number') || $request->filled('round_id')) {
            $round = $this->resolveRound($request, $season);

            if (!$round) {
                return $this->notFound('Round not found.');
            }

            // The found round defines the season (an id lookup may cross seasons).
            $season = $round->season;

            // A round is only treated as "not open" when it is genuinely a
            // future round: not the active round, not started yet, and no
            // contestant has been assigned or eliminated in it yet.
            // Completed rounds (57) and the currently running round (58) keep
            // showing their businesses as before.
            $hasParticipants = Contestant::where('season_id', $round->season_id)
                ->where(fn ($q) => $q->where('current_round_id', $round->id)
                    ->orWhere('eliminated_in_round_id', $round->id))
                ->exists();

            $roundNotOpen = !$round->is_active
                && $round->starts_at
                && $round->starts_at->isFuture()
                && !$hasParticipants;

            if ($roundNotOpen) {
                return $this->success('This round has not opened yet.', [
                    'round' => [
                        'round_id'     => $round->id,
                        'round_number' => $round->round_number,
                        'title'        => $round->title,
                        'is_active'    => $round->is_active,
                        'is_open'      => false,
                        'starts_at'    => $round->starts_at?->toIso8601String(),
                        'ends_at'      => $round->ends_at?->toIso8601String(),
                    ],
                    'businesses' => [],
                ]);
            }

            $myContestants = $this->myContestants($season, $userId);
            if ($myContestants->isEmpty()) {
                return $this->success('You are not participating in this season.', [
                    'businesses' => [],
                ]);
            }

            // Only MY businesses are returned for this round. Points and rank are
            // still computed against the full field, but the heavy all-round /
            // all-business build is not needed here.
            $fieldScores = $this->roundFieldScores($season, $round);

            $businesses = [];
            foreach ($myContestants as $contestant) {
                $business = $this->businessInRound($contestant, $round);
                if (!($business['in_round'] ?? false)) {
                    continue;
                }

                $business['points'] = $fieldScores[$contestant->id] ?? 0;
                $business['rank'] = $this->roundRank($fieldScores, $contestant->id);
                $businesses[] = $business;
            }

            return $this->success('My business round check retrieved successfully.', [
                'round' => [
                    'round_id'     => $round->id,
                    'round_number' => $round->round_number,
                    'title'        => $round->title,
                    'is_active'    => $round->is_active,
                    'is_open'      => true,
                    'starts_at'    => $round->starts_at?->toIso8601String(),
                    'ends_at'      => $round->ends_at?->toIso8601String(),
                ],
                'businesses' => $businesses,
            ]);
        }

        // ── No round: full round-wise journey (no season wrapper) ──────────
        $myContestants = $this->myContestants($season, $userId);

        if ($myContestants->isEmpty()) {
            return $this->success('You are not participating in this season.', [
                'businesses' => [],
            ]);
        }

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

    /**
     * The caller's own contestant records in a season.
     */
    private function myContestants(Season $season, int $userId)
    {
        return Contestant::where('season_id', $season->id)
            ->where('contestable_type', Business::class)
            ->whereIn('contestable_id', Business::where('user_id', $userId)->pluck('id'))
            ->with(['contestable.media', 'eliminatedInRound', 'currentRound'])
            ->orderBy('contestable_id')
            ->get();
    }

    /**
     * Resolve the round from the request. Accepts:
     *   ?round_id=58        → by round id (recommended)
     *   ?round_number=2     → by round number within the season
     *   ?round_number=58    → if no round has that number, falls back to id 58
     *                        (so passing a round id in round_number still works)
     */
    private function resolveRound(Request $request, Season $season): ?Round
    {
        $roundId = $request->integer('round_id');
        if ($roundId) {
            return Round::find($roundId);
        }

        $roundNumber = $request->integer('round_number');
        $round = $season->rounds()->where('round_number', $roundNumber)->first();

        // Lenient fallback: the client may have passed a round id as round_number.
        return $round ?? Round::find($roundNumber);
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
     * Points/rank are attached by the caller (computed against the full field).
     */
    private function businessInRound(Contestant $contestant, Round $round): array
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
            'status'   => $status,
        ]);
    }

    /**
     * Scores for the whole field in a single round (contestant_id → total score).
     * Only contestants who actually reached this round are included, so ranks
     * stay true against the full field — no business details are built here.
     */
    private function roundFieldScores(Season $season, Round $round): array
    {
        $voteTotals = Vote::where('round_id', $round->id)
            ->where('votable_type', Contestant::class)
            ->selectRaw('votable_id as contestant_id')
            ->selectRaw('COALESCE(SUM(weight), 0) as total_score')
            ->groupBy('votable_id')
            ->pluck('total_score', 'contestant_id');

        $scores = [];
        $contestants = Contestant::where('season_id', $season->id)
            ->with(['eliminatedInRound', 'contestable'])
            ->get();

        foreach ($contestants as $contestant) {
            // Same "reached this round" rule as buildRounds().
            $eliminatedRound = $contestant->eliminatedInRound;
            if ($contestant->eliminated_in_round_id !== null
                && ($eliminatedRound === null
                    || $eliminatedRound->round_number === null
                    || $eliminatedRound->round_number < $round->round_number)
            ) {
                continue;
            }

            $scores[$contestant->id] = $round->round_number === 1
                ? (float) ($contestant->contestable?->total_points ?? 0)
                : (float) ($voteTotals[$contestant->id] ?? 0);
        }

        return $scores;
    }

    /**
     * Competition-style rank for one contestant within a round field.
     * Ties share a rank (1, 2, 2, 4) — identical to the round-wise lists.
     */
    private function roundRank(array $fieldScores, int $contestantId): ?int
    {
        if (!array_key_exists($contestantId, $fieldScores)) {
            return null;
        }

        arsort($fieldScores);

        $rank = 1;
        $prevScore = null;
        $prevRank = null;
        foreach ($fieldScores as $id => $score) {
            $entryRank = $prevScore !== null && (float) $score === (float) $prevScore
                ? $prevRank
                : $rank;

            if ($id === $contestantId) {
                return $entryRank;
            }

            $prevScore = $score;
            $prevRank = $entryRank;
            $rank++;
        }

        return null;
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
            'avatar_url'               => $this->formatAvatar($contestant->avatar_url ?? $contestable?->getContestantAvatar()),
            'media'                    => $media,

            // Contest progress
            'current_status'           => $contestant->status,
            'current_round_number'     => $contestant->currentRound?->round_number,
            'eliminated_in_round_number' => $contestant->eliminatedInRound?->round_number,
        ];
    }

    private function formatAvatar(?string $path): string
    {
        if (!$path) {
            return asset('admin/default/user.jpg');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
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
            'avatar_url'                   => $this->formatAvatar($contestant->avatar_url ?? $contestable?->getContestantAvatar()),
            'points'                       => $entry['total_score'],
            'rank'                         => $entry['rank'],
            'status'                       => $entry['status'],
            'current_status'               => $contestant->status,
            'current_round_number'         => $contestant->currentRound?->round_number,
            'eliminated_in_round_number'   => $contestant->eliminatedInRound?->round_number,
        ];
    }
}
