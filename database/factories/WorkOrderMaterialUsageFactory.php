<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterialUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderMaterialUsage>
 */
class WorkOrderMaterialUsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'material_id' => Material::factory(),
            'user_id' => User::factory(),
            'quantity' => fake()->randomFloat(2, 1, 5),
            'used_at' => now(),
        ];
    }
}
