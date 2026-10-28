<?php

namespace App\Models;

use Database\Factories\WorkOrderMaterialUsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderMaterialUsage extends Model
{
    protected $fillable = [
        'work_order_id',
        'material_id',
        'user_id',
        'quantity',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'used_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @use HasFactory<WorkOrderMaterialUsageFactory> */
    use HasFactory;
}
