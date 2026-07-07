<?php

namespace App\Services\Contest;

use Illuminate\Support\Facades\Log;

class AiResponseParser
{
    /**
     * Parse the raw AI response into structured data.
     * Handles: pure JSON, JSON in code fences, markdown-wrapped JSON, corrupted JSON.
     *
     * @return array{score: int, verdict: string, confidence: float, reasoning: string, strengths: array, weaknesses: array, criteria_scores: array, usage: array}
     */
    public function parse(string $rawResponse): array
    {
        // 1. Try direct JSON decode first
        $result = json_decode($rawResponse, true);
        if ($this->isValid($result)) {
            return $this->normalize($result);
        }

        // 2. Try extracting from markdown code fences ```json ... ```
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $rawResponse, $matches)) {
            $result = json_decode($matches[1], true);
            if ($this->isValid($result)) {
                return $this->normalize($result);
            }
        }

        // 3. Try finding a JSON block containing "score" anywhere
        if (preg_match('/\{[^{}]*"score"[^{}]*\}/s', $rawResponse, $matches)) {
            $result = json_decode($matches[0], true);
            if ($this->isValid($result)) {
                return $this->normalize($result);
            }
        }

        // 4. Try a more lenient regex for JSON with nested objects
        if (preg_match('/\{.*"score".*"verdict".*\}/s', $rawResponse, $matches)) {
            $result = json_decode($matches[0], true);
            if ($this->isValid($result)) {
                return $this->normalize($result);
            }
        }

        // 5. Last resort: extract score via regex, build minimal response
        Log::warning('AiReviewParser: Failed to parse AI response as JSON', [
            'raw' => substr($rawResponse, 0, 500),
        ]);

        return $this->fallbackParse($rawResponse);
    }

    /**
     * Validate the parsed result has required fields.
     */
    private function isValid(?array $result): bool
    {
        return $result !== null
            && isset($result['score'])
            && isset($result['verdict']);
    }

    /**
     * Normalize inconsistent AI responses to a standard format.
     */
    private function normalize(array $result): array
    {
        return [
            'score'           => (int) ($result['score'] ?? 0),
            'verdict'         => $this->normalizeVerdict($result['verdict'] ?? 'needs_review'),
            'confidence'      => (float) ($result['confidence'] ?? 0.5),
            'reasoning'       => $result['reasoning'] ?? $result['review_notes'] ?? '',
            'strengths'       => $result['strengths'] ?? [],
            'weaknesses'      => $result['weaknesses'] ?? [],
            'criteria_scores' => $result['criteria_scores'] ?? [],
            'usage'           => $result['usage'] ?? [],
        ];
    }

    /**
     * Normalize various verdict strings to one of three values.
     */
    private function normalizeVerdict(string $verdict): string
    {
        $verdict = strtolower(trim($verdict));

        return match (true) {
            in_array($verdict, ['approve', 'approved', 'accept', 'accepted', 'yes', 'pass']) => 'approve',
            in_array($verdict, ['reject', 'rejected', 'deny', 'denied', 'no', 'fail'])         => 'reject',
            default => 'needs_review',
        };
    }

    /**
     * When all parsing fails, extract what we can via regex.
     * Returns a low-confidence result that will trigger admin review.
     */
    private function fallbackParse(string $raw): array
    {
        $score = 50;

        if (preg_match('/score[:\s]+(\d+)/i', $raw, $m)) {
            $score = (int) $m[1];
        }

        $verdict = match (true) {
            $score >= 70 => 'approve',
            $score < 50  => 'reject',
            default      => 'needs_review',
        };

        return [
            'score'           => min(100, max(0, $score)),
            'verdict'         => $verdict,
            'confidence'      => 0.25, // Low because parsing was unreliable
            'reasoning'       => 'Parsed from unstructured response: ' . substr($raw, 0, 200),
            'strengths'       => [],
            'weaknesses'      => [],
            'criteria_scores' => [],
            'usage'           => [],
        ];
    }
}
