<?php

namespace App\Services\Spotlight;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Services\AiService;
use App\Services\Contest\AiResponseParser;
use Illuminate\Support\Facades\Log;

class SpotlightAiReviewService
{
    public function __construct(
        private AiService $ai,
        private AiResponseParser $responseParser,
    ) {}

    /**
     * Score a spotlight application with AI (0 - 100).
     *
     * Returns the score on success, or null when AI is not configured or
     * the review fails (the application is left untouched either way).
     */
    public function review(SpotlightApplication $application): ?float
    {
        if (! $this->ai->isConfigured()) {
            return null;
        }

        try {
            $rawResponse = $this->ai->ask(
                prompt: $this->buildPrompt($application),
                systemPrompt: $this->systemPrompt(),
                options: [
                    'temperature' => 0.2,
                    'max_tokens'  => 1024,
                ]
            );

            $parsed = $this->responseParser->parse($rawResponse);
            $score  = min(100, max(0, (float) $parsed['score']));

            $application->update([
                'ai_score'       => $score,
                'ai_reviewed_at' => now(),
            ]);

            Log::info('Spotlight AI review completed', [
                'application_id' => $application->id,
                'score'          => $score,
                'verdict'        => $parsed['verdict'],
            ]);

            return $score;
        } catch (\Throwable $e) {
            Log::warning('Spotlight AI review failed', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * System prompt: defines the scoring criteria and JSON output contract.
     */
    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert community spotlight judge for a platform that features local artists and small businesses. Evaluate the spotlight submission on four criteria:

1. QUALITY & CRAFT (0-30 points): Is the artist/business skilled, polished, and impressive in what they do?
2. STORY & AUTHENTICITY (0-25 points): Is the story compelling, genuine, and well told?
3. COMMUNITY IMPACT (0-25 points): Does the spotlight positively connect with and benefit its community?
4. COMPLETENESS (0-20 points): Is the application detailed, complete, and well presented?

Respond with valid JSON only (no markdown, no code fences) using this exact structure:
{
  "score": <0-100 integer>,
  "verdict": <"approve" | "reject" | "needs_review">,
  "confidence": <0.0-1.0>,
  "reasoning": "<2-3 sentence explanation>",
  "strengths": ["<strength 1>", "<strength 2>"],
  "weaknesses": ["<weakness 1>", "<weakness 2>"]
}

RULES:
- Approve if score >= 70 and no fatal flaws (e.g., fraudulent, illegal, hateful content).
- Reject if score < 50 or a fatal flaw exists.
- needs_review for scores between 50-69.
- Set confidence based on how complete the submission is (0.9+ fully detailed, 0.5 vague, 0.0 mostly empty).
PROMPT;
    }

    /**
     * Build the user prompt by injecting the spotlight data.
     */
    private function buildPrompt(SpotlightApplication $application): string
    {
        $spotlight = $application->spotlightable;

        if ($spotlight instanceof ArtistSpotlight) {
            $name     = $spotlight->artist_stage_name ?? $spotlight->full_legal_name ?? 'Unknown Artist';
            $bio      = $this->pick($spotlight, ['full_artist_story', 'short_bio', 'why_spotlighted']);
            $impact   = $this->pick($spotlight, ['community_message']);
            $goals    = $this->pick($spotlight, ['current_goals']);
            $extra    = $this->pick($spotlight, ['awards_recognition']);
            $category = $spotlight->category?->name ?? 'General';
            $location = trim(implode(', ', array_filter([$spotlight->city ?? '', $spotlight->state ?? '']))) ?: 'Not provided';

            return <<<PROMPT
Please evaluate this ARTIST spotlight application:

ARTIST: {$name}
LOCATION: {$location} / {$category}

BIO / STORY:
{$bio}

COMMUNITY MESSAGE:
{$impact}

CURRENT GOALS:
{$goals}

AWARDS & RECOGNITION:
{$extra}

Respond with the exact JSON structure specified in the system prompt.
PROMPT;
        }

        if ($spotlight instanceof BusinessSpotlight) {
            $name       = $spotlight->business_name ?? 'Unknown Business';
            $story      = $this->pick($spotlight, ['business_story', 'challenges_overcome']);
            $products   = $this->pick($spotlight, ['products_services', 'unique_factor']);
            $why        = $this->pick($spotlight, ['why_featured']);
            $growth     = $this->pick($spotlight, ['growth_vision']);
            $bizType    = $this->pick($spotlight, ['business_category', 'service_type']);
            $location   = trim(implode(', ', array_filter([$spotlight->city ?? '', $spotlight->state ?? '']))) ?: 'Not provided';

            return <<<PROMPT
Please evaluate this BUSINESS spotlight application:

BUSINESS: {$name}
LOCATION: {$location} / {$bizType}

STORY / CHALLENGES:
{$story}

PRODUCTS / UNIQUE FACTOR:
{$products}

WHY FEATURED:
{$why}

GROWTH VISION:
{$growth}

Respond with the exact JSON structure specified in the system prompt.
PROMPT;
        }

        return "Evaluate spotlight application #{$application->id}. Respond with the exact JSON structure specified in the system prompt.";
    }

    /**
     * Return the first non-empty field value as a string.
     */
    private function pick($model, array $fields): string
    {
        if (! $model) {
            return 'Not provided';
        }

        foreach ($fields as $field) {
            $value = $model->{$field} ?? null;

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            if (! empty($value)) {
                return is_string($value) ? $value : (string) $value;
            }
        }

        return 'Not provided';
    }
}
