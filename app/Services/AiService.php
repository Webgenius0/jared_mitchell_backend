<?php

namespace App\Services;

use Anthropic\Laravel\Facades\Anthropic;
use Gemini\Data\Content;
use Gemini\Enums\Role as GeminiRole;
use Gemini\Laravel\Facades\Gemini;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

/**
 * Unified AI Service
 *
 * Provides a single interface for chat/text-generation across OpenAI,
 * Anthropic (Claude), and Google Gemini.  The active provider and
 * per-provider defaults are driven by config/ai.php (values come from .env).
 *
 * ─── Quick usage ────────────────────────────────────────────────────────────
 *
 *  // Via dependency injection (preferred)
 *  public function handle(AiService $ai)
 *  {
 *      $reply = $ai->ask('Summarise this text: ' . $text);
 *  }
 *
 *  // Multi-turn with a system prompt
 *  $reply = $ai->chat([
 *      ['role' => 'system',    'content' => 'You are a helpful assistant.'],
 *      ['role' => 'user',      'content' => 'Hello!'],
 *      ['role' => 'assistant', 'content' => 'Hi! How can I help?'],
 *      ['role' => 'user',      'content' => 'Explain black holes simply.'],
 *  ]);
 *
 *  // Override model per-call
 *  $reply = $ai->ask('Hello', options: ['model' => 'gpt-4-turbo']);
 *
 * ─── Supported providers ────────────────────────────────────────────────────
 *  'openai'    -> OpenAI GPT models   (config key: openai.api_key)
 *  'anthropic' -> Anthropic Claude    (config key: anthropic.api_key)
 *  'gemini'    -> Google Gemini       (config key: gemini.api_key)
 */
class AiService
{
    /*
    |--------------------------------------------------------------------------
    | Provider & Configuration Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * The currently configured AI provider.
     */
    public function provider(): string
    {
        return config('ai.provider', 'openai');
    }

    /**
     * The default model name for a given provider (or the active one).
     */
    public function defaultModel(?string $provider = null): string
    {
        $p = $provider ?? $this->provider();

        return config("ai.models.{$p}") ?? match ($p) {
            'anthropic' => 'claude-3-5-sonnet-20241022',
            'gemini'    => 'gemini-2.5-flash',
            default     => 'gpt-4o-mini',
        };
    }

    /**
     * Returns true when the active (or specified) provider has an API key set.
     */
    public function isConfigured(?string $provider = null): bool
    {
        $p = $provider ?? $this->provider();

        return (bool) match ($p) {
            'openai'    => config('openai.api_key'),
            'anthropic' => config('anthropic.api_key'),
            'gemini'    => config('gemini.api_key'),
            default     => false,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Primary Chat Interface
    |--------------------------------------------------------------------------
    */

    /**
     * Multi-turn chat using the standard message format:
     *
     *   [['role' => 'user|assistant|system', 'content' => '...']]
     *
     * Supported $options keys (all optional):
     *   'model'      - override the default model
     *   'max_tokens' - maximum tokens in the reply (required by Anthropic; optional elsewhere)
     *   'temperature'- sampling temperature (0–2 for OpenAI/Anthropic; 0–1 for Gemini)
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     *
     * @throws RuntimeException when the active provider has no API key configured.
     */
    public function chat(array $messages, array $options = []): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                "AI provider [{$this->provider()}] is not configured. "
                . 'Go to Settings → AI Platform and add the API key.'
            );
        }

        return match ($this->provider()) {
            'anthropic' => $this->chatAnthropic($messages, $options),
            'gemini'    => $this->chatGemini($messages, $options),
            default     => $this->chatOpenAi($messages, $options),
        };
    }

    /**
     * Single-turn shorthand.  Wraps a user prompt (and optional system prompt)
     * into a messages array and delegates to chat().
     *
     * @param  array<string, mixed>  $options
     */
    public function ask(string $prompt, ?string $systemPrompt = null, array $options = []): string
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->chat($messages, $options);
    }

    /*
    |--------------------------------------------------------------------------
    | Provider-Specific Drivers (private)
    |--------------------------------------------------------------------------
    */

    /**
     * OpenAI — uses the Chat Completions endpoint.
     * All message roles (system / user / assistant) are natively supported.
     */
    private function chatOpenAi(array $messages, array $options): string
    {
        $model     = $options['model'] ?? $this->defaultModel('openai');
        $maxTokens = $options['max_tokens'] ?? config('ai.max_tokens', 1024);

        unset($options['model'], $options['max_tokens']);

        $response = OpenAI::chat()->create([
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'messages'   => $messages,
            ...$options,
        ]);

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Anthropic (Claude) — uses the Messages endpoint.
     * Anthropic requires the system prompt as a separate top-level key,
     * not inside the messages array.
     */
    private function chatAnthropic(array $messages, array $options): string
    {
        $model     = $options['model'] ?? $this->defaultModel('anthropic');
        $maxTokens = $options['max_tokens'] ?? config('ai.max_tokens', 1024);

        unset($options['model'], $options['max_tokens']);

        // Extract system message — Anthropic keeps it as a separate parameter
        $systemText = null;
        $filtered   = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemText = $msg['content'];
            } else {
                $filtered[] = $msg;
            }
        }

        $payload = [
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'messages'   => $filtered,
            ...$options,
        ];

        if ($systemText !== null) {
            $payload['system'] = $systemText;
        }

        $response = Anthropic::messages()->create($payload);

        return $response->content[0]->text ?? '';
    }

    /**
     * Google Gemini — uses the GenerativeModel + ChatSession API.
     *
     * Message role mapping:
     *   'user'      → GeminiRole::USER
     *   'assistant' → GeminiRole::MODEL
     *   'system'    → withSystemInstruction() on the model
     *
     * Multi-turn: all messages except the final user message are passed as
     * chat history; the last message is sent via sendMessage().
     */
    private function chatGemini(array $messages, array $options): string
    {
        $modelName = $options['model'] ?? $this->defaultModel('gemini');

        unset($options['model']);

        // Separate system instruction and conversation turns
        $systemText = null;
        $turns      = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemText = $msg['content'];
            } else {
                $turns[] = $msg;
            }
        }

        // Build the GenerativeModel, optionally with a system instruction
        $model = Gemini::generativeModel($modelName);

        if ($systemText !== null) {
            $model = $model->withSystemInstruction(Content::parse($systemText));
        }

        // The final element must be a user message
        $lastTurn = array_pop($turns);

        // Build Content history from preceding turns
        $history = [];

        foreach ($turns as $turn) {
            $geminiRole = $turn['role'] === 'assistant' ? GeminiRole::MODEL : GeminiRole::USER;
            $history[]  = Content::parse($turn['content'], $geminiRole);
        }

        $chat     = $model->startChat(history: $history);
        $response = $chat->sendMessage($lastTurn['content']);

        return $response->candidates[0]->content->parts[0]->text ?? '';
    }
}
