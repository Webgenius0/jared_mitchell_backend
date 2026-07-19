<?php

namespace App\Services\Contest;

use App\Models\Contest\Contestant;
use App\Models\Contest\RoundSubmission;
use App\Models\Round;
use App\Helpers\FileHandle;
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
        Round $round,
        string $title,
        ?string $description = null,
        ?array $mediaFiles = null,
        ?array $metadata = null,
    ): RoundSubmission {
        return DB::transaction(function () use ($contestant, $round, $title, $description, $mediaFiles, $metadata) {
            $existingSubmission = RoundSubmission::where('contestant_id', $contestant->id)
                ->where('round_id', $round->id)
                ->first();

            $mediaUrls = $existingSubmission ? $existingSubmission->media_urls : [];

            if ($mediaFiles !== null) {
                // If new files are uploaded, delete old files
                if ($existingSubmission && !empty($existingSubmission->media_urls)) {
                    foreach ($existingSubmission->media_urls as $oldPath) {
                        FileHandle::fileDelete($oldPath);
                    }
                }

                $mediaUrls = [];
                foreach ($mediaFiles as $file) {
                    $path = FileHandle::fileUpload($file, 'submissions');
                    if ($path) {
                        $mediaUrls[] = $path;
                    }
                }
            }

            $submission = RoundSubmission::updateOrCreate(
                [
                    'contestant_id' => $contestant->id,
                    'round_id' => $round->id,
                ],
                [
                    'title' => $title,
                    'description' => $description,
                    'media_urls' => empty($mediaUrls) ? null : $mediaUrls,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'metadata' => $metadata,
                ]
            );

            Log::info('Round submission saved', [
                'contestant_id' => $contestant->id,
                'round_id' => $round->id,
                'submission_id' => $submission->id,
                'title' => $title,
            ]);

            return $submission;
        });
    }

    /**
     * Save a draft submission (not yet submitted).
     */
    public function saveDraft(
        Contestant $contestant,
        Round $round,
        ?string $title = null,
        ?string $description = null,
        ?array $mediaFiles = null,
    ): RoundSubmission {
        $existingSubmission = RoundSubmission::where('contestant_id', $contestant->id)
            ->where('round_id', $round->id)
            ->first();

        $mediaUrls = $existingSubmission ? $existingSubmission->media_urls : [];

        if ($mediaFiles !== null) {
            // If new files are uploaded, delete old files
            if ($existingSubmission && !empty($existingSubmission->media_urls)) {
                foreach ($existingSubmission->media_urls as $oldPath) {
                    FileHandle::fileDelete($oldPath);
                }
            }

            $mediaUrls = [];
            foreach ($mediaFiles as $file) {
                $path = FileHandle::fileUpload($file, 'submissions');
                if ($path) {
                    $mediaUrls[] = $path;
                }
            }
        }

        return RoundSubmission::updateOrCreate(
            [
                'contestant_id' => $contestant->id,
                'round_id' => $round->id,
            ],
            [
                'title' => $title,
                'description'=> $description,
                'media_urls' => empty($mediaUrls) ? null : $mediaUrls,
                'status' => 'draft',
            ]
        );
    }

    /**
     * Update an existing submission.
     */
    public function updateSubmission(
        RoundSubmission $submission,
        string $title,
        ?string $description = null,
        ?array $mediaFiles = null,
    ): RoundSubmission {
        return DB::transaction(function () use ($submission, $title, $description, $mediaFiles) {
            $mediaUrls = $submission->media_urls ?? [];

            if ($mediaFiles !== null) {
                // If new files are uploaded, delete old files
                if (!empty($submission->media_urls)) {
                    foreach ($submission->media_urls as $oldPath) {
                        FileHandle::fileDelete($oldPath);
                    }
                }

                $mediaUrls = [];
                foreach ($mediaFiles as $file) {
                    $path = FileHandle::fileUpload($file, 'submissions');
                    if ($path) {
                        $mediaUrls[] = $path;
                    }
                }
            }

            $submission->update([
                'title' => $title,
                'description' => $description,
                'media_urls' => empty($mediaUrls) ? null : $mediaUrls,
            ]);

            Log::info('Round submission updated', [
                'submission_id' => $submission->id,
                'title' => $title,
            ]);

            return $submission;
        });
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
