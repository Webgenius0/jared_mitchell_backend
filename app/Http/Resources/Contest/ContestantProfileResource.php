<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContestantProfileResource extends JsonResource
{
    /**
     * Transform the contestant into a detailed profile array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contestable = $this->contestable ?? null;
        $currentRound = $this->currentRound ?? null;
        $submission = $this->currentRound ? $this->submissionForRound($this->currentRound->id) : null;

        // Calculate vote stats
        $voteStats = $this->voteStats ?? [];

        // Trend calculation
        $todayVotes = $voteStats['today_votes'] ?? 0;
        $yesterdayVotes = $voteStats['yesterday_votes'] ?? 0;
        $trend = 'neutral';
        if ($todayVotes > $yesterdayVotes) {
            $trend = 'up';
        } elseif ($todayVotes < $yesterdayVotes) {
            $trend = 'down';
        }

        return [
            'id' => $this->id,
            'season_id' => $this->season_id,
            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'avatar_url' => $this->avatar_url
                ? asset('storage/' . $this->avatar_url)
                : asset('admin/default/user.jpg'),
            'status' => $this->status,
            'total_score' => (float) $this->total_score,
            'entered_at' => $this->entered_at?->toIso8601String(),
            'eliminated_at' => $this->eliminated_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Business / contestable entity
            'contestable' => $contestable ? [
                'id' => $contestable->id,
                'type' => get_class($contestable),
                'business_name' => $contestable->business_name ?? null,
                'owner_founder_name' => $contestable->owner_founder_name ?? null,
                'slug' => $contestable->slug ?? null,
                'story' => $contestable->story ?? null,
                'mission' => $contestable->mission ?? null,
                'website_social_media' => $contestable->website_social_media ?? null,
                'community_impact_statement' => $contestable->community_impact_statement ?? null,
                'revenue_stage' => $contestable->revenue_stage ?? null,
                'why_they_deserve_to_compete' => $contestable->why_they_deserve_to_compete ?? null,
                'status' => $contestable->status ?? null,
                'total_claps' => (int) ($contestable->total_claps ?? 0),
                'total_saves' => (int) ($contestable->total_saves ?? 0),
                'total_shares' => (int) ($contestable->total_shares ?? 0),
                'total_points' => (int) ($contestable->total_points ?? 0),
                'media' => $contestable->media && $contestable->media->count() > 0
                    ? $contestable->media->map(fn($m) => [
                        'id' => $m->id,
                        'file_path' => asset('storage/' . $m->file_path),
                        'file_name' => $m->file_name,
                        'mime_type' => $m->mime_type,
                    ])
                    : [],
            ] : null,

            // Current round information
            'current_round' => $currentRound ? [
                'id' => $currentRound->id,
                'season_id' => $currentRound->season_id,
                'round_number' => $currentRound->round_number,
                'title' => $currentRound->title,
                'goal' => $currentRound->goal,
                'voting_strategy' => $currentRound->voting_strategy,
                'submission_type' => $currentRound->submission_type,
                'is_active' => $currentRound->is_active,
                'starts_at' => $currentRound->starts_at?->toIso8601String(),
                'ends_at' => $currentRound->ends_at?->toIso8601String(),
                'voting_ends_at' => $currentRound->voting_ends_at?->toIso8601String(),
                'days_left' => $this->daysLeft($currentRound),
                'is_voting_open' => $currentRound->isVotingOpen(),
                'is_submission_open' => $currentRound->isSubmissionOpen(),
            ] : null,

            // Voting statistics
            'voting' => [
                'total_votes' => (int) ($voteStats['total_votes'] ?? 0),
                'total_weighted_score' => (float) ($voteStats['total_weighted_score'] ?? 0),
                'avg_score' => isset($voteStats['total_votes']) && $voteStats['total_votes'] > 0
                    ? round((float) ($voteStats['total_weighted_score'] ?? 0) / (int) $voteStats['total_votes'], 2)
                    : null,
                'today_votes' => (int) $todayVotes,
                'yesterday_votes' => (int) $yesterdayVotes,
                'trend' => $trend,
            ],

            // Current round submission
            'submission' => $submission ? [
                'id' => $submission->id,
                'title' => $submission->title,
                'description' => $submission->description,
                'media_urls' => $submission->media_urls
                    ? collect($submission->media_urls)->map(fn($url) => asset('storage/' . $url))->toArray()
                    : [],
                'status' => $submission->status,
                'score' => $submission->score ? (float) $submission->score : null,
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
            ] : null,
        ];
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
    private function submissionForRound(int $roundId): ?\App\Models\Contest\RoundSubmission
    {
        return $this->submissions
            ->where('round_id', $roundId)
            ->first();
    }
}
