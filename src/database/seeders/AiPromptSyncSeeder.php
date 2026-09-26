<?php

namespace Database\Seeders;

use App\Helpers\SeederHelper;
use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

class AiPromptSyncSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach (SeederHelper::aiPrompts() as $source) {
            $existing = AiPrompt::query()->where('name', $source->name)->first();

            $attributes = [
                'name' => $source->name,
                'prompt' => $source->prompt,
                'step_number' => $source->step_number,
                'depend_on_prompt_ids' => $source->depend_on_prompt_ids,
            ];

            if (! $existing) {
                AiPrompt::factory()->state($attributes)->create();

                $created++;

                continue;
            }

            if ($existing->prompt === $source->prompt) {
                $skipped++;

                continue;
            }

            $existing->forceFill($attributes)->save();

            $updated++;
        }

        $this->command?->info(sprintf(
            'AI prompts synchronised: %d created, %d updated, %d unchanged.',
            $created,
            $updated,
            $skipped,
        ));
    }
}
