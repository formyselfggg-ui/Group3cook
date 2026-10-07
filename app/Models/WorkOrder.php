<?php

namespace App\Models;

use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_FOR_REVIEW = 'for_review';

    public const STATUS_COMPLETED = 'completed';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ASSIGNED => 'Assigned',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_FOR_REVIEW => 'Waiting for review',
        self::STATUS_COMPLETED => 'Completed',
    ];

    public const PRIORITIES = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_NORMAL => 'Normal',
        self::PRIORITY_HIGH => 'High',
        self::PRIORITY_URGENT => 'Urgent',
    ];

    protected $fillable = [
        'work_order_number',
        'title',
        'description',
        'category',
        'priority',
        'assigned_personnel_id',
        'asset_id',
        'created_by_user_id',
        'date_assigned',
        'scheduled_at',
        'status',
        'work_performed',
        'field_notes',
        'return_notes',
        'photo_paths',
        'submitted_at',
        'completion_at',
        'supervisor_approved_by_id',
        'supervisor_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'date_assigned' => 'datetime',
            'scheduled_at' => 'datetime',
            'photo_paths' => 'array',
            'submitted_at' => 'datetime',
            'completion_at' => 'datetime',
            'supervisor_approved_at' => 'datetime',
        ];
    }

    public function assignedPersonnel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_personnel_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(WorkOrderMaterialUsage::class);
    }

    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory;
}
