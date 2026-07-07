<?php

namespace App\Services\Contest;

use App\Models\Contest\AiReview;
use App\Models\ContestApplication;
use App\Services\AiService;
use App\Events\Contest\ApplicationReviewed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiReviewService
{
    const MAX_RETRIES = 2;

    public function __construct(
        private AiService          $ai,
        private AiPromptBuilder    $promptBuilder,
        private AiResponseParser   $responseParser,
        private ConfidenceEngine   $confidenceEngine,
        private ContestantService  $contestantService,
    ) {}

    /**
     * Review a contest application using AI.
     * Called by the ProcessAiReview queued job.
     */
    public function review(ContestApplication $application): AiReview
    {
        // 1. Build the prompt
        $prompt       = $this->promptBuilder->build($application);
        $systemPrompt = $this->promptBuilder->systemPrompt();

        // 2. Determine which model to use
        $model = $this->resolveModel($application);

        // 3. Call the AI provider
        $rawResponse = $this->ai->ask(
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: [
                'model'       => $model,
                'temperature' => 0.2,
                'max_tokens'  => 2048,
            ]
        );

        // 4. Parse the response
        $parsed = $this->responseParser->parse($rawResponse);

        // 5. Calculate confidence
        $completeness = $this->calculateCompleteness($application);
        $confidence   = $this->confidenceEngine->calculate($parsed, $completeness);

        // 6. Determine verdict
        $verdict = $parsed['verdict'];

        // 7. Persist the review
        return DB::transaction(function () use (
            $application, $parsed, $confidence, $verdict, $rawResponse, $model
        ) {
            $review = AiReview::create([
                'reviewable_type'  => 'contest_application',
                'reviewable_id'    => $application->id,
                'provider'         => $this->ai->provider(),
                'model'            => $model,
                'score'            => $parsed['score'],
                'verdict'          => $verdict,
                'confidence'       => $confidence,
                'raw_response'     => $rawResponse,
                'parsed_result'    => $parsed,
                'review_notes'     => $parsed['reasoning'] ?? null,
                'prompt_tokens'    => $parsed['usage']['prompt_tokens'] ?? null,
                'completion_tokens'=> $parsed['usage']['completion_tokens'] ?? null,
                'total_tokens'     => $parsed['usage']['total_tokens'] ?? null,
                'reviewed_at'      => now(),
            ]);

            // Update the application with AI review data
            $application->update([
                'ai_reviewed_at' => now(),
                'ai_verdict'     => $verdict,
                'ai_confidence'  => $confidence,
            ]);

            // Process the verdict
            $this->processVerdict($application, $review, $verdict, $confidence);

            // Fire event
            ApplicationReviewed::dispatch($application, $review, $verdict);

            Log::info('AiReview completed', [
                'application_id' => $application->id,
                'verdict'        => $verdict,
                'confidence'     => $confidence,
                'score'          => $parsed['score'],
                'provider'       => $this->ai->provider(),
                'model'          => $model,
                'tokens_used'    => $review->total_tokens,
            ]);

            return $review;
        });
    }

    /**
     * Retry with a different AI model/provider for low-confidence reviews.
     */
    public function retryWithDifferentModel(ContestApplication $application): ?AiReview
    {
        $retries = $application->metadata['ai_retries'] ?? 0;

        if ($retries >= self::MAX_RETRIES) {
            $this->forceFlagForAdmin($application);
            return null;
        }

        // Update retry count
        $metadata = $application->metadata ?? [];
        $metadata['ai_retries'] = $retries + 1;
        $application->update(['metadata' => $metadata]);

        return $this->review($application);
    }

    /**
     * Force-flag an application for admin after retries exhausted.
     */
    private function forceFlagForAdmin(ContestApplication $application): void
    {
        $application->update([
            'ai_reviewed_at' => now(),
            'ai_verdict'     => 'needs_review',
            'ai_confidence'  => 0.0,
            'status'         => 'needs_review',
        ]);

        Log::warning('AiReview: Max retries exceeded, flagged for admin', [
            'application_id' => $application->id,
        ]);
    }

    /**
     * Process the AI verdict: auto-approve, auto-reject, or flag for admin.
     */
    private function processVerdict(
        ContestApplication $application,
        AiReview $review,
        string $verdict,
        float $confidence
    ): void {
        if ($this->confidenceEngine->canAutoProcess($confidence)) {
            if ($verdict === 'approve') {
                $this->autoApprove($application, $review);
            } else {
                $this->autoReject($application, $review);
            }
        } elseif ($this->confidenceEngine->shouldRetry($confidence)) {
            // Low confidence — retry with different model
            $this->retryWithDifferentModel($application);
        } else {
            // Flag for admin review
            $application->update(['status' => 'needs_review']);
        }
    }

    /**
     * Auto-approve an application and create a contestant.
     */
    private function autoApprove(ContestApplication $application, AiReview $review): void
    {
        $this->contestantService->createFromApplication(
            $application,
            $review
        );
    }

    /**
     * Auto-reject an application.
     */
    private function autoReject(ContestApplication $application, AiReview $review): void
    {
        $application->update([
            'status'          => 'rejected',
            'rejected_reason' => 'AI review determined the application does not meet criteria. '
                . $review->review_notes,
        ]);
    }

    /**
     * Resolve which AI model to use for a given contest type.
     */
    private function resolveModel(ContestApplication $application): string
    {
        $contestType = $application->season->contest_type ?? 'business';

        return match ($contestType) {
            'artist'   => config('ai.models.anthropic', 'claude-3-5-sonnet-20241022'),
            'startup'  => config('ai.models.openai', 'gpt-4o'),
            default    => $this->ai->defaultModel(),
        };
    }

    /**
     * Calculate how complete the application is (0.0 - 1.0).
     * Based on how many fields are filled in the contestable entity.
     */
    private function calculateCompleteness(ContestApplication $application): float
    {
        $contestable = $application->contestable;
        $fields = ['story', 'mission', 'community_impact_statement', 'revenue_stage', 'why_they_deserve_to_compete'];

        if (!$contestable) {
            return 0.5;
        }

        $filled = 0;
        $total = count($fields);

        foreach ($fields as $field) {
            $value = $contestable->{$field} ?? null;
            if (!empty($value)) {
                $filled++;
            }
        }

        return $total > 0 ? round($filled / $total, 2) : 0.5;
    }
}
