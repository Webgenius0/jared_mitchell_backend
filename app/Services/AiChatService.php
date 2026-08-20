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
        $this->defaultSystemPrompt = "You are the OSI AI Content Assistant, an intelligent, friendly AI assistant for the OSI platform. You help users with questions about votes, contest rounds, events, artist and business spotlights, subscriptions, and general platform features. Keep your answers clear, helpful, and concise.";
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
