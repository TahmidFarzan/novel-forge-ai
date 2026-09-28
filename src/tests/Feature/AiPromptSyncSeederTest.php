<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\SeederHelper;
use App\Models\AiPrompt;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use Database\Seeders\AiPromptSyncSeeder;
use Database\Seeders\NovelGeneratorStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiPromptSyncSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['is_super_admin' => true]);
    }

    public function test_it_creates_exactly_the_three_generation_prompts(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame(3, AiPrompt::query()->count());

        $this->assertDatabaseHas('ai_prompts', [
            'name' => AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
            'step_number' => 2,
            'prompt' => AiPromptGeneratorHelper::planChapterPrompt(),
        ]);
    }

    public function test_the_stored_code_stays_derived_from_the_prompt_name(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $prompt = AiPrompt::query()
            ->where('name', AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER)
            ->firstOrFail();

        $this->assertSame(Str::studly($prompt->name), $prompt->code);
        $this->assertSame('PlanChapter', $prompt->code);
    }

    public function test_it_updates_the_stored_prompt_text(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $prompt = AiPrompt::query()->where('name', AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION)->firstOrFail();

        $prompt->update(['prompt' => 'stale prompt text']);

        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame(
            AiPromptGeneratorHelper::foundationPrompt(),
            $prompt->fresh()->prompt,
        );
    }

    public function test_it_preserves_ids_so_step_links_survive(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $before = AiPrompt::query()->pluck('id', 'name')->all();

        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame($before, AiPrompt::query()->pluck('id', 'name')->all());
    }

    public function test_it_keeps_existing_step_links_intact(): void
    {
        $this->seed(AiPromptSyncSeeder::class);
        $this->seed(NovelGeneratorStepSeeder::class);

        $this->assertSame(3, NovelGeneratorStep::query()->count());

        $links = NovelGeneratorStep::query()->pluck('ai_prompt_id', 'name')->all();

        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame($links, NovelGeneratorStep::query()->pluck('ai_prompt_id', 'name')->all());
    }

    public function test_it_does_not_delete_prompts_that_are_not_in_the_helper(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        AiPrompt::factory()->create([
            'name' => 'Custom Operator Prompt',
            'code' => 'CustomOperatorPrompt',
            'prompt' => 'hand written',
            'step_number' => 99,
        ]);

        $this->seed(AiPromptSyncSeeder::class);

        $this->assertDatabaseHas('ai_prompts', [
            'name' => 'Custom Operator Prompt',
            'prompt' => 'hand written',
        ]);
    }

    public function test_every_synchronised_prompt_carries_its_output_contract(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        foreach (AiPrompt::query()->get() as $prompt) {
            $this->assertStringContainsString('OUTPUT CONTRACT', $prompt->prompt, $prompt->name);
        }
    }

    public function test_the_generator_steps_are_seeded_in_pipeline_order(): void
    {
        $this->seed(AiPromptSyncSeeder::class);
        $this->seed(NovelGeneratorStepSeeder::class);

        $steps = NovelGeneratorStep::query()->orderBy('id')->get();

        $this->assertSame(
            [
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            ],
            $steps->pluck('name')->all(),
        );

        $this->assertNull($steps[0]->depend_on_step_ids);
        $this->assertSame([$steps[0]->id], $steps[1]->depend_on_step_ids);
        $this->assertSame([$steps[0]->id, $steps[1]->id], $steps[2]->depend_on_step_ids);

        $this->assertSame($steps[0]->id, $steps[1]->previous_step_id);
        $this->assertSame($steps[1]->id, $steps[2]->previous_step_id);
        $this->assertNull($steps[2]->next_step_id);

        foreach ($steps as $step) {
            $this->assertSame($step->name, $step->aiPrompt?->name);
        }
    }

    public function test_the_seeded_step_names_match_the_helper_prompts(): void
    {
        $stepNames = SeederHelper::novelGeneratorSteps()->pluck('name')->all();
        $promptNames = SeederHelper::aiPrompts()->pluck('name')->all();

        $this->assertSame($promptNames, $stepNames);
    }
}
