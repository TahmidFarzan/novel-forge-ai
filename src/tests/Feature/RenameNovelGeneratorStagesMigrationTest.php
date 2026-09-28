<?php

namespace Tests\Feature;

use App\Helpers\AiPromptGeneratorHelper;
use App\Helpers\NovelHelper;
use App\Models\AiPrompt;
use App\Models\Audience;
use App\Models\Language;
use App\Models\Novel;
use App\Models\NovelGeneratorStep;
use App\Models\NovelType;
use App\Models\User;
use Database\Seeders\AudienceSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\NovelTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenameNovelGeneratorStagesMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const LEGACY_NAMES = [
        'Story Package Generator',
        'Plan Package Generator',
        'Chapter Content Generator',
    ];

    private const EXPECTED_NAMES = [
        'Foundation',
        'Plan Chapter',
        'Chapter Content',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['is_super_admin' => true]);
    }

    public function test_it_renames_the_persisted_prompts_and_steps(): void
    {
        $this->seedLegacyStages();

        $this->migration()->up();

        $this->assertSame(3, AiPrompt::query()->count());
        $this->assertSame(3, NovelGeneratorStep::query()->count());

        $this->assertSame(
            self::EXPECTED_NAMES,
            AiPrompt::query()->orderBy('id')->pluck('name')->all(),
        );

        $this->assertSame(
            self::EXPECTED_NAMES,
            NovelGeneratorStep::query()->orderBy('id')->pluck('name')->all(),
        );

        $this->assertSame(
            [
                AiPromptGeneratorHelper::AI_PROMPT_NAME_FOUNDATION,
                AiPromptGeneratorHelper::AI_PROMPT_NAME_PLAN_CHAPTER,
                AiPromptGeneratorHelper::AI_PROMPT_NAME_CHAPTER_CONTENT,
            ],
            NovelGeneratorStep::query()->orderBy('id')->pluck('name')->all(),
        );
    }

    public function test_it_updates_the_derived_slug_and_code_columns(): void
    {
        $this->seedLegacyStages();

        $this->migration()->up();

        $this->assertDatabaseHas('ai_prompts', [
            'name' => 'Foundation',
            'slug' => 'foundation',
            'code' => 'Foundation',
        ]);

        $this->assertDatabaseHas('ai_prompts', [
            'name' => 'Plan Chapter',
            'slug' => 'plan-chapter',
            'code' => 'PlanChapter',
        ]);

        $this->assertDatabaseHas('novel_generator_steps', [
            'name' => 'Foundation',
            'slug' => 'foundation',
        ]);

        $this->assertDatabaseHas('novel_generator_steps', [
            'name' => 'Chapter Content',
            'slug' => 'chapter-content',
        ]);
    }

    public function test_it_preserves_ids_and_prompt_links(): void
    {
        $before = $this->seedLegacyStages();

        $this->migration()->up();

        $this->assertSame(
            $before['prompt_ids'],
            AiPrompt::query()->orderBy('id')->pluck('id')->all(),
        );

        $this->assertSame(
            $before['step_ids'],
            NovelGeneratorStep::query()->orderBy('id')->pluck('id')->all(),
        );

        $this->assertSame(
            $before['step_prompt_ids'],
            NovelGeneratorStep::query()->orderBy('id')->pluck('ai_prompt_id')->all(),
        );

        $this->assertSame(
            $before['step_dependency_ids'],
            NovelGeneratorStep::query()->orderBy('id')->pluck('depend_on_step_ids')->all(),
        );
    }

    public function test_it_preserves_generated_novel_progress_and_content(): void
    {
        $before = $this->seedLegacyStages();

        $this->seed([AudienceSeeder::class, NovelTypeSeeder::class, LanguageSeeder::class]);

        $progress = [
            $before['step_ids'][0] => ['status' => 'completed', 'message' => 'Story package generated successfully.'],
            $before['step_ids'][1] => ['status' => 'pending', 'message' => null],
            $before['step_ids'][2] => ['status' => 'pending', 'message' => null],
        ];

        $novel = new Novel();

        $novel->forceFill([
            'title' => 'Sample Novel',
            'sub_title' => 'A Subtitle',
            'audience_id' => Audience::query()->value('id'),
            'novel_type_id' => NovelType::query()->value('id'),
            'language_id' => Language::query()->value('id'),
            'created_by_id' => User::query()->value('id'),
            'status' => NovelHelper::STATUS_ONGOING,
            'generation_steps' => $progress,
            'foundation' => ['premise' => 'kept as generated'],
        ]);

        $novel->save();

        $this->migration()->up();

        $fresh = $novel->fresh();

        $this->assertSame($progress, $fresh->generation_steps);
        $this->assertSame('completed', $fresh->generation_steps[$before['step_ids'][0]]['status']);
        $this->assertSame(['premise' => 'kept as generated'], $fresh->foundation);
    }

    public function test_it_leaves_unrelated_rows_alone(): void
    {
        $this->seedLegacyStages();

        AiPrompt::factory()->create([
            'name' => 'Custom Operator Prompt',
            'code' => 'CustomOperatorPrompt',
        ]);

        NovelGeneratorStep::factory()->create([
            'name' => 'Beta Proofreader',
        ]);

        $this->migration()->up();

        $this->assertDatabaseHas('ai_prompts', [
            'name' => 'Custom Operator Prompt',
            'slug' => 'custom-operator-prompt',
        ]);

        $this->assertDatabaseHas('novel_generator_steps', [
            'name' => 'Beta Proofreader',
            'slug' => 'beta-proofreader',
        ]);
    }

    public function test_it_is_a_no_op_when_the_stages_are_already_renamed(): void
    {
        $this->seedLegacyStages();

        $this->migration()->up();

        $before = AiPrompt::query()->orderBy('id')->pluck('id', 'name')->all();

        $this->migration()->up();

        $this->assertSame($before, AiPrompt::query()->orderBy('id')->pluck('id', 'name')->all());
    }

    public function test_it_can_be_reverted(): void
    {
        $this->seedLegacyStages();

        $migration = $this->migration();

        $migration->up();
        $migration->down();

        $this->assertSame(
            self::LEGACY_NAMES,
            AiPrompt::query()->orderBy('id')->pluck('name')->all(),
        );

        $this->assertSame(
            self::LEGACY_NAMES,
            NovelGeneratorStep::query()->orderBy('id')->pluck('name')->all(),
        );
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_27_000001_rename_novel_generator_stages.php');
    }

    private function seedLegacyStages(): array
    {
        $stepIds = [];

        foreach (self::LEGACY_NAMES as $index => $name) {
            $prompt = AiPrompt::factory()->create([
                'name' => $name,
                'code' => str_replace(' ', '', $name),
                'step_number' => $index + 1,
            ]);

            NovelGeneratorStep::factory()->create([
                'name' => $name,
                'ai_prompt_id' => $prompt->id,
                'depend_on_step_ids' => $stepIds ?: null,
            ]);

            $stepIds[] = $prompt->id;
        }

        $stepIds = NovelGeneratorStep::query()->orderBy('id')->pluck('id')->all();

        return [
            'prompt_ids' => AiPrompt::query()->orderBy('id')->pluck('id')->all(),
            'step_ids' => $stepIds,
            'step_prompt_ids' => NovelGeneratorStep::query()->orderBy('id')->pluck('ai_prompt_id')->all(),
            'step_dependency_ids' => NovelGeneratorStep::query()->orderBy('id')->pluck('depend_on_step_ids')->all(),
        ];
    }
}
