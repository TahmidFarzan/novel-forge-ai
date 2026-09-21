<?php

namespace Database\Seeders;

use App\Helpers\SeederHelper;
use App\Models\AiPrompt;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NovelGeneratorStepSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            NovelGeneratorStep::query()->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name='novel_generator_steps'");
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            NovelGeneratorStep::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        if (in_array(config('database.default'), ['pgsql', 'sqlsrv'])) {
            NovelGeneratorStep::truncate();
        }

        $definitions = SeederHelper::novelGeneratorSteps();

        $promptIdsByName = $this->resolvePromptIds($definitions);
        $createdBy = User::query()->where('is_super_admin', true)->first();

        $stepIdsByName = [];

        foreach ($definitions as $definition) {
            $novelGeneratorStep = NovelGeneratorStep::factory()->state([
                'name' => $definition->name,
                'ai_prompt_id' => $promptIdsByName[$definition->prompt_name],
                'created_by_id' => $createdBy?->id ?? User::factory(),
            ])->create();

            $stepIdsByName[$definition->name] = $novelGeneratorStep->id;
        }

        $previousStepId = null;

        foreach ($definitions as $index => $definition) {
            $novelGeneratorStep = NovelGeneratorStep::query()
                ->where('name', $definition->name)
                ->firstOrFail();

            $dependOnStepIds = $this->resolveDependencyStepIds($definition, $stepIdsByName);
            $nextStepId = isset($definitions[$index + 1])
                ? $stepIdsByName[$definitions[$index + 1]->name]
                : null;

            $novelGeneratorStep->depend_on_step_ids = $dependOnStepIds;
            $novelGeneratorStep->previous_step_id = $previousStepId;
            $novelGeneratorStep->next_step_id = $nextStepId;
            $novelGeneratorStep->save();

            $previousStepId = $stepIdsByName[$definition->name];
        }
    }

    private function resolvePromptIds(iterable $definitions): array
    {
        $promptNames = collect($definitions)
            ->pluck('prompt_name')
            ->unique()
            ->values();

        $promptIdsByName = AiPrompt::query()
            ->whereIn('name', $promptNames)
            ->pluck('id', 'name');

        $missingPromptNames = $promptNames->diff($promptIdsByName->keys());

        if ($missingPromptNames->isNotEmpty()) {
            throw new Exception(
                'Novel generator step seeding failed. Missing AiPrompt names: '
                .$missingPromptNames->implode(', ')
                .'. Run AiPromptSeeder first.'
            );
        }

        return $promptIdsByName->all();
    }

    private function resolveDependencyStepIds(object $definition, array $stepIdsByName): ?array
    {
        if (empty($definition->depends_on)) {
            return null;
        }

        $unknownDependencies = collect($definition->depends_on)
            ->reject(fn (string $stepName) => array_key_exists($stepName, $stepIdsByName));

        if ($unknownDependencies->isNotEmpty()) {
            throw new Exception(
                "Novel generator step seeding failed for [{$definition->name}]. Unknown dependency step names: "
                .$unknownDependencies->implode(', ')
            );
        }

        $dependOnStepIds = collect($definition->depends_on)
            ->map(fn (string $stepName) => $stepIdsByName[$stepName])
            ->unique()
            ->values()
            ->all();

        return $dependOnStepIds ?: null;
    }
}
