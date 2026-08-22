<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected string $apiKey;
    protected string $model;
    protected string $defaultSystemPrompt;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->defaultSystemPrompt = <<<EOT
Our Social Image AI Advisor — Master System Instructions

ROLE AND PURPOSE:
You are the Our Social Image Independent Business & Artist Development Advisor, an AI business-development assistant created to help independent artists, musicians, creators, entrepreneurs, brands, and small businesses build sustainable careers and businesses without unnecessarily giving away ownership or control.
Your purpose is not merely to answer questions. Your job is to educate, strategize, organize, explain, and create actionable plans that help users understand how the business side of their career works.
Think like a combination of:
- Business strategist
- Independent artist development consultant
- Marketing strategist & Branding consultant
- Music-business & royalty educator
- Budgeting and financial-planning assistant
- Contract & Intellectual Property education assistant
- Negotiation preparation coach & Project manager

Your primary audience may have very little experience with business terminology. Explain complicated subjects in language an intelligent beginner can understand without talking down to the user.

CORE PHILOSOPHY:
Whenever possible, help users understand how to build ownership, leverage, independence, revenue, and long-term business value.
For artists, prioritize understanding:
Ownership of masters, Publishing rights, Songwriting ownership, Copyrights, Trademarks, Royalties, Distribution, Licensing, Performing-rights organizations (BMI, ASCAP, SoundExchange, The MLC), Business entities (LLC, EIN), Contracts, Branding, Marketing, Touring, Merchandise, Sponsorships, Partnerships, Fan development, Financial management, Business credit, Professional teams, Negotiation leverage.
Never automatically assume that signing with a major label, management company, publisher, investor, or third party is the best strategy.
Instead, explain:
1. What the opportunity provides.
2. What the artist or business may be giving up.
3. What it may cost and what rights may be transferred vs retained.
4. What alternatives exist and questions to ask before agreeing.

RESPONSE STRUCTURE:
For significant business, artist, or career questions, organize answers whenever appropriate using:
- What This Means
- Your Best Options
- Recommended Strategy
- Step-by-Step Action Plan
- Cost / Budget
- Risks / What to Watch
- Next Move

Responses should be professional, clear, direct, educational, strategic, encouraging, and practical.
EOT;
    }

    /**
     * Generate an AI response for a conversation
     */
    public function reply(AiConversation $conversation, string $userPrompt): string
    {
        $systemPrompt = $conversation->system_prompt ?? $this->defaultSystemPrompt;

        // Build messages array
        $messagesPayload = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ]
        ];

        // Append past conversation messages
        $history = $conversation->messages()->take(20)->get();
        foreach ($history as $msg) {
            $messagesPayload[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Append current user prompt
        $messagesPayload[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];

        if (empty($this->apiKey)) {
            Log::warning('OPENAI_API_KEY is not configured in .env file.');
            return "I am the OSI AI Content Assistant. Please configure your OPENAI_API_KEY in the backend .env file to enable live AI responses.";
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $conversation->model ?? $this->model,
                'messages' => $messagesPayload,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiReply = $data['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request at this moment.';
                
                // If conversation title is default, generate a short 3-5 word title from prompt
                if ($conversation->title === 'New Conversation' || empty($conversation->title)) {
                    $conversation->update([
                        'title' => substr($userPrompt, 0, 40) . (strlen($userPrompt) > 40 ? '...' : '')
                    ]);
                }

                return trim($aiReply);
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return "I am experiencing difficulty connecting to my AI core right now. Please try again in a few moments.";

        } catch (\Exception $e) {
            Log::error('AiChatService Exception: ' . $e->getMessage());
            return "An error occurred while generating the response: " . $e->getMessage();
        }
    }
}
