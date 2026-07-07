<?php

namespace App\Services\Contest;

class ConfidenceEngine
{
    const AUTO_APPROVE_THRESHOLD = 0.85;
    const FLAG_THRESHOLD         = 0.50;

    /**
     * Calculate how confident we should be in the AI's verdict.
     *
     * Factors:
     * 1. AI's self-reported confidence (from the prompt output)
     * 2. Score extremity (very high/very low = more confident)
     * 3. Mid-range penalty (45-65 range is uncertain territory)
     */
    public function calculate(array $parsedResult, float $completeness = 1.0): float
    {
        $confidence = $parsedResult['confidence'] ?? 0.5;

        // Factor 1: Score extremity bonus
        $score = $parsedResult['score'] ?? 50;
        if ($score >= 85 || $score <= 25) {
            $confidence = min(1.0, $confidence * 1.1);
        }

        // Factor 2: Mid-range penalty (uncertainty zone)
        if ($score >= 45 && $score <= 65) {
            $confidence *= 0.8;
        }

        // Factor 3: Application completeness penalty
        if ($completeness < 0.7) {
            $confidence *= 0.85;
        }

        return round(min(1.0, max(0.0, $confidence)), 2);
    }

    /**
     * Whether the AI can auto-process based on confidence.
     */
    public function canAutoProcess(float $confidence): bool
    {
        return $confidence >= self::AUTO_APPROVE_THRESHOLD;
    }

    /**
     * Whether the review needs an admin.
     */
    public function needsAdmin(float $confidence): bool
    {
        return $confidence < self::AUTO_APPROVE_THRESHOLD;
    }

    /**
     * Whether to retry with a different model (very low confidence).
     */
    public function shouldRetry(float $confidence): bool
    {
        return $confidence < self::FLAG_THRESHOLD;
    }
}
