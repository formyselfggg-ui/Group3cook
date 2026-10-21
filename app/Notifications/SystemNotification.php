<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public const TYPE_WORK_ORDER_ASSIGNED = 'work_order_assigned';

    public const TYPE_WORK_ORDER_PRIORITY_CHANGED = 'work_order_priority_changed';

    public const TYPE_WORK_ORDER_SUBMITTED = 'work_order_submitted';

    public const TYPE_WORK_ORDER_RETURNED = 'work_order_returned';

    public const TYPE_WORK_ORDER_APPROVED = 'work_order_approved';

    public const TYPE_WORK_ORDER_COMPLETED = 'work_order_completed';

    public const TYPE_LOW_INVENTORY = 'low_inventory';

    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $route = null,
        public readonly ?int $workOrderId = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'route' => $this->route,
            'work_order_id' => $this->workOrderId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
