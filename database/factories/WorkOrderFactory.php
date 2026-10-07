<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_number' => fake()->unique()->bothify('WO-######'),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Maintenance', 'Repair', 'Inspection', 'Installation']),
            'priority' => WorkOrder::PRIORITY_NORMAL,
            'assigned_personnel_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'date_assigned' => now(),
            'scheduled_at' => now()->addDay(),
            'status' => WorkOrder::STATUS_ASSIGNED,
        ];
    }
}
