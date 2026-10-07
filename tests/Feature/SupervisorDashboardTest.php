<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupervisorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_supervisor_can_sign_in_and_view_the_supervisor_workspace(): void
    {
        $supervisor = User::factory()->create([
            'name' => 'Taylor Supervisor',
            'email' => 'supervisor@example.com',
            'password' => 'supervisor-password',
            'role' => User::ROLE_SUPERVISOR,
        ]);
        $fieldUser = User::factory()->create([
            'name' => 'Morgan Field',
            'role' => User::ROLE_FIELD_PERSONNEL,
        ]);
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'work_order_number' => 'WO-200001',
            'title' => 'Inspect feeder line',
            'priority' => WorkOrder::PRIORITY_URGENT,
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->post(route('login'), [
            'email' => $supervisor->email,
            'password' => 'supervisor-password',
        ])->assertRedirect(route('supervisor.dashboard'));

        $response = $this->get(route('supervisor.dashboard'));

        $response->assertSee('Taylor Supervisor')
            ->assertSee('Supervisor workspace')
            ->assertSee('Create and assign work order')
            ->assertSee('Field crew workload')
            ->assertSee('Inspect feeder line')
            ->assertSee('Morgan Field');
        $this->assertSame(1, $response->viewData('counts')['in_progress']);
        $this->assertSame(1, $response->viewData('counts')['urgent']);
    }

    public function test_field_personnel_cannot_access_supervisor_dashboard_or_create_work_orders(): void
    {
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);

        $this->actingAs($fieldUser)
            ->get(route('supervisor.dashboard'))
            ->assertForbidden();

        $this->actingAs($fieldUser)
            ->post(route('supervisor.work-orders.store'), [])
            ->assertForbidden();
    }

    public function test_supervisor_can_create_and_assign_a_work_order_and_actions_are_logged(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create([
            'name' => 'Assigned Crew Member',
            'role' => User::ROLE_FIELD_PERSONNEL,
        ]);
        $asset = Asset::factory()->create();

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.store'), [
                'title' => 'Replace damaged connector',
                'description' => 'Inspect and replace the connector.',
                'category' => 'Repair',
                'priority' => WorkOrder::PRIORITY_HIGH,
                'assigned_personnel_id' => $fieldUser->id,
                'asset_id' => $asset->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHas('status', 'Work order created and assigned.');

        $workOrder = WorkOrder::query()->where('title', 'Replace damaged connector')->firstOrFail();

        $this->assertSame('WO-'.str_pad((string) $workOrder->id, 6, '0', STR_PAD_LEFT), $workOrder->work_order_number);
        $this->assertSame(WorkOrder::STATUS_ASSIGNED, $workOrder->status);
        $this->assertSame($fieldUser->id, $workOrder->assigned_personnel_id);
        $this->assertSame($asset->id, $workOrder->asset_id);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'work_order_created',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'field_personnel_assigned',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_supervisor_cannot_assign_an_order_to_a_non_field_account(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $otherSupervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($supervisor)
            ->from(route('supervisor.dashboard'))
            ->post(route('supervisor.work-orders.store'), [
                'title' => 'Test assignment',
                'category' => 'Inspection',
                'priority' => WorkOrder::PRIORITY_NORMAL,
                'assigned_personnel_id' => $otherSupervisor->id,
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHasErrors('assigned_personnel_id');

        $this->assertDatabaseMissing('work_orders', ['title' => 'Test assignment']);
    }

    public function test_supervisor_can_return_a_submission_with_notes_for_field_correction(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()
            ->for($fieldUser, 'assignedPersonnel')
            ->create([
                'status' => WorkOrder::STATUS_FOR_REVIEW,
                'submitted_at' => now(),
                'work_performed' => 'Repaired the damaged line.',
            ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.review', $workOrder), [
                'decision' => 'return',
                'return_notes' => 'Add a clear photo of the replacement part.',
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHas('status', 'Work returned to field personnel with correction instructions.');

        $this->assertSame(WorkOrder::STATUS_IN_PROGRESS, $workOrder->fresh()->status);
        $this->assertSame('Add a clear photo of the replacement part.', $workOrder->fresh()->return_notes);
        $this->assertNull($workOrder->fresh()->submitted_at);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'work_order_returned_for_correction',
            'record_id' => $workOrder->id,
        ]);

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard'))
            ->assertSee('Correction requested by your supervisor')
            ->assertSee('Add a clear photo of the replacement part.');

        $this->post(route('field.work-orders.submit', $workOrder), [
            'work_performed' => 'Added the requested photo and completed the repair.',
        ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHas('status', 'Work submitted for supervisor review.');

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->fresh()->status);
        $this->assertNull($workOrder->fresh()->return_notes);
    }

    public function test_supervisor_can_view_a_submission_photo_from_private_storage(): void
    {
        Storage::fake('local');
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $path = 'work-orders/submission.png';
        Storage::disk('local')->put(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j8ioAAAAASUVORK5CYII='),
        );
        $workOrder = WorkOrder::factory()->create(['photo_paths' => [$path]]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.work-orders.photos.show', [$workOrder, 0]))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->actingAs(User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]))
            ->get(route('supervisor.work-orders.photos.show', [$workOrder, 0]))
            ->assertForbidden();
    }

    public function test_return_for_correction_requires_notes(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_FOR_REVIEW]);

        $this->actingAs($supervisor)
            ->from(route('supervisor.dashboard'))
            ->post(route('supervisor.work-orders.review', $workOrder), [
                'decision' => 'return',
                'return_notes' => '',
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHasErrors('return_notes');

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->fresh()->status);
    }

    public function test_supervisor_can_approve_then_close_a_submission_and_restore_maintained_asset(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $asset = Asset::factory()->create(['current_status' => 'under_maintenance']);
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrder::STATUS_FOR_REVIEW,
            'submitted_at' => now(),
            'work_performed' => 'Completed repairs.',
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.review', $workOrder), [
                'decision' => 'approve',
            ])
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHas('status', 'Work approved. Close the work order when ready.');

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->fresh()->status);
        $this->assertSame($supervisor->id, $workOrder->fresh()->supervisor_approved_by_id);
        $this->assertNotNull($workOrder->fresh()->supervisor_approved_at);

        $this->post(route('supervisor.work-orders.close', $workOrder))
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHas('status', 'Work order closed.');

        $this->assertSame(WorkOrder::STATUS_COMPLETED, $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completion_at);
        $this->assertSame('active', $asset->fresh()->current_status);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'work_order_approved',
            'record_id' => $workOrder->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $supervisor->id,
            'action' => 'work_order_closed',
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_unapproved_work_order_cannot_be_closed(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $workOrder = WorkOrder::factory()->create(['status' => WorkOrder::STATUS_FOR_REVIEW]);

        $this->actingAs($supervisor)
            ->from(route('supervisor.dashboard'))
            ->post(route('supervisor.work-orders.close', $workOrder))
            ->assertRedirect(route('supervisor.dashboard'))
            ->assertSessionHasErrors('work_order');

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->fresh()->status);
        $this->assertNull($workOrder->fresh()->completion_at);
    }
}
