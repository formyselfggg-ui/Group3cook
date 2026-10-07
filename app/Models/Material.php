<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'unit',
        'quantity_on_hand',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:2',
            'reorder_level' => 'decimal:2',
        ];
    }

    public function workOrderUsages(): HasMany
    {
        return $this->hasMany(WorkOrderMaterialUsage::class);
    }

    /** @use HasFactory<MaterialFactory> */
    use HasFactory;
}
