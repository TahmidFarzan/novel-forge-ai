<?php

namespace Tests\Feature;

use App\Services\BackOffice\OllamaService;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaServiceTransportTest extends TestCase
{
    public function test_ollama_generate_requests_are_not_forced_to_stream(): void
    {
        Http::fake([
            'http://ollama:11434/api/generate' => Http::response([
                'response' => '{"title":"A"}',
                'done' => true,
            ], 200),
        ]);

        app(OllamaService::class)->generate(
            'Foundation',
            'http://ollama:11434',
            'qwen3:4b',
            'Return only JSON.',
            [],
            100,
            30,
        );

        Http::assertSent(fn ($request) => $request->url() === 'http://ollama:11434/api/generate'
            && $request['model'] === 'qwen3:4b'
            && $request['stream'] === false
            && ! $request->hasHeader('Authorization'));
    }

    public function test_a_missing_model_preserves_the_provider_status_and_names_the_model(): void
    {
        Http::fake([
            'http://ollama:11434/api/generate' => Http::response([
                'error' => "model 'qwen3:4b' not found",
            ], 404),
        ]);

        try {
            app(OllamaService::class)->sendPostRequest(
                'Foundation',
                'http://ollama:11434',
                'qwen3:4b',
                'Return only JSON.',
                null,
                30,
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertSame(404, $exception->getCode());
            $this->assertStringContainsString('[qwen3:4b]', $exception->getMessage());
            $this->assertStringContainsString('installed (HTTP 404)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }
}
