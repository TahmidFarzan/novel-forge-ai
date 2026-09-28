<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use Tests\Concerns\InteractsWithPromptSkeleton;
use Tests\TestCase;

class FoundationPromptContractSyncTest extends TestCase
{
    use InteractsWithPromptSkeleton;

    public function test_the_foundation_prompt_contains_a_decodable_json_skeleton(): void
    {
        $skeleton = self::skeletonOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION);

        $this->assertNotSame([], $skeleton, 'The Foundation prompt must contain a decodable JSON skeleton.');
        $this->assertArrayHasKey('title', $skeleton);
        $this->assertArrayHasKey('sub_title', $skeleton);
        $this->assertArrayHasKey('world_bible', $skeleton);
    }

    public function test_the_plan_chapter_prompt_contains_a_decodable_json_skeleton(): void
    {
        $skeleton = self::skeletonOf(AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER);

        $this->assertNotSame([], $skeleton, 'The Plan Chapter prompt must contain a decodable JSON skeleton.');
        $this->assertArrayHasKey('chapter_plan', $skeleton);
    }

    public function test_the_prompts_guide_the_ai_to_return_only_json(): void
    {
        foreach ([
            AiPromptGeneratorHelper::foundationPrompt(),
            AiPromptGeneratorHelper::planChapterPrompt(),
            AiPromptGeneratorHelper::chapterContentPrompt(),
        ] as $prompt) {
            $this->assertStringContainsString('Return ONLY valid JSON.', $prompt);
            $this->assertMatchesRegularExpression('/valid json only/i', $prompt);
        }
    }

    public function test_the_prompts_do_not_demand_every_field_be_present(): void
    {
        foreach ([
            AiPromptGeneratorHelper::foundationPrompt(),
            AiPromptGeneratorHelper::planChapterPrompt(),
            AiPromptGeneratorHelper::chapterContentPrompt(),
        ] as $prompt) {
            $this->assertStringNotContainsString('Every field shown above must be present.', $prompt);
            $this->assertStringNotContainsString('Add fields that are not listed in the structure above.', $prompt);
            $this->assertStringNotContainsString('Rename, abbreviate, or nest any listed field.', $prompt);
            $this->assertStringNotContainsString('Never drop a field', $prompt);
        }
    }

    public function test_the_prompts_still_demand_strict_json_formatting(): void
    {
        foreach ([
            AiPromptGeneratorHelper::foundationPrompt(),
            AiPromptGeneratorHelper::planChapterPrompt(),
            AiPromptGeneratorHelper::chapterContentPrompt(),
        ] as $prompt) {
            $this->assertStringContainsString('Wrap the JSON in a Markdown code fence.', $prompt);
            $this->assertStringContainsString('Add trailing commas', $prompt);
            $this->assertStringContainsString('parseable by a standard JSON parser in a single pass', $prompt);
        }
    }
}
