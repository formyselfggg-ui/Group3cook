<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\SystemNotification;
use App\Support\NotificationTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_work_order_notifies_the_field_personnel(): void
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
            ])
            ->assertRedirect(route('supervisor.dashboard'));

        $notification = $fieldUser->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(SystemNotification::TYPE_WORK_ORDER_ASSIGNED, $notification->data['type']);
        $this->assertStringContainsString($workOrder->work_order_number, $notification->data['message']);
    }

    public function test_changing_the_priority_notifies_the_assigned_field_personnel(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_ASSIGNED,
            'priority' => WorkOrder::PRIORITY_NORMAL,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.priority', $workOrder), [
                'priority' => WorkOrder::PRIORITY_URGENT,
            ])
            ->assertRedirect(route('supervisor.dashboard'));

        $notification = $fieldUser->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame(SystemNotification::TYPE_WORK_ORDER_PRIORITY_CHANGED, $notification->data['type']);
    }

    public function test_submitted_work_notifies_supervisors_for_review(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($fieldUser)
            ->post(route('field.work-orders.submit', $workOrder), [
                'work_performed' => 'Replaced the damaged feeder section.',
            ])
            ->assertRedirect(route('field.dashboard'));

        $notification = $supervisor->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(SystemNotification::TYPE_WORK_ORDER_SUBMITTED, $notification->data['type']);
    }

    public function test_returning_work_for_correction_notifies_the_field_personnel(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.review', $workOrder), [
                'decision' => 'return',
                'return_notes' => 'Add the torque readings to the report.',
            ])
            ->assertRedirect(route('supervisor.dashboard'));

        $notification = $fieldUser->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame(SystemNotification::TYPE_WORK_ORDER_RETURNED, $notification->data['type']);
    }

    public function test_approving_and_closing_work_order_notifies_the_field_personnel(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);
        $workOrder = WorkOrder::factory()->for($fieldUser, 'assignedPersonnel')->create([
            'status' => WorkOrder::STATUS_FOR_REVIEW,
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.review', $workOrder), ['decision' => 'approve'])
            ->assertRedirect(route('supervisor.dashboard'));

        $this->assertContains(
            SystemNotification::TYPE_WORK_ORDER_APPROVED,
            $fieldUser->notifications()->pluck('data')->map(fn (array $data): string => $data['type'])->all(),
        );

        $this->actingAs($supervisor)
            ->post(route('supervisor.work-orders.close', $workOrder))
            ->assertRedirect(route('supervisor.dashboard'));

        $this->assertContains(
            SystemNotification::TYPE_WORK_ORDER_COMPLETED,
            $fieldUser->notifications()->pluck('data')->map(fn (array $data): string => $data['type'])->all(),
        );
    }

    public function test_low_inventory_notifies_operations_and_administrators(): void
    {
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $material = Material::factory()->create([
            'quantity_on_hand' => 20,
            'reorder_level' => 5,
        ]);

        $this->actingAs($operationsUser)
            ->patch(route('operations.materials.update', $material), [
                'name' => $material->name,
                'unit' => $material->unit,
                'quantity_on_hand' => 1,
                'reorder_level' => 5,
            ])
            ->assertRedirect(route('operations.dashboard'));

        $this->assertSame(
            SystemNotification::TYPE_LOW_INVENTORY,
            $operationsUser->notifications()->latest()->first()->data['type'],
        );

        $this->assertSame(
            SystemNotification::TYPE_LOW_INVENTORY,
            $admin->notifications()->latest()->first()->data['type'],
        );
    }

    public function test_users_can_read_their_notifications_and_mark_them_read(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $otherUser = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $user->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_ASSIGNED,
            title: 'New work order assigned',
            message: 'Work order WO-000001 was assigned to you.',
            route: 'supervisor.dashboard',
        ));

        $otherUser->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_ASSIGNED,
            title: 'New work order assigned',
            message: 'Work order WO-000002 was assigned to you.',
            route: 'supervisor.dashboard',
        ));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()->assertSee('WO-000001')->assertDontSee('WO-000002');

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_notification_links_open_a_page_the_recipient_can_access(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $operationsUser = User::factory()->create(['role' => User::ROLE_OPERATIONS]);
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $fieldUser = User::factory()->create(['role' => User::ROLE_FIELD_PERSONNEL]);

        $material = Material::factory()->create([
            'quantity_on_hand' => 20,
            'reorder_level' => 5,
        ]);

        $this->actingAs($operationsUser)
            ->patch(route('operations.materials.update', $material), [
                'name' => $material->name,
                'unit' => $material->unit,
                'quantity_on_hand' => 1,
                'reorder_level' => 5,
            ])
            ->assertRedirect(route('operations.dashboard'));

        $adminNotification = $admin->notifications()->latest()->firstOrFail();
        $adminTarget = NotificationTarget::resolve($adminNotification->data, $admin);

        $this->assertNotNull($adminTarget);
        $this->assertStringContainsString(route('admin.dashboard'), $adminTarget);

        $this->actingAs($admin)->get($adminTarget)->assertOk();

        $operationsTarget = NotificationTarget::resolve(
            $operationsUser->notifications()->latest()->firstOrFail()->data,
            $operationsUser,
        );

        $this->assertSame(route('operations.dashboard').'#material-inventory-heading', $operationsTarget);

        $fieldTarget = NotificationTarget::resolve(
            ['route' => 'field.dashboard', 'type' => SystemNotification::TYPE_WORK_ORDER_ASSIGNED],
            $fieldUser,
        );

        $this->assertSame(route('field.dashboard'), $fieldTarget);

        $supervisorTarget = NotificationTarget::resolve(
            ['route' => 'supervisor.dashboard', 'type' => SystemNotification::TYPE_WORK_ORDER_SUBMITTED],
            $supervisor,
        );

        $this->assertSame(route('supervisor.dashboard'), $supervisorTarget);
    }

    public function test_users_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $otherUser = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $otherUser->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_ASSIGNED,
            title: 'New work order assigned',
            message: 'Work order WO-000003 was assigned to you.',
            route: 'supervisor.dashboard',
        ));

        $notification = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
