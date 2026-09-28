<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Services\BackOffice\HuggingFaceApiService;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class HuggingFaceApiServiceRequestTest extends TestCase
{
    use InteractsWithPromptSkeleton;

    private const URL = 'https://router.huggingface.co/v1/chat/completions';

    private function completion(string $content, string $finishReason = 'stop'): array
    {
        return [
            'choices' => [
                ['message' => ['content' => $content], 'finish_reason' => $finishReason],
            ],
        ];
    }

    private function foundation(): array
    {
        return self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);
    }

    private function foundationResponse(): array
    {
        return $this->completion((string) json_encode($this->foundation()));
    }

    public function test_a_valid_response_succeeds_with_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response($this->foundationResponse(), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(1);

        $this->assertSame($this->foundation()['novel_title'], $result['title']);
        $this->assertSame($this->foundation()['characters']['characters'][0]['name'], $result['characters']['characters'][0]['name']);
    }

    public function test_it_accepts_a_valid_payload_wrapped_in_prose(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion(
                "Sure! Here is the FOUNDATION generator output you requested:\n\n"
                .json_encode($this->foundation(), JSON_PRETTY_PRINT)
                ."\n\nLet me know if you would like me to expand any character."
            ), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(1);

        $this->assertSame($this->foundation()['novel_title'], $result['title']);
    }

    public function test_malformed_json_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion('Here is the JSON: {"novel_title": '), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception for a malformed response.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('was not valid JSON', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_truncated_response_fails_after_exactly_one_request(): void
    {
        Log::spy();

        Http::fake([
            self::URL => Http::response($this->completion('{"novel_title": "Cut off before the end', 'length'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception for a truncated response.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('cut off before it was complete', $exception->getMessage());
        }

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'AI response was truncated by the provider.'
                    && ($context['finish_reason'] ?? null) === 'length';
            });

        Http::assertSentCount(1);
    }

    public function test_a_non_json_response_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion('this is not json'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('was not valid JSON', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_rejected_response_is_never_replaced_by_a_second_request(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion('this is not json'), 200)
                ->push($this->foundationResponse(), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('was not valid JSON', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_partial_structure_is_accepted_and_never_replaced_by_a_second_request(): void
    {
        $sparse = ['novel_title' => 'The Salt Archive'];

        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion((string) json_encode($sparse)), 200)
                ->push($this->foundationResponse(), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        $this->assertSame('The Salt Archive', $result['title']);
        $this->assertSame([], $result['world_bible']);

        Http::assertSentCount(1);
    }

    public function test_a_response_without_a_title_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion('{"novel_subtitle":"Only a subtitle"}'), 200)
                ->push($this->foundationResponse(), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception for an unusable response.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('was not usable for this step', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_server_error_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push(['error' => 'overloaded'], 503)
                ->push($this->foundationResponse(), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('returned a server error (HTTP 503)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_rate_limit_fails_after_exactly_one_request(): void
    {
        Log::spy();

        Http::fake([
            self::URL => Http::sequence()
                ->push(['error' => 'slow down'], 429)
                ->push($this->foundationResponse(), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('rate limit was reached', $exception->getMessage());
        }

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'AI provider responded with an error.'
                    && ($context['status'] ?? null) === 429;
            });

        Http::assertSentCount(1);
    }

    public function test_an_unauthorized_response_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'invalid token'], 401),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'bad-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('rejected the request (HTTP 401)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_bad_request_fails_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'unsupported model'], 400),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('rejected the request (HTTP 400)', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_transport_failure_fails_after_exactly_one_request(): void
    {
        Log::spy();

        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;

            throw new RuntimeException('cURL error 28: Operation timed out');
        });

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('did not finish in time', $exception->getMessage());
            $this->assertStringNotContainsString('cURL', $exception->getMessage());
            $this->assertInstanceOf(RuntimeException::class, $exception->getPrevious());
        }

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'AI generation request failed.'
                    && ($context['timeout'] ?? 0) > 0;
            });

        $this->assertSame(1, $attempts);
    }

    public function test_a_connection_failure_fails_after_exactly_one_request(): void
    {
        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;

            throw new Exception('cURL error 6: Could not resolve host');
        });

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('could not be reached', $exception->getMessage());
        }

        $this->assertSame(1, $attempts);
    }

    public function test_it_rejects_a_truncated_response_after_exactly_one_request(): void
    {
        $payload = $this->foundation();
        unset($payload['characters']['characters'][0]['role']);

        Http::fake([
            self::URL => Http::response($this->completion((string) json_encode($payload), 'length'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception for a truncated response.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('cut off before it was complete', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_it_rejects_malformed_json_after_exactly_one_request(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion('{"novel_title": "Broken"'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an Exception for malformed JSON.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('was not valid JSON', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_generate_sends_the_full_prompt_with_the_token_budget_and_the_payload_limit(): void
    {
        Http::fake([
            self::URL => Http::response($this->foundationResponse(), 200),
        ]);

        $service = $this->service();

        $service->generate(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            self::URL,
            'test-key',
            'test-model',
            AiPromptGeneratorHelper::foundationPrompt(),
            $service->foundationInputs([]),
            8000,
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();
            $content = $payload['messages'][0]['content'];

            return $payload['max_tokens'] === 8000
                && str_contains($content, 'Maximum output tokens: 8000')
                && ! str_contains($content, '{{language}}');
        });
    }

    public function test_generate_never_sends_a_limit_that_is_not_a_positive_budget(): void
    {
        $service = $this->service();

        foreach ([null, 0, -10] as $budget) {
            Http::fake([
                self::URL => Http::response($this->foundationResponse(), 200),
            ]);

            $service->generate(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'The language is {{language}}.',
                ['language' => 'English'],
                $budget,
            );

            Http::assertSent(function (Request $request): bool {
                $payload = $request->data();

                return ! array_key_exists('max_tokens', $payload)
                    && ! str_contains($payload['messages'][0]['content'], 'Maximum output tokens');
            });
        }
    }

    public function test_generate_refuses_to_send_a_prompt_with_an_unresolved_placeholder(): void
    {
        Http::fake();

        $this->expectException(Exception::class);

        try {
            $this->service()->generate(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                self::URL,
                'test-key',
                'test-model',
                'The language is {{language}} and the genre is {{genre_prompt_instruction}}.',
                ['language' => 'English'],
                4000,
            );
        } finally {
            Http::assertNothingSent();
        }
    }

    private function service(): HuggingFaceApiService
    {
        return app(HuggingFaceApiService::class);
    }
}
