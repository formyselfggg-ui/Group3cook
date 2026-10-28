<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_number' => fake()->unique()->bothify('AST-####'),
            'asset_type' => fake()->randomElement(['Transformer', 'Electric Meter', 'Distribution Equipment', 'Cable']),
            'name' => fake()->words(3, true),
            'serial_number' => fake()->optional()->bothify('SN-########'),
            'current_status' => 'active',
            'condition' => fake()->randomElement(['good', 'fair', 'needs_attention']),
            'installed_on' => fake()->date(),
        ];
    }
}
