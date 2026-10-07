<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'email_verified_at', 'remember_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_OPERATIONS = 'operations';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_FIELD_PERSONNEL = 'field_personnel';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_OPERATIONS => 'Operations / Engineering',
        self::ROLE_SUPERVISOR => 'Supervisor / Dispatcher',
        self::ROLE_FIELD_PERSONNEL => 'Field personnel',
    ];

    public function createdWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'created_by_user_id');
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function assignedWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'assigned_personnel_id');
    }
}
