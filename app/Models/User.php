<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function rolePermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role', 'permission_id', 'role');
    }

    /**
     * The permission names effectively granted to the account.
     *
     * @return array<int, string>
     */
    public function effectivePermissions(): array
    {
        if ($this->role === self::ROLE_ADMIN) {
            return array_keys(Permission::catalogue());
        }

        $granted = Permission::namesForRole($this->role)->all();

        foreach ($this->permissions as $permission) {
            $granted[] = $permission->name;
        }

        return array_values(array_unique($granted));
    }

    public function hasPermissionTo(string $permission): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        if ($this->relationLoaded('permissions') && $this->permissions->contains('name', $permission)) {
            return true;
        }

        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return in_array($permission, Permission::namesForRole($this->role)->all(), true);
    }

    public function grantPermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }
}
