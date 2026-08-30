<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiBrainRunner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiBrainRunner>
 */
class AiBrainRunnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::where("is_super_admin", true)->inRandomOrder()->first();

        $name  = $this->faker->name();
        $brief = $this->faker->sentence();
        $ip = long2ip(mt_rand(0, 4294967295));

        return [
            'name'          => $name,
            'url'           => "http://{$ip}",
            'brief'         => $brief,
            "created_by_id" => $user?->id ?? "1",
        ];
    }
}
