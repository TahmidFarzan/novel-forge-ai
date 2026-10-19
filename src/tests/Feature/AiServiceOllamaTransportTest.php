<?php

namespace Tests\Feature;

use App\Services\BackOffice\AiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiServiceOllamaTransportTest extends TestCase
{
    public function test_ollama_generate_requests_are_not_forced_to_stream(): void
    {
        Http::fake([
            'http://ollama:11434/api/generate' => Http::response([
                'response' => '{"title":"A"}',
                'done' => true,
            ], 200),
        ]);

        app(AiService::class)->generate(
            'Foundation',
            'http://ollama:11434',
            'ollama',
            'qwen3:4b',
            'Return only JSON.',
            [],
            100,
            30,
        );

        Http::assertSent(fn ($request) => $request->url() === 'http://ollama:11434/api/generate'
            && $request['stream'] === false);
    }
}
