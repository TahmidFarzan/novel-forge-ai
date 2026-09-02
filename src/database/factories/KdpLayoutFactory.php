<?php
namespace Database\Factories;

use App\Models\KdpLayout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KdpLayout>
 */
class KdpLayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::where("is_super_admin", true)->inRandomOrder()->first();

        return [
            'name'               => $this->faker->unique()->words(3, true),
            'description'        => $this->faker->sentence(),
            'binding_type'       => $this->faker->randomElement(['Paperback', 'Hardcover']),
            'page_size'          => $this->faker->randomElement(['6 x 9 in', '5.5 x 8.5 in', '7.5 x 9.25 in']),
            'width'              => $this->faker->randomFloat(2, 5, 8),
            'height'             => $this->faker->randomFloat(2, 7, 10),
            'top_margin'         => $this->faker->randomFloat(2, 0.5, 1.5),
            'bottom_margin'      => $this->faker->randomFloat(2, 0.5, 1.5),
            'outside_margin'     => $this->faker->randomFloat(2, 0.5, 1),
            'gutter'             => $this->faker->randomFloat(2, 0.1, 0.5),
            'bleed'              => $this->faker->randomElement(['No bleed', 'Bleed']),
            'interior_type'      => $this->faker->randomElement(['Black & White', 'Color', 'Premium Color']),
            'paper_color'        => $this->faker->randomElement(['White', 'Cream']),
            'trim_size'          => $this->faker->randomElement(['6 x 9', '5.5 x 8.5']),
            'font_settings'      => $this->faker->randomElement(['Times New Roman 12pt', 'Garamond 11pt', 'Georgia 12pt']),
            'minimum_page_count' => $this->faker->numberBetween(24, 100),
            'maximum_page_count' => $this->faker->numberBetween(400, 828),
            'created_by_id'      => $user?->id ?? "1",
        ];
    }
}
