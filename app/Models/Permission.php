<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    public const MANAGE_USERS = 'users.manage';

    public const MANAGE_PERMISSIONS = 'permissions.manage';

    public const VIEW_ALL_WORK_ORDERS = 'work_orders.view_all';

    public const MANAGE_ASSETS = 'assets.manage';

    public const MANAGE_MATERIALS = 'materials.manage';

    public const CREATE_WORK_ORDERS = 'work_orders.create';

    public const ASSIGN_WORK_ORDERS = 'work_orders.assign';

    public const REVIEW_WORK_ORDERS = 'work_orders.review';

    public const CLOSE_WORK_ORDERS = 'work_orders.close';

    public const DELETE_WORK_ORDERS = 'work_orders.delete';

    public const UPDATE_PRIORITY = 'work_orders.priority';

    public const VIEW_REPORTS = 'reports.view';

    public const RECORD_MATERIALS = 'materials.record';

    protected $fillable = [
        'name',
        'label',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }

    public static function catalogue(): array
    {
        return [
            self::MANAGE_USERS => [
                'label' => 'Manage user accounts',
                'description' => 'Create, edit and deactivate accounts and assign roles.',
            ],
            self::MANAGE_PERMISSIONS => [
                'label' => 'Manage role permissions',
                'description' => 'Grant or revoke permissions for each role and individual account.',
            ],
            self::VIEW_ALL_WORK_ORDERS => [
                'label' => 'View all work orders',
                'description' => 'See work orders that are not assigned to the account holder.',
            ],
            self::MANAGE_ASSETS => [
                'label' => 'Manage asset records',
                'description' => 'Register assets and update their condition and status.',
            ],
            self::MANAGE_MATERIALS => [
                'label' => 'Manage inventory',
                'description' => 'Register materials and adjust stock levels and reorder points.',
            ],
            self::CREATE_WORK_ORDERS => [
                'label' => 'Create work orders',
                'description' => 'Raise new work orders and set their priority and schedule.',
            ],
            self::ASSIGN_WORK_ORDERS => [
                'label' => 'Assign field personnel',
                'description' => 'Assign work orders to field crew and schedule the work.',
            ],
            self::REVIEW_WORK_ORDERS => [
                'label' => 'Review submissions',
                'description' => 'Approve completed work or return it for correction.',
            ],
            self::CLOSE_WORK_ORDERS => [
                'label' => 'Close work orders',
                'description' => 'Close approved work orders and update maintenance records.',
            ],
            self::DELETE_WORK_ORDERS => [
                'label' => 'Delete work orders',
                'description' => 'Delete work orders that field personnel have not started.',
            ],
            self::UPDATE_PRIORITY => [
                'label' => 'Change work order priority',
                'description' => 'Adjust the priority of a work order that has not been started.',
            ],
            self::VIEW_REPORTS => [
                'label' => 'View operational reports',
                'description' => 'Review and export operational reporting.',
            ],
            self::RECORD_MATERIALS => [
                'label' => 'Record material usage',
                'description' => 'Record materials used against an assigned work order.',
            ],
        ];
    }

    public static function defaultsForRole(string $role): array
    {
        return match ($role) {
            User::ROLE_ADMIN => array_keys(self::catalogue()),
            User::ROLE_OPERATIONS => [
                self::VIEW_ALL_WORK_ORDERS,
                self::CREATE_WORK_ORDERS,
                self::MANAGE_ASSETS,
                self::MANAGE_MATERIALS,
                self::VIEW_REPORTS,
            ],
            User::ROLE_SUPERVISOR => [
                self::VIEW_ALL_WORK_ORDERS,
                self::CREATE_WORK_ORDERS,
                self::ASSIGN_WORK_ORDERS,
                self::REVIEW_WORK_ORDERS,
                self::CLOSE_WORK_ORDERS,
                self::DELETE_WORK_ORDERS,
                self::UPDATE_PRIORITY,
                self::VIEW_REPORTS,
            ],
            User::ROLE_FIELD_PERSONNEL => [
                self::RECORD_MATERIALS,
            ],
            default => [],
        };
    }

    /**
     * Determine whether a role has a stored permission configuration.
     */
    public static function roleIsConfigured(string $role): bool
    {
        return DB::table('role_permission_configs')->where('role', $role)->exists();
    }

    /**
     * The permissions effectively granted to a role.
     *
     * Roles without a stored configuration fall back to the built-in defaults.
     *
     * @return Collection<int, string>
     */
    public static function namesForRole(string $role): Collection
    {
        if (self::roleIsConfigured($role)) {
            return DB::table('permission_role')
                ->where('role', $role)
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->pluck('permissions.name');
        }

        return collect(self::defaultsForRole($role));
    }

    /**
     * Mark a role as having a stored permission configuration.
     */
    public static function markRoleConfigured(string $role): void
    {
        DB::table('role_permission_configs')->updateOrInsert(
            ['role' => $role],
            ['updated_at' => now(), 'created_at' => now()],
        );
    }

    /**
     * @use HasFactory<PermissionFactory>
     */
    use HasFactory;
}
