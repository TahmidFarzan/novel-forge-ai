<?php

return [

    'default' => 'qwen3:4b',

    "ai-brains" => [

        "qwen3:4b" => [

            'name' => 'Ollama: Qwen3 4B Q4',

            'provider' => 'Ollama',

            'output_type' => [
                'Text'
            ],

            'model' => 'qwen3:4b',

            'api_url' => env('AI_BRAIN_URL', env('OLLAMA_URL', 'http://localhost:11434')),

            // AI Brains require a non-empty API key value. Local Ollama ignores
            // the Authorization header, so any placeholder works.
            'api_key' => env('AI_BRAIN_API_KEY', 'ollama'),

            'brief' => 'Local Ollama writing model for story generation, chapter writing and structured generation.',

            'focus' => 'Story writing, creative writing, long-form chapter content, structured generation',

            'context_window' => 32768,

            'average_latency' => 12.00,

            'minimum_wait_time' => 0,

            'timeout_seconds' => 600,

            'max_output_tokens' => 32000,

        ],

    ],

];
