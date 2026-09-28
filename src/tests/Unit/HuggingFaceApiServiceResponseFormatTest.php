<?php

namespace Tests\Unit;

use App\Helpers\AiPromptGeneratorHelper;
use App\Services\BackOffice\HuggingFaceApiService;
use Exception;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class HuggingFaceApiServiceResponseFormatTest extends TestCase
{
    use InteractsWithPromptSkeleton;

    private function completion(string $content, string $finishReason = 'stop'): array
    {
        return [
            'choices' => [
                ['message' => ['content' => $content], 'finish_reason' => $finishReason],
            ],
        ];
    }

    private function service(): HuggingFaceApiService
    {
        return app(HuggingFaceApiService::class);
    }

    public function test_the_foundation_maps_every_persisted_column(): void
    {
        $payload = self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame([
            'title',
            'sub_title',
            'foundation',
            'characters',
            'world_bible',
            'locations',
            'factions',
            'creatures',
            'systems',
            'timeline',
        ], array_keys($result));

        $this->assertSame($payload['title'], $result['title']);
        $this->assertSame($payload['sub_title'], $result['sub_title']);
        $this->assertSame($payload['novel_foundation'], $result['foundation']);
        $this->assertSame(['characters', 'relationships'], array_keys($result['characters']));
        $this->assertSame($payload['world_bible'], $result['world_bible']);
    }

    public function test_the_foundation_accepts_a_payload_missing_optional_fields(): void
    {
        $payload = self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);

        unset(
            $payload['novel_foundation']['premise'],
            $payload['world_bible'],
            $payload['locations'],
            $payload['factions'],
        );

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame($payload['title'], $result['title']);
        $this->assertSame([], $result['world_bible']);
        $this->assertSame([], $result['locations']);
        $this->assertSame([], $result['factions']);
    }

    public function test_the_foundation_accepts_genre_specific_content_the_prompt_never_mentioned(): void
    {
        $payload = ['title' => 'The Salt Archive'];

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame('The Salt Archive', $result['title']);
        $this->assertSame('', $result['sub_title']);
        $this->assertSame([], $result['foundation']);
        $this->assertSame([], $result['world_bible']);
    }

    public function test_the_foundation_rejects_a_payload_without_a_title(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            $this->completion((string) json_encode(['sub_title' => 'No title here'])),
        );
    }

    public function test_the_foundation_rejects_a_blank_title(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
            $this->completion('{"title":"   "}'),
        );
    }

    public function test_the_plan_chapter_maps_every_persisted_column(): void
    {
        $payload = self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame([
            'story_structure',
            'twists_and_foreshadowing',
            'scene_plans',
            'dialogue_plans',
            'chapter_plan',
            'page_plan',
            'chapter_summaries',
        ], array_keys($result));

        $this->assertSame($payload['story_structure'], $result['story_structure']);
        $this->assertCount(1, $result['chapter_plan']);
        $this->assertCount(1, $result['chapter_summaries']);
        $this->assertSame($payload['chapter_summaries'][0]['chapter_summary'], $result['chapter_summaries'][0]['chapter_summary']);
    }

    public function test_the_plan_chapter_accepts_a_payload_without_chapter_summaries(): void
    {
        $payload = self::validPayloadOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);
        $payload['chapter_summaries'] = [];

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame([], $result['chapter_summaries']);
        $this->assertCount(1, $result['chapter_plan']);
    }

    public function test_the_plan_chapter_accepts_a_completely_different_genre_shape(): void
    {
        $payload = [
            'story_structure' => [
                'outline' => [
                    ['title' => 'Opening', 'summary' => 'A courier is handed a sealed letter.'],
                ],
            ],
            'creative_direction' => [
                'tone' => 'dark',
            ],
            'chapter_plan' => [
                ['chapter_number' => 1, 'title' => 'The Letter'],
            ],
        ];

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame($payload['story_structure'], $result['story_structure']);
        $this->assertSame([], $result['twists_and_foreshadowing']);
        $this->assertSame([], $result['scene_plans']);
        $this->assertSame([], $result['dialogue_plans']);
        $this->assertSame([], $result['page_plan']);
        $this->assertSame($payload['chapter_plan'], $result['chapter_plan']);
    }

    public function test_the_plan_chapter_accepts_plan_entries_with_no_nested_fields(): void
    {
        $payload = [
            'chapter_plan' => [
                ['chapter_number' => 1, 'title' => 'Opening'],
            ],
        ];

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            $this->completion((string) json_encode($payload)),
        );

        $this->assertSame([['chapter_number' => 1, 'title' => 'Opening']], $result['chapter_plan']);
    }

    public function test_the_plan_chapter_rejects_a_payload_without_a_chapter_plan(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            $this->completion('{"story_structure":{"outline":[]},"creative_direction":{"tone":"dark"}}'),
        );
    }

    public function test_the_chapter_content_returns_the_documented_json(): void
    {
        $prose = "The rain had not stopped for nine days.\n\nAda counted the cracks in the ceiling.";

        $result = $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            $this->completion((string) json_encode(['chapter_content' => $prose])),
        );

        $this->assertSame(['chapter_content' => $prose], $result);
    }

    public function test_the_chapter_content_rejects_bare_prose(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            $this->completion('The rain had not stopped for nine days.'),
        );
    }

    public function test_the_chapter_content_rejects_an_empty_field(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            $this->completion('{"chapter_content":"   "}'),
        );
    }

    public function test_the_chapter_content_rejects_an_empty_response(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats(
            AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            $this->completion('   '),
        );
    }

    public function test_the_chapter_content_rejects_a_truncated_response(): void
    {
        try {
            $this->service()->aiResponseFormats(
                AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
                $this->completion('{"chapter_content":"The rain had not stopped for nine days. Ada coun', 'length'),
            );

            $this->fail('Expected an Exception for a truncated chapter.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('cut off before it was complete', $exception->getMessage());
        }
    }

    public function test_it_rejects_an_unknown_step_name(): void
    {
        $this->expectException(Exception::class);

        $this->service()->aiResponseFormats('Mystery Step', $this->completion('{"a":1}'));
    }
}
