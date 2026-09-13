<?php

namespace Database\Factories;

use App\Models\AiBrainOutputType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiBrainOutputType>
 */
class AiBrainOutputTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::where('is_super_admin', true)->inRandomOrder()->first();

        $name = $this->faker->unique()->randomElement(['Image', 'Text']);

        return [
            'name' => $name,
            'code' => Str::studly($name),
            'brief' => $this->faker->sentence(),
            'created_by_id' => $user?->id ?? '1',
        ];
    }
}
