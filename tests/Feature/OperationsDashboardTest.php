<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Material;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterialUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_staff_can_view_operational_summaries_and_asset_history(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $asset = Asset::factory()->create([
            'current_status' => 'under_maintenance',
            'condition' => 'needs_attention',
        ]);
        $material = Material::factory()->create(['name' => 'Copper cable', 'unit' => 'm']);
        $workOrder = WorkOrder::factory()->for($asset)->for($fieldUser, 'assignedPersonnel')->create([
            'title' => 'Repair damaged feeder',
            'category' => 'Repair',
            'priority' => WorkOrder::PRIORITY_URGENT,
            'status' => WorkOrder::STATUS_COMPLETED,
            'completion_at' => now(),
            'work_performed' => 'Replaced the damaged feeder section.',
        ]);
        WorkOrderMaterialUsage::factory()->for($workOrder)->for($material)->create([
            'quantity' => 12,
        ]);

        $response = $this->actingAs($operationsUser)->get(route('operations.dashboard'));

        $response->assertSee('Engineering dashboard')
            ->assertSee('Repair damaged feeder')
            ->assertSee('Recent maintenance activities')
            ->assertSee('Copper cable')
            ->assertSee('Repair');
        $this->assertSame(1, $response->viewData('counts')['work_orders']);
        $this->assertSame(1, $response->viewData('counts')['assets_under_maintenance']);
    }

    public function test_operations_login_redirects_to_engineering_workspace(): void
    {
        $operationsUser = User::factory()->create([
            'role' => User::ROLE_OPERATIONS,
            'password' => 'operations-password',
        ]);

        $this->post(route('login'), [
            'email' => $operationsUser->email,
            'password' => 'operations-password',
        ])->assertRedirect(route('operations.dashboard'));
    }

    public function test_non_operations_roles_cannot_access_engineering_endpoints(): void
    {
        $this->get(route('operations.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]))
            ->get(route('operations.dashboard'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_SUPERVISOR]))
            ->post(route('operations.assets.store'), [])
            ->assertForbidden();
    }

    public function test_operations_staff_can_register_a_work_requirement_for_supervisor_assignment(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $asset = Asset::factory()->create();

        $this->actingAs($operationsUser)
            ->post(route('operations.work-orders.store'), [
                'title' => 'Inspect substation transformer',
                'description' => 'Check the oil level and inspect for leaks.',
                'category' => 'Maintenance',
                'priority' => WorkOrder::PRIORITY_HIGH,
                'asset_id' => $asset->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('operations.dashboard'))
            ->assertSessionHas('status', 'Work requirement registered for supervisor assignment.');

        $workOrder = WorkOrder::query()->where('title', 'Inspect substation transformer')->firstOrFail();

        $this->assertSame(WorkOrder::STATUS_PENDING, $workOrder->status);
        $this->assertNull($workOrder->assigned_personnel_id);
        $this->assertSame($asset->id, $workOrder->asset_id);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $operationsUser->id,
            'action' => 'work_order_created',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_SUPERVISOR]))
            ->get(route('supervisor.dashboard'))
            ->assertSee('Inspect substation transformer')
            ->assertSee('ready to assign');
    }

    public function test_operations_staff_can_register_and_update_an_asset_with_activity_logs(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $this->actingAs($operationsUser)
            ->post(route('operations.assets.store'), [
                'asset_number' => 'AST-NEW-01',
                'asset_type' => 'Transformer',
                'name' => 'North feeder transformer',
                'serial_number' => 'SN-NEW-01',
                'current_status' => 'active',
                'condition' => 'good',
                'installed_on' => '2025-03-01',
            ])
            ->assertRedirect(route('operations.dashboard'))
            ->assertSessionHas('status', 'Asset registered successfully.');

        $asset = Asset::query()->where('asset_number', 'AST-NEW-01')->firstOrFail();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $operationsUser->id,
            'action' => 'asset_created',
            'record_id' => $asset->id,
        ]);

        $this->patch(route('operations.assets.update', $asset), [
            'asset_number' => 'AST-NEW-01',
            'asset_type' => 'Transformer',
            'name' => 'North feeder transformer, revised',
            'serial_number' => 'SN-NEW-01',
            'current_status' => 'under_maintenance',
            'condition' => 'needs_attention',
            'installed_on' => '2025-03-01',
        ])->assertRedirect(route('operations.dashboard'))
            ->assertSessionHas('status', 'Asset record updated.');

        $this->assertSame('North feeder transformer, revised', $asset->fresh()->name);
        $this->assertSame('under_maintenance', $asset->fresh()->current_status);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $operationsUser->id,
            'action' => 'asset_updated',
            'record_id' => $asset->id,
        ]);
    }

    public function test_work_order_report_applies_filters_and_exports_csv(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $included = WorkOrder::factory()->create([
            'title' => 'Filtered urgent repair',
            'category' => 'Repair',
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]);
        WorkOrder::factory()->create([
            'title' => 'Other inspection',
            'category' => 'Inspection',
            'priority' => WorkOrder::PRIORITY_LOW,
        ]);

        $this->actingAs($operationsUser)
            ->get(route('operations.dashboard', [
                'category' => 'Repair',
                'priority' => WorkOrder::PRIORITY_URGENT,
            ]))
            ->assertSee('Filtered urgent repair')
            ->assertDontSee('Other inspection');

        $report = $this->get(route('operations.reports.export', [
            'category' => 'Repair',
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]));

        $report->assertDownload('engineering-work-orders.csv');
        $this->assertStringContainsString($included->work_order_number, $report->streamedContent());
        $this->assertStringNotContainsString('Other inspection', $report->streamedContent());
    }

    public function test_operations_work_order_filters_reject_invalid_status_and_date_ranges(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);

        $this->actingAs($operationsUser)
            ->from(route('operations.dashboard'))
            ->get(route('operations.dashboard', ['status' => 'invalid']))
            ->assertRedirect(route('operations.dashboard'))
            ->assertSessionHasErrors('status');

        $this->get(route('operations.dashboard', [
            'from' => '2026-10-10',
            'to' => '2026-10-01',
        ]))->assertSessionHasErrors('to');
    }

    public function test_supervisor_can_assign_an_engineering_requirement_to_active_field_personnel(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_PENDING,
            'assigned_personnel_id' => null,
            'date_assigned' => null,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.assign', $workOrder), [
                'assigned_personnel_id' => $fieldUser->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHas('status', 'Work requirement assigned to field personnel.');

        $this->assertSame(WorkOrder::STATUS_ASSIGNED, $workOrder->fresh()->status);
        $this->assertSame($fieldUser->id, $workOrder->fresh()->assigned_personnel_id);
        $this->assertNotNull($workOrder->fresh()->date_assigned);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'field_personnel_assigned',
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_supervisor_assignment_rejects_invalid_work_orders_without_changing_them(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.assign', $workOrder), [
                'assigned_personnel_id' => $fieldUser->id,
            ])
            ->assertSessionHasErrors('work_order');

        $this->assertSame(WorkOrder::STATUS_IN_PROGRESS, $workOrder->fresh()->status);
        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'field_personnel_assigned',
            'record_id' => $workOrder->id,
        ]);
    }
}
