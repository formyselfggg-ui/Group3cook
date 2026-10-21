<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Permission;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permission::catalogue() as $name => $meta) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'description' => $meta['description']],
            );
        }
    }

    public function test_roles_fall_back_to_the_built_in_default_permissions(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $this->assertTrue($supervisor->hasPermissionTo(Permission::ASSIGN_WORK_ORDERS));
        $this->assertTrue($supervisor->hasPermissionTo(Permission::UPDATE_PRIORITY));
        $this->assertFalse($supervisor->hasPermissionTo(Permission::MANAGE_USERS));

        $this->assertTrue($fieldUser->hasPermissionTo(Permission::RECORD_MATERIALS));
        $this->assertFalse($fieldUser->hasPermissionTo(Permission::VIEW_ALL_WORK_ORDERS));

        $this->assertTrue($operationsUser->hasPermissionTo(Permission::MANAGE_MATERIALS));
        $this->assertTrue($operationsUser->hasPermissionTo(Permission::VIEW_REPORTS));
    }

    public function test_administrators_always_hold_every_permission(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->assertTrue($admin->hasPermissionTo(Permission::MANAGE_USERS));
        $this->assertTrue($admin->hasPermissionTo(Permission::MANAGE_PERMISSIONS));
        $this->assertTrue($admin->hasPermissionTo(Permission::RECORD_MATERIALS));
    }

    public function test_admin_can_save_a_role_permission_configuration(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $permissions = Permission::query()->get();
        $this->assertGreaterThan(0, $permissions->count());

        $manageMaterials = Permission::query()->where('name', Permission::MANAGE_MATERIALS)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.roles.update', User::ROLE_OPERATIONS), [
                'permissions' => [$manageMaterials->id],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertTrue(Permission::roleIsConfigured(User::ROLE_OPERATIONS));
        $this->assertSame(
            [Permission::MANAGE_MATERIALS],
            Permission::namesForRole(User::ROLE_OPERATIONS)->all(),
        );

        $this->assertDatabaseHas('activity_logs', ['action' => 'role_permissions_updated']);
    }

    public function test_a_role_configuration_overrides_the_built_in_defaults(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->assertTrue($supervisor->hasPermissionTo(Permission::ASSIGN_WORK_ORDERS));

        $this->actingAs($admin)->post(route('admin.roles.update', User::ROLE_SUPERVISOR), [
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertFalse($supervisor->fresh()->hasPermissionTo(Permission::ASSIGN_WORK_ORDERS));
    }

    public function test_restoring_defaults_rolls_back_a_role_configuration(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($admin)->post(route('admin.roles.update', User::ROLE_SUPERVISOR), [
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->actingAs($admin)
            ->post(route('admin.roles.reset', User::ROLE_SUPERVISOR))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertFalse(Permission::roleIsConfigured(User::ROLE_SUPERVISOR));
        $this->assertTrue($supervisor->fresh()->hasPermissionTo(Permission::ASSIGN_WORK_ORDERS));
    }

    public function test_admin_can_grant_an_extra_permission_to_a_single_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $permission = Permission::query()->where('name', Permission::VIEW_ALL_WORK_ORDERS)->firstOrFail();

        $this->assertFalse($fieldUser->hasPermissionTo(Permission::VIEW_ALL_WORK_ORDERS));

        $this->actingAs($admin)
            ->post(route('admin.roles.user-permission', [$fieldUser, $permission]))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertTrue($fieldUser->fresh()->hasPermissionTo(Permission::VIEW_ALL_WORK_ORDERS));
        $this->assertDatabaseHas('permission_user', [
            'user_id' => $fieldUser->id,
            'permission_id' => $permission->id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_permission_granted']);
    }

    public function test_permissions_are_enforced_on_the_backend(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $manageMaterials = Permission::query()->where('name', Permission::MANAGE_MATERIALS)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.roles.update', User::ROLE_OPERATIONS), [
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->actingAs($operationsUser)
            ->post(route('operations.materials.store'), [
                'sku' => 'BLOCKED-1',
                'name' => 'Blocked material',
                'unit' => 'pcs',
                'quantity_on_hand' => 5,
                'reorder_level' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('materials', ['sku' => 'BLOCKED-1']);

        $this->actingAs($operationsUser)
            ->get(route('operations.reports.export'))
            ->assertForbidden();

        $this->actingAs($operationsUser)
            ->patch(route('operations.materials.update', Material::factory()->create()), [
                'name' => 'Blocked material',
                'unit' => 'pcs',
                'quantity_on_hand' => 5,
                'reorder_level' => 1,
            ])
            ->assertForbidden();
    }

    public function test_field_personnel_without_the_record_permission_cannot_record_materials(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $material = Material::factory()->create(['quantity_on_hand' => 50]);
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $recordMaterials = Permission::query()->where('name', Permission::RECORD_MATERIALS)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.roles.update', User::ROLE_FIELD_PERSONNEL), [
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.materials.store', $workOrder), [
                'material_id' => $material->id,
                'quantity' => 5,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('work_order_material_usages', [
            'work_order_id' => $workOrder->id,
        ]);

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard'))
            ->assertOk()
            ->assertDontSee('Record materials used');

        $this->assertNotNull($recordMaterials);
    }

    public function test_supervisor_without_the_assign_permission_cannot_assign_work(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_PENDING,
            'assigned_personnel_id' => null,
            'date_assigned' => null,
        ]);

        $this->actingAs($admin)->post(route('admin.roles.update', User::ROLE_SUPERVISOR), [
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $originalPersonnelId = $workOrder->assigned_personnel_id;

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.assign', $workOrder), [
                'assigned_personnel_id' => $fieldUser->id,
            ])
            ->assertForbidden();

        $this->assertSame($originalPersonnelId, $workOrder->fresh()->assigned_personnel_id);
    }

    public function test_permission_configuration_rows_are_stored_per_role(): void
    {
        $permission = Permission::query()->where('name', Permission::VIEW_REPORTS)->firstOrFail();

        Permission::markRoleConfigured(User::ROLE_OPERATIONS);

        $this->assertDatabaseHas('role_permission_configs', ['role' => User::ROLE_OPERATIONS]);

        DB::table('permission_role')->insert([
            'role' => User::ROLE_OPERATIONS,
            'permission_id' => $permission->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(
            [Permission::VIEW_REPORTS],
            Permission::namesForRole(User::ROLE_OPERATIONS)->all(),
        );
    }
}
