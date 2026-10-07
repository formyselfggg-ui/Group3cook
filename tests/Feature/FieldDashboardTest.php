<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FieldDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_field_sign_in(): void
    {
        $this->get(route('field.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_can_view_the_field_sign_in_form(): void
    {
        $this->get(route('login'))
            ->assertSee('Sign in to your account')
            ->assertSee('Secure team access')
            ->assertSee('name="email"', false);
    }

    public function test_field_personnel_can_sign_in_and_view_only_their_assigned_work_orders(): void
    {
        $fieldUser = User::factory()->create([
            'name' => 'Jamie Field',
            'email' => 'jamie@example.com',
            'password' => 'field-password',
        ]);
        $otherFieldUser = User::factory()->create();
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'work_order_number' => 'WO-100001',
            'title' => 'Inspect pole transformer',
            'status' => WorkOrder::STATUS_ASSIGNED,
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]);
        WorkOrder::factory()->for($otherFieldUser, 'assignedPersonnel')->create([
            'work_order_number' => 'WO-100002',
            'title' => 'Other crew task',
        ]);

        $this->post(route('login'), [
            'email' => 'jamie@example.com',
            'password' => 'field-password',
        ])->assertRedirect(route('field.dashboard'));

        $this->get(route('field.dashboard'))
            ->assertSee('Jamie Field')
            ->assertSee('WO-100001')
            ->assertSee('Inspect pole transformer')
            ->assertDontSee('WO-100002')
            ->assertDontSee('Other crew task');
    }

    public function test_inactive_field_account_cannot_sign_in(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'field-password',
            'is_active' => false,
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'inactive@example.com',
                'password' => 'field-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_field_role_is_forbidden_from_field_dashboard(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($supervisor)
            ->get(route('field.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_filters_work_orders_for_the_signed_in_user(): void
    {
        $fieldUser = User::factory()->create();
        $otherFieldUser = User::factory()->create();
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
            'title' => 'New task item',
            'priority' => WorkOrder::PRIORITY_URGENT,
        ]);
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
            'title' => 'Replace damaged cable',
        ]);
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
        ]);
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_COMPLETED,
        ]);
        WorkOrder::factory()->for($otherFieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
            'title' => 'Private assignment',
        ]);

        $response = $this->actingAs($fieldUser)
            ->get(route('field.dashboard'));

        $response
            ->assertSee('New assignments')
            ->assertSee('In progress')
            ->assertSee('For review')
            ->assertSee('Completed')
            ->assertSee('Replace damaged cable')
            ->assertDontSee('Private assignment');

        $this->assertSame([
            'assigned' => 1,
            'in_progress' => 1,
            'for_review' => 1,
            'completed' => 1,
            'urgent' => 1,
        ], $response->viewData('counts'));

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard', ['status' => WorkOrder::STATUS_IN_PROGRESS]))
            ->assertSee('Replace damaged cable')
            ->assertDontSee('Private assignment')
            ->assertDontSee('New task item')
            ->assertDontSee('Start work');

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard', ['priority' => WorkOrder::PRIORITY_URGENT]))
            ->assertSee('New task item')
            ->assertDontSee('Replace damaged cable')
            ->assertDontSee('Private assignment');
    }

    public function test_field_personnel_see_a_clear_empty_state_when_no_work_is_assigned(): void
    {
        $fieldUser = User::factory()->create();

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard'))
            ->assertSee('Nothing assigned right now')
            ->assertSee('New work orders assigned to you will appear here.');
    }

    public function test_field_personnel_can_start_an_assigned_work_order_and_activity_is_logged(): void
    {
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
        ]);

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.start', $workOrder))
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHas('status', 'Work started. Keep the task updated as you go.');

        $this->assertSame(WorkOrder::STATUS_IN_PROGRESS, $workOrder->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $fieldUser->id,
            'action' => 'work_order_started',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_field_personnel_cannot_access_another_users_work_order(): void
    {
        $fieldUser = User::factory()->create();
        $otherFieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($otherFieldUser, 'assignedPersonnel')->create();

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.start', $workOrder))
            ->assertNotFound();

        $this->assertSame(WorkOrder::STATUS_ASSIGNED, $workOrder->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_field_personnel_cannot_skip_the_assigned_to_in_progress_transition(): void
    {
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
        ]);

        $this->actingAs($fieldUser)
            ->from(route('field.dashboard'))
            ->post(route('field.work-orders.start', $workOrder))
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHasErrors('work_order');

        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_field_personnel_can_submit_work_notes_and_photos_for_review(): void
    {
        Storage::fake('local');
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.submit', $workOrder), [
                'work_performed' => 'Replaced the damaged connector and tested the line.',
                'field_notes' => 'Service restored; no additional hazards observed.',
                'photos' => [
                    UploadedFile::fake()
                        ->create('repair.jpg', 1)
                        ->mimeType('image/jpeg'),
                ],
            ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHas('status', 'Work submitted for supervisor review.');

        $workOrder->refresh();
        $this->assertSame(WorkOrder::STATUS_FOR_REVIEW, $workOrder->status);
        $this->assertSame('Replaced the damaged connector and tested the line.', $workOrder->work_performed);
        $this->assertSame('Service restored; no additional hazards observed.', $workOrder->field_notes);
        $this->assertNotNull($workOrder->submitted_at);
        $this->assertCount(1, $workOrder->photo_paths);
        Storage::disk('local')->assertExists($workOrder->photo_paths[0]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $fieldUser->id,
            'action' => 'work_order_submitted_for_review',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_field_personnel_can_record_material_usage_and_reduce_inventory(): void
    {
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);
        $material = Material::factory()->create([
            'name' => 'Copper cable',
            'unit' => 'm',
            'quantity_on_hand' => 10,
        ]);

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.materials.store', $workOrder), [
                'material_id' => $material->id,
                'quantity' => '2.50',
            ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHas('status', 'Material usage recorded and inventory updated.');

        $this->assertSame('7.50', $material->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('work_order_material_usages', [
            'work_order_id' => $workOrder->id,
            'material_id' => $material->id,
            'user_id' => $fieldUser->id,
            'quantity' => '2.50',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $fieldUser->id,
            'action' => 'work_order_material_recorded',
            'record_type' => WorkOrder::class,
            'record_id' => $workOrder->id,
        ]);
    }

    public function test_field_personnel_cannot_record_more_material_than_inventory_contains(): void
    {
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);
        $material = Material::factory()->create(['quantity_on_hand' => 1]);

        $this->actingAs($fieldUser)
            ->from(route('field.dashboard'))
            ->post(route('field.work-orders.materials.store', $workOrder), [
                'material_id' => $material->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHasErrors('quantity');

        $this->assertSame('1.00', $material->fresh()->quantity_on_hand);
        $this->assertDatabaseCount('work_order_material_usages', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_submitting_without_work_performed_does_not_change_work_order_status(): void
    {
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($fieldUser)
            ->from(route('field.dashboard'))
            ->post(route('field.work-orders.submit', $workOrder), [
                'work_performed' => '',
            ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHasErrors('work_performed');

        $this->assertSame(WorkOrder::STATUS_IN_PROGRESS, $workOrder->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_field_personnel_cannot_submit_unsupported_photo_types(): void
    {
        Storage::fake('local');
        $fieldUser = User::factory()->create();
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($fieldUser)
            ->from(route('field.dashboard'))
            ->post(route('field.work-orders.submit', $workOrder), [
                'work_performed' => 'Completed the repair.',
                'photos' => [
                    UploadedFile::fake()
                        ->create('unsafe.svg', 1)
                        ->mimeType('image/svg+xml'),
                ],
            ])
            ->assertRedirect(route('field.dashboard'))
            ->assertSessionHasErrors('photos.0');

        $this->assertSame(WorkOrder::STATUS_IN_PROGRESS, $workOrder->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_dashboard_escapes_user_provided_work_order_content(): void
    {
        $fieldUser = User::factory()->create();
        WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'title' => '<script>alert("unsafe")</script>',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $this->actingAs($fieldUser)
            ->get(route('field.dashboard'))
            ->assertDontSee('<script>alert("unsafe")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
