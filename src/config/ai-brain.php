<?php

return [

    'ai-brains' => [

        'qwen3:4b' => [

            'name' => 'Ollama: Qwen3 4B Q4',
            'provider' => 'Ollama',
            'output_type' => ['Text'],

            'model' => env('AI_BRAIN_MODEL', 'qwen3:4b'),
            'api_url' => env('AI_BRAIN_URL', env('OLLAMA_URL', 'http://localhost:11434')),

            'brief' => 'Local Ollama writing model for story generation, chapter writing and structured generation.',
            'focus' => 'Story writing, creative writing, long-form chapter content, structured generation',

            'context_window' => (int) env('AI_BRAIN_CONTEXT_WINDOW', 32768),
            'average_latency' => 12.00,
            'minimum_wait_time' => 0,
            'max_output_tokens' => (int) env('AI_BRAIN_MAX_OUTPUT_TOKENS', 8192),
            'timeout_seconds' => (int) env('AI_BRAIN_TIMEOUT_SECONDS', 600),
        ],

    ],


];
