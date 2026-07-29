<?php

namespace App\Http\Controllers\Api\Contest;

use App\Http\Controllers\Controller;
use App\Models\Contest\Contestant;
use App\Models\Contest\Vote;
use App\Models\ContestApplication;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ContestantProfileController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/contest/contestants/{contestant}
     *
     * Get the full profile of a contestant including their business info,
     * current round details, voting statistics, and current submission.
     * Response format mirrors the spotlight details endpoint for consistency.
     * Public endpoint — no authentication required.
     */
    public function show(Contestant $contestant): JsonResponse
    {
        // Load relationships eagerly
        $contestant->load([
            'contestable',
            'contestable.media',
            'contestable.user.profile',
            'currentRound',
            'season',
            'submissions' => function ($query) {
                $query->whereIn('status', ['submitted', 'approved'])
                    ->latest('submitted_at');
            },
        ]);

        $contestable = $contestant->contestable ?? null;
        $currentRound = $contestant->currentRound ?? null;

        // Compute vote aggregates for this contestant in current round
        $voteAggregates = $this->computeVoteAggregates($contestant);

        // Today vs yesterday votes for trend
        $todayVotes = $this->countVotes($contestant, now()->startOfDay());
        $yesterdayVotes = $this->countVotesBetween($contestant, now()->subDay()->startOfDay(), now()->subDay()->endOfDay());

        // Trend calculation
        $trend = 'neutral';
        if ($todayVotes > $yesterdayVotes) {
            $trend = 'up';
        } elseif ($todayVotes < $yesterdayVotes) {
            $trend = 'down';
        }

        // Current round submission
        $submission = $currentRound ? $this->submissionForRound($contestant, $currentRound->id) : null;

        // --- Media formatting (like spotlight's formatBusinessMedia) ---
        $media = $this->formatContestantMedia($contestable);

        // --- Interactions (like spotlight) ---
        $interactions = [
            'likes_count'     => (int) ($contestable->total_claps ?? 0),
            'bookmarks_count' => (int) ($contestable->total_saves ?? 0),
            'shares_count'    => (int) ($contestable->total_shares ?? 0),
            'total_points'    => (int) ($contestable->total_points ?? 0),
        ];

        // --- Owner info (like spotlight) ---
        $owner = null;
        if ($contestable && $contestable->user) {
            $user = $contestable->user;
            $owner = [
                'id'    => $user->id,
                'name'  => $user->profile?->name ?? $user->email,
                'email' => $user->email,
            ];
        }

        // --- Contest applications history (like spotlight's application_history) ---
        $contestApplications = ContestApplication::where('business_id', $contestable?->id)
            ->with(['season', 'approver.profile'])
            ->latest()
            ->get()
            ->map(function ($app) {
                return [
                    'id'              => $app->id,
                    'season'          => $app->season ? [
                        'id'     => $app->season->id,
                        'title'  => $app->season->title,
                        'slug'   => $app->season->slug,
                        'status' => $app->season->status,
                    ] : null,
                    'status'          => $app->status,
                    'ai_verdict'      => $app->ai_verdict,
                    'ai_confidence'   => $app->ai_confidence,
                    'approved_at'     => $app->approved_at?->toIso8601String(),
                    'rejected_reason' => $app->rejected_reason,
                    'admin_note'      => $app->admin_note,
                    'approver'        => $app->approver ? [
                        'id'   => $app->approver->id,
                        'name' => $app->approver->profile?->name ?? $app->approver->email,
                    ] : null,
                    'created_at'      => $app->created_at?->toIso8601String(),
                ];
            });

        // --- Voting summary (round-based, like spotlight) ---
        $voting = [
            'total_votes'          => (int) ($voteAggregates->total_votes ?? 0),
            'total_weighted_score' => (float) ($voteAggregates->total_weighted_score ?? 0),
            'avg_score'            => isset($voteAggregates->total_votes) && $voteAggregates->total_votes > 0
                ? round((float) ($voteAggregates->total_weighted_score ?? 0) / (int) $voteAggregates->total_votes, 2)
                : null,
            'today_votes'          => (int) $todayVotes,
            'yesterday_votes'      => (int) $yesterdayVotes,
            'trend'                => $trend,
        ];

        // --- Current round (contest-specific) ---
        $currentRoundData = $currentRound ? [
            'id'                => $currentRound->id,
            'season_id'         => $currentRound->season_id,
            'round_number'      => $currentRound->round_number,
            'title'             => $currentRound->title,
            'goal'              => $currentRound->goal,
            'voting_strategy'   => $currentRound->voting_strategy,
            'submission_type'   => $currentRound->submission_type,
            'is_active'         => $currentRound->is_active,
            'starts_at'         => $currentRound->starts_at?->toIso8601String(),
            'ends_at'           => $currentRound->ends_at?->toIso8601String(),
            'voting_ends_at'    => $currentRound->voting_ends_at?->toIso8601String(),
            'days_left'         => $this->daysLeft($currentRound),
            'is_voting_open'    => $currentRound->isVotingOpen(),
            'is_submission_open'=> $currentRound->isSubmissionOpen(),
        ] : null;

        // --- Submission (contest-specific) ---
        $submissionData = $submission ? [
            'id'           => $submission->id,
            'title'        => $submission->title,
            'description'  => $submission->description,
            'media_urls'   => $submission->media_urls
                ? collect($submission->media_urls)->map(fn($url) => $this->formatImageUrl($url))->toArray()
                : [],
            'status'       => $submission->status,
            'score'        => $submission->score ? (float) $submission->score : null,
            'submitted_at' => $submission->submitted_at?->toIso8601String(),
        ] : null;

        return $this->success('Contestant profile retrieved successfully.', [
            'contestant' => [
                // Basic identification (like spotlight)
                'id'                        => $contestant->id,
                'season_id'                 => $contestant->season_id,
                'display_name'              => $contestant->display_name,
                'slug'                      => $contestant->slug,
                'avatar_url'                => $contestant->avatar_url
                    ? $this->formatImageUrl($contestant->avatar_url)
                    : asset('admin/default/user.jpg'),
                'status'                    => $contestant->status,
                'total_score'               => (float) $contestant->total_score,

                // Business info (flattened like spotlight)
                'business_name'             => $contestable?->business_name ?? null,
                'owner_founder_name'        => $contestable?->owner_founder_name ?? null,
                'business_slug'             => $contestable?->slug ?? null,
                'story'                     => $contestable?->story ?? null,
                'mission'                   => $contestable?->mission ?? null,
                'website_social_media'      => $contestable?->website_social_media ?? null,
                'community_impact_statement'=> $contestable?->community_impact_statement ?? null,
                'revenue_stage'             => $contestable?->revenue_stage ?? null,
                'why_they_deserve_to_compete'=> $contestable?->why_they_deserve_to_compete ?? null,
                'business_status'           => $contestable?->status ?? null,

                // Media (formatted like spotlight)
                'media'                     => $media,

                // Interactions (like spotlight)
                'interactions'              => $interactions,

                // Owner (like spotlight)
                'owner'                     => $owner,

                // Voting
                'voting'                    => $voting,

                // Current round (contest-specific)
                'current_round'             => $currentRoundData,

                // Submission (contest-specific)
                'submission'                => $submissionData,

                // Contest applications history (like spotlight's application_history)
                'application_history'       => $contestApplications,

                // Timestamps
                'entered_at'                => $contestant->entered_at?->toIso8601String(),
                'eliminated_at'             => $contestant->eliminated_at?->toIso8601String(),
                'created_at'                => $contestant->created_at?->toIso8601String(),
                'updated_at'                => $contestant->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Format contestant media (from contestable BusinessMedia) with named keys
     * and default image fallback, mirroring spotlight's formatBusinessMedia.
     */
    private function formatContestantMedia($contestable): array
    {
        $media = [
            'images'        => [],
            'primary_image' => null,
        ];

        if (! $contestable || ! $contestable->media || $contestable->media->count() === 0) {
            $media['primary_image'] = asset('admin/default/user.jpg');
            return $media;
        }

        foreach ($contestable->media as $m) {
            $url = $this->formatImageUrl($m->file_path) ?? asset('admin/default/user.jpg');
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
     * Convert a storage path or URL to a public URL.
     * Handles 'storage/' prefix stripping and falls back to default image.
     */
    private function formatImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Remove 'storage/' prefix if present since Storage::disk('public')->url() already adds it
        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
    }

    /**
     * Compute vote aggregates for a contestant in the current round.
     */
    private function computeVoteAggregates(Contestant $contestant)
    {
        $currentRoundId = $contestant->current_round_id;

        if (! $currentRoundId) {
            return (object) [
                'total_votes'          => 0,
                'total_weighted_score' => 0,
            ];
        }

        return Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->selectRaw('COUNT(*) as total_votes')
            ->selectRaw('COALESCE(SUM(weight), 0) as total_weighted_score')
            ->first();
    }

    /**
     * Count votes for a contestant since a given datetime.
     */
    private function countVotes(Contestant $contestant, $since): int
    {
        $currentRoundId = $contestant->current_round_id;

        if (! $currentRoundId) {
            return 0;
        }

        return Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Count votes for a contestant between two datetimes.
     */
    private function countVotesBetween(Contestant $contestant, $start, $end): int
    {
        $currentRoundId = $contestant->current_round_id;

        if (! $currentRoundId) {
            return 0;
        }

        return Vote::where('round_id', $currentRoundId)
            ->where('votable_type', Contestant::class)
            ->where('votable_id', $contestant->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    /**
     * Calculate days left for voting or submission.
     */
    private function daysLeft($round): int
    {
        if ($round->voting_ends_at && $round->voting_ends_at->isFuture()) {
            return (int) now()->diffInDays($round->voting_ends_at);
        }

        if ($round->ends_at && $round->ends_at->isFuture()) {
            return (int) now()->diffInDays($round->ends_at);
        }

        return 0;
    }

    /**
     * Get the contestant's submission for a given round.
     */
    private function submissionForRound(Contestant $contestant, int $roundId): ?\App\Models\Contest\RoundSubmission
    {
        return $contestant->submissions
            ->where('round_id', $roundId)
            ->first();
    }
}
