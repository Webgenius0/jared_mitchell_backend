<?php

namespace App\Services\Contest;

use App\Models\Contest\Contestant;
use App\Models\Contest\RoundSubmission;
use App\Models\Round;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoundSubmissionService
{
    /**
     * Create or update a submission for a contestant in a round.
     *
     * If the contestant already has a submission for this round, it's updated.
     */
    public function submit(
        Contestant $contestant,
        Round      $round,
        string     $title,
        ?string    $description = null,
        ?array     $mediaUrls = null,
        ?array     $metadata = null,
    ): RoundSubmission {
        return DB::transaction(function () use ($contestant, $round, $title, $description, $mediaUrls, $metadata) {
            $submission = RoundSubmission::updateOrCreate(
                [
                    'contestant_id' => $contestant->id,
                    'round_id'      => $round->id,
                ],
                [
                    'title'       => $title,
                    'description' => $description,
                    'media_urls'  => $mediaUrls,
                    'status'      => 'submitted',
                    'submitted_at' => now(),
                    'metadata'    => $metadata,
                ]
            );

            Log::info('Round submission saved', [
                'contestant_id' => $contestant->id,
                'round_id'      => $round->id,
                'submission_id' => $submission->id,
                'title'         => $title,
            ]);

            return $submission;
        });
    }

    /**
     * Save a draft submission (not yet submitted).
     */
    public function saveDraft(
        Contestant $contestant,
        Round      $round,
        ?string    $title = null,
        ?string    $description = null,
        ?array     $mediaUrls = null,
    ): RoundSubmission {
        return RoundSubmission::updateOrCreate(
            [
                'contestant_id' => $contestant->id,
                'round_id'      => $round->id,
            ],
            [
                'title'      => $title,
                'description'=> $description,
                'media_urls' => $mediaUrls,
                'status'     => 'draft',
            ]
        );
    }

    /**
     * Get the current contestant's submission for a specific round.
     */
    public function getSubmission(Contestant $contestant, Round $round): ?RoundSubmission
    {
        return RoundSubmission::where('contestant_id', $contestant->id)
            ->where('round_id', $round->id)
            ->first();
    }

    /**
     * Get all submissions for a round, with contestant info eager-loaded.
     */
    public function submissionsForRound(Round $round)
    {
        return RoundSubmission::where('round_id', $round->id)
            ->with(['contestant.contestable'])
            ->orderBy('submitted_at')
            ->get();
    }

    /**
     * Check if a contestant has submitted for a given round.
     */
    public function hasSubmitted(Contestant $contestant, Round $round): bool
    {
        return RoundSubmission::where('contestant_id', $contestant->id)
            ->where('round_id', $round->id)
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();
    }

    /**
     * Approve a submission (admin/judge action).
     */
    public function approve(RoundSubmission $submission): void
    {
        $submission->approve();

        Log::info('Round submission approved', [
            'submission_id'  => $submission->id,
            'contestant_id'  => $submission->contestant_id,
            'round_id'       => $submission->round_id,
        ]);
    }

    /**
     * Reject a submission.
     */
    public function reject(RoundSubmission $submission, ?string $reason = null): void
    {
        $submission->update([
            'status'   => 'rejected',
            'metadata' => array_merge($submission->metadata ?? [], [
                'rejected_reason' => $reason,
                'rejected_at'     => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Round submission rejected', [
            'submission_id'  => $submission->id,
            'contestant_id'  => $submission->contestant_id,
            'round_id'       => $submission->round_id,
            'reason'         => $reason,
        ]);
    }
}
