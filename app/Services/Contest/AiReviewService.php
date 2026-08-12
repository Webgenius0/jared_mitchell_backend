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
    public function __construct(
        private AiService          $ai,
        private AiPromptBuilder    $promptBuilder,
        private AiResponseParser   $responseParser,
        private ConfidenceEngine   $confidenceEngine,
        private ContestantService  $contestantService,
    ) {}

    /**
     * Review a contest application using AI — runs synchronously.
     *
     * Called immediately after an application is submitted.
     * On success, auto-approves (creates a contestant), auto-rejects, or flags for admin.
     * On failure, the caller should mark the application as needs_review.
     */
    public function review(ContestApplication $application): AiReview
    {
        // Build the prompt
        $prompt       = $this->promptBuilder->build($application);
        $systemPrompt = $this->promptBuilder->systemPrompt();

        // Resolve which model to use
        $model = $this->resolveModel($application);

        // Call the AI provider
        $rawResponse = $this->ai->ask(
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            options: [
                'model'       => $model,
                'temperature' => 0.2,
                'max_tokens'  => 2048,
            ]
        );

        // Parse the response
        $parsed = $this->responseParser->parse($rawResponse);

        // Calculate confidence
        $completeness = $this->calculateCompleteness($application);
        $confidence   = $this->confidenceEngine->calculate($parsed, $completeness);

        $verdict = $parsed['verdict'];

        // Persist the review and process the verdict
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
                'ai_score'       => $parsed['score'],
                'ai_verdict'     => $verdict,
                'ai_confidence'  => $confidence,
            ]);

            // Process the verdict: auto-approve, auto-reject, or flag for admin
            if ($this->confidenceEngine->canAutoProcess($confidence)) {
                if ($verdict === 'approve') {
                    $this->contestantService->createFromApplication($application, $review);
                } else {
                    $application->update([
                        'status'          => 'rejected',
                        'rejected_reason' => 'AI review determined the application does not meet criteria. '
                            . ($review->review_notes ?? ''),
                    ]);
                }
            } else {
                // Not confident enough — flag for admin review
                $application->update(['status' => 'needs_review']);
            }

            // Fire event for notification
            ApplicationReviewed::dispatch($application, $review, $verdict);

            Log::info('AiReview completed', [
                'application_id' => $application->id,
                'verdict'        => $verdict,
                'confidence'     => $confidence,
                'score'          => $parsed['score'],
                'provider'       => $this->ai->provider(),
                'model'          => $model,
            ]);

            return $review;
        });
    }

    private function resolveModel(ContestApplication $application): string
    {
        $contestType = $application->season->contest_type ?? 'business';

        return match ($contestType) {
            'artist'   => config('ai.models.anthropic', 'claude-3-5-sonnet-20241022'),
            'startup'  => config('ai.models.openai', 'gpt-4o'),
            default    => $this->ai->defaultModel(),
        };
    }

    private function calculateCompleteness(ContestApplication $application): float
    {
        $business = $application->business;
        $fields = ['story', 'mission', 'community_impact_statement', 'revenue_stage', 'why_they_deserve_to_compete'];

        if (!$business) {
            return 0.5;
        }

        $filled = 0;
        $total = count($fields);

        foreach ($fields as $field) {
            $value = $business->{$field} ?? null;
            if (!empty($value)) {
                $filled++;
            }
        }

        return $total > 0 ? round($filled / $total, 2) : 0.5;
    }
}
