<?php

namespace App\Services;

use App\Models\Material;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WorkOrderNotifier
{
    public function assigned(WorkOrder $workOrder): void
    {
        $workOrder->assignedPersonnel?->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_ASSIGNED,
            title: 'New work order assigned',
            message: sprintf(
                'Work order %s "%s" was assigned to you with %s priority.',
                $workOrder->work_order_number,
                $workOrder->title,
                Str::headline($workOrder->priority),
            ),
            route: 'field.dashboard',
            workOrderId: $workOrder->id,
        ));
    }

    public function priorityChanged(WorkOrder $workOrder, string $previousPriority): void
    {
        $workOrder->assignedPersonnel?->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_PRIORITY_CHANGED,
            title: 'Work order priority changed',
            message: sprintf(
                'Work order %s priority changed from %s to %s.',
                $workOrder->work_order_number,
                Str::headline($previousPriority),
                Str::headline($workOrder->priority),
            ),
            route: 'field.dashboard',
            workOrderId: $workOrder->id,
        ));
    }

    public function submittedForReview(WorkOrder $workOrder): void
    {
        $this->notifyRoles(
            User::ROLE_SUPERVISOR,
            new SystemNotification(
                type: SystemNotification::TYPE_WORK_ORDER_SUBMITTED,
                title: 'Work order waiting for review',
                message: sprintf(
                    'Work order %s "%s" was submitted by %s and needs your review.',
                    $workOrder->work_order_number,
                    $workOrder->title,
                    $workOrder->assignedPersonnel?->name ?? 'field personnel',
                ),
                route: 'supervisor.dashboard',
                workOrderId: $workOrder->id,
            ),
        );
    }

    public function returnedForCorrection(WorkOrder $workOrder, string $notes): void
    {
        $workOrder->assignedPersonnel?->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_RETURNED,
            title: 'Work returned for correction',
            message: sprintf(
                'Work order %s was returned for correction. Notes: %s',
                $workOrder->work_order_number,
                Str::limit($notes, 200),
            ),
            route: 'field.dashboard',
            workOrderId: $workOrder->id,
        ));
    }

    public function approved(WorkOrder $workOrder): void
    {
        $workOrder->assignedPersonnel?->notify(new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_APPROVED,
            title: 'Work order approved',
            message: sprintf(
                'Your submission for work order %s was approved. Awaiting closure.',
                $workOrder->work_order_number,
            ),
            route: 'field.dashboard',
            workOrderId: $workOrder->id,
        ));
    }

    public function completed(WorkOrder $workOrder): void
    {
        $notification = new SystemNotification(
            type: SystemNotification::TYPE_WORK_ORDER_COMPLETED,
            title: 'Work order completed',
            message: sprintf(
                'Work order %s "%s" is now completed and recorded in the maintenance history.',
                $workOrder->work_order_number,
                $workOrder->title,
            ),
            route: 'field.dashboard',
            workOrderId: $workOrder->id,
        );

        $recipients = collect([$workOrder->assignedPersonnel, $workOrder->createdBy])->filter();

        $this->notifyUsers($recipients, $notification);

        $this->notifyRoles(
            User::ROLE_OPERATIONS,
            $notification,
        );
    }

    public function lowInventory(Material $material): void
    {
        $notification = new SystemNotification(
            type: SystemNotification::TYPE_LOW_INVENTORY,
            title: 'Low inventory level',
            message: sprintf(
                '%s (%s) is down to %s %s, at or below the reorder level of %s %s.',
                $material->name,
                $material->sku,
                rtrim(rtrim((string) $material->quantity_on_hand, '0'), '.'),
                $material->unit,
                rtrim(rtrim((string) $material->reorder_level, '0'), '.'),
                $material->unit,
            ),
            route: 'operations.dashboard',
        );

        $this->notifyRoles(User::ROLE_OPERATIONS, $notification);
        $this->notifyRoles(User::ROLE_ADMIN, $notification);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function notifyUsers(Collection $users, SystemNotification $notification): void
    {
        $users->unique('id')->each(fn (User $user) => $user->notify($notification));
    }

    private function notifyRoles(string $role, SystemNotification $notification): void
    {
        User::query()
            ->where('role', $role)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $user) => $user->notify($notification));
    }
}
