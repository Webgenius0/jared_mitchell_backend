<?php

namespace App\Services\Contest;

use App\Models\ContestApplication;

class AiPromptBuilder
{
    /**
     * The system prompt that defines the AI's persona and behavior.
     */
    public function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert contest judge for "Boss Beginnings," a business competition platform
similar to Shark Tank. You evaluate business applications based on five criteria:

1. VIABILITY (0-30 points): Does the business have a clear revenue model and market?
2. STORY & MISSION (0-25 points): Is the founder's story compelling and authentic?
3. COMMUNITY IMPACT (0-20 points): Does the business positively impact its community?
4. GROWTH POTENTIAL (0-15 points): Can this business scale?
5. PRESENTATION (0-10 points): Is the application well-written and complete?

You MUST respond with valid JSON only (no markdown, no code fences) using this exact structure:
{
  "score": <0-100 integer>,
  "verdict": <"approve" | "reject" | "needs_review">,
  "confidence": <0.0-1.0>,
  "reasoning": "<2-3 sentence explanation>",
  "strengths": ["<strength 1>", "<strength 2>"],
  "weaknesses": ["<weakness 1>", "<weakness 2>"],
  "criteria_scores": {
    "viability": <0-30>,
    "story_mission": <0-25>,
    "community_impact": <0-20>,
    "growth_potential": <0-15>,
    "presentation": <0-10>
  }
}

RULES:
- Approve if score >= 70 AND no fatal flaws (e.g., illegal business, fraudulent claims)
- Reject if score < 50 OR has a fatal flaw
- needs_review for scores between 50-69 OR if you're uncertain
- Set confidence based on how complete the application is:
  0.9-1.0: Fully detailed, all fields complete
  0.7-0.9: Most fields filled, reasonable detail
  0.5-0.7: Some fields missing or vague
  0.0-0.5: Significant missing information or contradictory data
PROMPT;
    }

    /**
     * Build the user prompt by injecting contest application data.
     * Handles polymorphic contestable entities (Business, User, etc.).
     */
    public function build(ContestApplication $application): string
    {
        $contestable = $application->contestable;

        $name   = method_exists($contestable, 'getContestantName')
            ? $contestable->getContestantName()
            : 'Unknown';

        $story  = $this->extractField($contestable, ['story', 'business_story', 'full_artist_story', 'bio']);
        $mission = $this->extractField($contestable, ['mission', 'why_featured', 'current_goals', 'why_spotlighted']);
        $impact = $this->extractField($contestable, ['community_impact_statement', 'community_message']);
        $revenue = $this->extractField($contestable, ['revenue_stage', 'growth_vision']);
        $pitch  = $this->extractField($contestable, ['why_they_deserve_to_compete', 'why_featured']);

        return <<<PROMPT
Please evaluate this contest application:

APPLICANT: {$name}

STORY:
{$story}

MISSION / MOTIVATION:
{$mission}

COMMUNITY IMPACT:
{$impact}

REVENUE & GROWTH:
{$revenue}

PITCH / WHY THEY DESERVE TO COMPETE:
{$pitch}

Respond with the exact JSON structure specified in the system prompt.
PROMPT;
    }

    /**
     * Extract the first non-empty field value from a model.
     */
    private function extractField($model, array $fields): string
    {
        foreach ($fields as $field) {
            $value = $model->{$field} ?? null;
            if (!empty($value)) {
                return is_string($value) ? $value : (string) $value;
            }
        }

        return 'Not provided';
    }
}
