<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    public const STATUSES = [
        'active' => 'Active',
        'under_maintenance' => 'Under maintenance',
        'damaged' => 'Damaged',
        'retired' => 'Retired',
    ];

    protected $fillable = [
        'asset_number',
        'asset_type',
        'name',
        'serial_number',
        'current_status',
        'condition',
        'installed_on',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /** @use HasFactory<AssetFactory> */
    use HasFactory;
}
