<?php

namespace Database\Factories;

use App\Models\AiPrompt;
use App\Models\NovelGeneratorStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NovelGeneratorStep>
 */
class NovelGeneratorStepFactory extends Factory
{
    public function definition(): array
    {
        $user = User::where('is_super_admin', true)->inRandomOrder()->first();

        return [
            'name' => $this->faker->unique()->words(3, true),
            'depend_on_step_ids' => null,
            'previous_step_id' => null,
            'next_step_id' => null,
            'ai_prompt_id' => AiPrompt::factory(),
            'created_by_id' => $user?->id ?? '1',
        ];
    }

    public function withDependencies(array $stepIds): static
    {
        return $this->state(fn () => [
            'depend_on_step_ids' => array_values(array_unique(array_map('intval', $stepIds))),
        ]);
    }

    public function withNextStep(int $stepId): static
    {
        return $this->state(fn () => [
            'next_step_id' => $stepId,
        ]);
    }

    public function withPreviousStep(int $stepId): static
    {
        return $this->state(fn () => [
            'previous_step_id' => $stepId,
        ]);
    }
}
