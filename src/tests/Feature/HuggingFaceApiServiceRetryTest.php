<?php

namespace Tests\Feature;

use App\Exceptions\AiResponseException;
use App\Helpers\AiPromptGeneratorHelper;
use App\Services\BackOffice\HuggingFaceApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HuggingFaceApiServiceRetryTest extends TestCase
{
    private const URL = 'https://router.huggingface.co/v1/chat/completions';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ai_generation.max_attempts' => 3,
            'services.ai_generation.retry_delay_ms' => 0,
        ]);
    }

    private function stepTwoPayload(): array
    {
        return [
            'characters' => [
                [
                    'name' => 'Ada', 'role' => 'protagonist', 'character_type' => 'human',
                    'personality' => 'curious', 'appearance_direction' => 'lean',
                    'background_direction' => 'engineer', 'motivation' => 'truth', 'goal' => 'escape',
                    'strengths' => ['focus'], 'weaknesses' => ['stubborn'],
                    'internal_conflict' => 'fear', 'external_conflict' => 'empire',
                    'relationship_to_main_character' => 'self', 'character_arc_direction' => 'grows',
                ],
            ],
            'relationships' => [
                [
                    'characters' => 'Ada and Bo', 'relationship' => 'siblings',
                    'story_purpose' => 'tension',
                ],
            ],
        ];
    }

    private function completion(string $content, string $finishReason = 'stop'): array
    {
        return [
            'choices' => [
                ['message' => ['content' => $content], 'finish_reason' => $finishReason],
            ],
        ];
    }

    public function test_it_returns_a_valid_response_on_the_first_attempt(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(1);

        $this->assertSame('Ada', $result['characters']['characters'][0]['name']);
        $this->assertSame('siblings', $result['characters']['relationships'][0]['relationship']);
    }

    public function test_it_accepts_a_valid_payload_wrapped_in_prose_on_the_first_attempt(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion(
                "Sure! Here is the CHARACTER generator output you requested:\n\n"
                .json_encode($this->stepTwoPayload(), JSON_PRETTY_PRINT)
                ."\n\nLet me know if you would like me to expand any character."
            ), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(1);

        $this->assertSame('Ada', $result['characters']['characters'][0]['name']);
        $this->assertSame('siblings', $result['characters']['relationships'][0]['relationship']);
    }

    public function test_it_retries_malformed_json_and_succeeds(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion('Here is the JSON: {"characters": ['), 200)
                ->push($this->completion("```json\n".json_encode($this->stepTwoPayload())."\n```"), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(2);

        $this->assertSame('Ada', $result['characters']['characters'][0]['name']);
    }

    public function test_it_retries_a_truncated_response(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push($this->completion('{"characters": [', 'length'), 200)
                ->push($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $result = $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(2);

        $this->assertSame('Ada', $result['characters']['characters'][0]['name']);
    }

    public function test_it_gives_up_after_the_configured_number_of_attempts(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion('this is not json'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an AiResponseException after exhausting retries.');
        } catch (AiResponseException $exception) {
            $this->assertTrue($exception->isRetryable());
            $this->assertSame(3, $exception->context()['attempts']);
            $this->assertStringContainsString('CHARACTER Generator', $exception->getMessage());
        }

        Http::assertSentCount(3);
    }

    public function test_it_honours_a_lower_attempt_limit(): void
    {
        config(['services.ai_generation.max_attempts' => 2]);

        Http::fake([
            self::URL => Http::response($this->completion('this is not json'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an AiResponseException.');
        } catch (AiResponseException $exception) {
            $this->assertSame(2, $exception->context()['attempts']);
        }

        Http::assertSentCount(2);
    }

    public function test_it_retries_a_server_error(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push(['error' => 'overloaded'], 503)
                ->push($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(2);
    }

    public function test_it_retries_a_rate_limit(): void
    {
        Http::fake([
            self::URL => Http::sequence()
                ->push(['error' => 'slow down'], 429)
                ->push($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSentCount(2);
    }

    public function test_it_does_not_retry_an_unauthorized_response(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'invalid token'], 401),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
                self::URL,
                'bad-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an AiResponseException.');
        } catch (AiResponseException $exception) {
            $this->assertFalse($exception->isRetryable());
            $this->assertSame(401, $exception->context()['status']);
        }

        Http::assertSentCount(1);
    }

    public function test_it_does_not_retry_a_bad_request(): void
    {
        Http::fake([
            self::URL => Http::response(['error' => 'unsupported model'], 400),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an AiResponseException.');
        } catch (AiResponseException $exception) {
            $this->assertFalse($exception->isRetryable());
            $this->assertSame(400, $exception->context()['status']);
        }

        Http::assertSentCount(1);
    }

    public function test_it_rejects_a_response_that_violates_the_step_contract(): void
    {
        config(['services.ai_generation.max_attempts' => 1]);

        Http::fake([
            self::URL => Http::response($this->completion('{"characters": [{"name": "Only a name"}]}'), 200),
        ]);

        try {
            $this->service()->sendPostRequest(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
                self::URL,
                'test-key',
                'test-model',
                'prompt',
            );

            $this->fail('Expected an AiResponseException for an incomplete structure.');
        } catch (AiResponseException $exception) {
            $this->assertStringContainsString('does not match the expected structure', $exception->getMessage());
            $this->assertContains('characters.0.role', $exception->context()['failed_keys']);
        }

        Http::assertSentCount(1);
    }

    public function test_it_omits_json_response_format_by_default(): void
    {
        Http::fake([
            self::URL => Http::response($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
            5000,
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return ! array_key_exists('response_format', $payload) && $payload['max_tokens'] === 5000;
        });
    }

    public function test_it_sends_json_response_format_when_enabled(): void
    {
        config(['services.ai_generation.json_response_format' => true]);

        Http::fake([
            self::URL => Http::response($this->completion(json_encode($this->stepTwoPayload())), 200),
        ]);

        $this->service()->sendPostRequest(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            self::URL,
            'test-key',
            'test-model',
            'prompt',
        );

        Http::assertSent(function (Request $request): bool {
            return $request->data()['response_format'] === ['type' => 'json_object'];
        });
    }

    private function service(): HuggingFaceApiService
    {
        return app(HuggingFaceApiService::class);
    }
}
