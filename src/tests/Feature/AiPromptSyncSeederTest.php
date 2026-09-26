<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Models\AiPrompt;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use Database\Seeders\AiPromptSyncSeeder;
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

    public function test_it_creates_prompts_that_do_not_exist_yet(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame(16, AiPrompt::query()->count());

        $this->assertDatabaseHas('ai_prompts', [
            'name' => AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2,
            'step_number' => 2,
            'prompt' => AiPromptGeneratorHelper::step2Prompt(),
        ]);
    }

    public function test_the_stored_code_stays_derived_from_the_prompt_name(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $prompt = AiPrompt::query()
            ->where('name', AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP2)
            ->firstOrFail();

        $this->assertSame(Str::studly($prompt->name), $prompt->code);
    }

    public function test_it_updates_the_stored_prompt_text(): void
    {
        $this->seed(AiPromptSyncSeeder::class);

        $prompt = AiPrompt::query()->where('name', AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP1)->firstOrFail();

        $prompt->update(['prompt' => 'stale prompt text']);

        $this->seed(AiPromptSyncSeeder::class);

        $this->assertSame(
            AiPromptGeneratorHelper::step1Prompt(),
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

        foreach (NovelGeneratorStep::query()->get() as $step) {
            $step->update(['prompt' => 'stale']);
        }

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

        foreach (AiPrompt::query()->where('step_number', '<=', 15)->get() as $prompt) {
            $this->assertStringContainsString('OUTPUT CONTRACT', $prompt->prompt, $prompt->name);
        }

        $chapterContent = AiPrompt::query()
            ->where('name', AiPromptGeneratorHelper::AI_PROMPT_NAME_STEP16)
            ->firstOrFail();

        $this->assertStringNotContainsString('OUTPUT CONTRACT', $chapterContent->prompt);
    }
}
