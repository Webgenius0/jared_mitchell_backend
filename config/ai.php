<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active AI Provider
    |--------------------------------------------------------------------------
    |
    | Controls which AI provider the application uses. Change via the admin
    | Settings > AI Platform page. Supported: "openai", "anthropic", "gemini"
    |
    */

    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Default Models
    |--------------------------------------------------------------------------
    |
    | The default model used for each provider when no model is specified in
    | the chat/ask call. These can be overridden per-call via $options['model'].
    |
    */

    'models' => [
        'openai'    => env('AI_OPENAI_MODEL',    'gpt-4o'),
        'anthropic' => env('AI_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'gemini'    => env('AI_GEMINI_MODEL',    'gemini-1.5-pro'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Max Tokens
    |--------------------------------------------------------------------------
    |
    | Anthropic requires max_tokens to be explicitly set. This value also
    | applies to OpenAI and Gemini unless overridden per-call.
    |
    */

    'max_tokens' => (int) env('AI_MAX_TOKENS', 1024),

];
