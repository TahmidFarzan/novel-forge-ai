<?php

namespace Tests\Unit;

use App\Exceptions\AiResponseException;
use App\Helpers\AiPromptGeneratorHelper;
use App\Services\BackOffice\AiResponseService;
use Tests\TestCase;

class AiResponseServiceTest extends TestCase
{
    private AiResponseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AiResponseService;
    }

    public function test_it_returns_message_content(): void
    {
        $content = $this->service->contentFromApiResponse('Step', [
            'choices' => [
                ['message' => ['content' => '{"a":1}'], 'finish_reason' => 'stop'],
            ],
        ]);

        $this->assertSame('{"a":1}', $content);
    }

    public function test_it_rejects_a_missing_message_content(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service->contentFromApiResponse('Step', [
            'choices' => [['message' => [], 'finish_reason' => 'stop']],
        ]);
    }

    public function test_it_detects_truncation_before_parsing(): void
    {
        try {
            $this->service->contentFromApiResponse('Step', [
                'choices' => [
                    ['message' => ['content' => '{"novel_title":"A very long'], 'finish_reason' => 'length'],
                ],
            ]);

            $this->fail('Expected an AiResponseException for a truncated response.');
        } catch (AiResponseException $exception) {
            $this->assertTrue($exception->isRetryable());
            $this->assertSame('truncated', $exception->context()['reason']);
            $this->assertSame('length', $exception->context()['finish_reason']);
        }
    }

    public function test_it_strips_a_complete_code_fence(): void
    {
        $this->assertSame('{"a":1}', $this->service->normalize("```json\n{\"a\":1}\n```"));
        $this->assertSame('{"a":1}', $this->service->normalize("```\n{\"a\":1}\n```"));
    }

    public function test_it_keeps_an_unterminated_code_fence_rejectable(): void
    {
        $this->assertStringNotContainsString("\n```", $this->service->normalize("```json\n{\"a\":1}"));
    }

    public function test_it_extracts_json_wrapped_in_prose(): void
    {
        $this->assertSame('{"a":1}', $this->service->normalize("Here is the JSON you asked for:\n{\"a\":1}\nLet me know if you need changes."));
    }

    public function test_it_preserves_braces_inside_strings(): void
    {
        $payload = '{"a":"a } and { inside a string"}';

        $this->assertSame($payload, $this->service->normalize("Prose before.\n".$payload."\nProse after."));
    }

    public function test_it_preserves_escaped_quotes_inside_strings(): void
    {
        $payload = '{"a":"he said \\"stop\\" and left"}';

        $this->assertSame($payload, $this->service->normalize($payload));
    }

    public function test_it_rejects_unrepairable_json(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service->decode('{"a":1,}', 'Step');
    }

    public function test_it_reports_the_json_error_for_unrepairable_json(): void
    {
        try {
            $this->service->decode('not json at all', 'Step');

            $this->fail('Expected an AiResponseException.');
        } catch (AiResponseException $exception) {
            $this->assertTrue($exception->isRetryable());
            $this->assertArrayHasKey('content_preview', $exception->context());
            $this->assertStringContainsString('Step', $exception->getMessage());
        }
    }

    public function test_it_rejects_a_json_scalar(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service->decode('"just a string"', 'Step');
    }

    public function test_it_casts_numeric_strings_to_integers(): void
    {
        $decoded = $this->service->decode(
            '{"chapter_number":"7","title":"T"}',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
        );

        $this->assertSame(7, $decoded['chapter_number']);
    }

    public function test_it_leaves_non_numeric_values_untouched(): void
    {
        $decoded = $this->service->decode(
            '{"chapter_number":"seven","title":"T"}',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
        );

        $this->assertSame('seven', $decoded['chapter_number']);
    }

    public function test_it_casts_integers_inside_lists(): void
    {
        $decoded = $this->service->decode(
            '{"chapter_plan":[{"chapter_number":"1","title":"A","summary":"S","pacing_and_flow":"P","scenes":["x"],"chapter_goals":["g"]}]}',
            AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP13,
        );

        $this->assertSame(1, $decoded['chapter_plan'][0]['chapter_number']);
    }

    public function test_it_rejects_a_payload_missing_a_required_field(): void
    {
        $this->expectException(AiResponseException::class);

        $this->service->validate(['chapter_number' => 1, 'title' => 'T'], AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15);
    }

    public function test_it_rejects_an_empty_required_list(): void
    {
        try {
            $this->service->decodeAndValidate(
                '{"chapter_number":1,"chapter_title":"T","chapter_summary":{'
                .'"chapter_purpose":"p","progression":"g","emotional_and_narrative_movement":"e",'
                .'"chapter_ending_and_setup":"s","chapter_goals":[],"characters_involved":["a"],'
                .'"important_events":["b"],"relevant_conflicts":["c"],"important_revelations":["d"],'
                .'"scene_progression":["e"],"continuity_requirements":["f"]}}',
                AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP15,
            );

            $this->fail('Expected an AiResponseException for an empty required list.');
        } catch (AiResponseException $exception) {
            $this->assertTrue($exception->isRetryable());
            $this->assertStringContainsString('chapter_summary.chapter_goals', $exception->getMessage());
        }
    }
}
