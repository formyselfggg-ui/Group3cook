<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('MAT-####'),
            'name' => fake()->randomElement(['Electrical Cable', 'Connector', 'Insulator', 'Fuse']),
            'unit' => fake()->randomElement(['pcs', 'm', 'roll']),
            'quantity_on_hand' => fake()->randomFloat(2, 1, 100),
            'reorder_level' => 5,
        ];
    }
}
