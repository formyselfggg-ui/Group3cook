<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_staff_can_register_a_material_in_the_inventory(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $response = $this->actingAs($operationsUser)->post(route('operations.materials.store'), [
            'sku' => 'CBL-4C-25',
            'name' => '4 core copper cable',
            'unit' => 'm',
            'quantity_on_hand' => 120,
            'reorder_level' => 30,
        ]);

        $response->assertRedirect(route('operations.dashboard'));

        $this->assertDatabaseHas('materials', [
            'sku' => 'CBL-4C-25',
            'name' => '4 core copper cable',
            'unit' => 'm',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'material_registered',
            'record_type' => Material::class,
        ]);
    }

    public function test_operations_staff_can_adjust_stock_levels_and_reorder_points(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $material = Material::factory()->create([
            'name' => 'Connector',
            'unit' => 'pcs',
            'quantity_on_hand' => 40,
            'reorder_level' => 10,
        ]);

        $this->actingAs($operationsUser)
            ->patch(route('operations.materials.update', $material), [
                'name' => 'Connector',
                'unit' => 'pcs',
                'quantity_on_hand' => 8,
                'reorder_level' => 10,
            ])
            ->assertRedirect(route('operations.dashboard'));

        $material->refresh();

        $this->assertSame('8.00', $material->quantity_on_hand);
        $this->assertTrue($material->isLowStock());
        $this->assertDatabaseHas('activity_logs', ['action' => 'material_updated']);
    }

    public function test_material_registration_requires_a_unique_sku(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        Material::factory()->create(['sku' => 'CBL-4C-25']);

        $this->actingAs($operationsUser)
            ->post(route('operations.materials.store'), [
                'sku' => 'CBL-4C-25',
                'name' => 'Duplicate cable',
                'unit' => 'm',
                'quantity_on_hand' => 10,
                'reorder_level' => 2,
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_only_operations_staff_can_manage_inventory(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_SUPERVISOR]))
            ->post(route('operations.materials.store'), [
                'sku' => 'CBL-1',
                'name' => 'Cable',
                'unit' => 'm',
                'quantity_on_hand' => 10,
                'reorder_level' => 2,
            ])
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]))
            ->patch(route('operations.materials.update', Material::factory()->create()), [
                'name' => 'Cable',
                'unit' => 'm',
                'quantity_on_hand' => 10,
                'reorder_level' => 2,
            ])
            ->assertForbidden();
    }

    public function test_inventory_activity_is_recorded_for_registered_materials(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $this->actingAs($operationsUser)->post(route('operations.materials.store'), [
            'sku' => 'NEW-1',
            'name' => 'New material',
            'unit' => 'pcs',
            'quantity_on_hand' => 5,
            'reorder_level' => 1,
        ]);

        $material = Material::query()->where('sku', 'NEW-1')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $operationsUser->id,
            'action' => 'material_registered',
            'record_type' => Material::class,
            'record_id' => $material->id,
        ]);
    }
}
