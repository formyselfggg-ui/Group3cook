<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()->orderBy('id')->get();

        $grantsByRole = [];

        foreach (array_keys(User::ROLES) as $role) {
            $grantsByRole[$role] = Permission::namesForRole($role)->all();
        }

        return view('admin.roles', [
            'permissions' => $permissions,
            'roles' => User::ROLES,
            'grantsByRole' => $grantsByRole,
            'configuredRoles' => array_values(array_filter(
                array_keys(User::ROLES),
                fn (string $role): bool => Permission::roleIsConfigured($role),
            )),
            'users' => User::query()->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function updateRole(Request $request, string $role): RedirectResponse
    {
        abort_unless(array_key_exists($role, User::ROLES), 404);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        DB::transaction(function () use ($request, $role, $permissionIds): void {
            DB::table('permission_role')->where('role', $role)->delete();

            DB::table('permission_role')->insert(array_map(
                fn (int $permissionId): array => [
                    'role' => $role,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $permissionIds,
            ));

            Permission::markRoleConfigured($role);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'role_permissions_updated',
                'record_type' => User::class,
                'record_id' => $request->user()->id,
                'details' => 'Updated permissions for the '.$role.' role.',
            ]);
        });

        return redirect()->route('admin.roles.index')->with('status', 'Permissions updated for the '.$role.' role.');
    }

    public function resetRole(Request $request, string $role): RedirectResponse
    {
        abort_unless(array_key_exists($role, User::ROLES), 404);

        DB::transaction(function () use ($request, $role): void {
            DB::table('permission_role')->where('role', $role)->delete();
            DB::table('role_permission_configs')->where('role', $role)->delete();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'role_permissions_reset',
                'record_type' => User::class,
                'record_id' => $request->user()->id,
                'details' => 'Restored the default permissions for the '.$role.' role.',
            ]);
        });

        return redirect()->route('admin.roles.index')
            ->with('status', 'Default permissions restored for the '.$role.' role.');
    }

    public function toggleUserPermission(Request $request, User $user, Permission $permission): RedirectResponse
    {
        $explicitGrant = $user->permissions()->whereKey($permission->id)->exists();

        if ($explicitGrant) {
            $user->revokePermission($permission);

            $action = 'user_permission_revoked';
            $message = 'Removed the explicit '.$permission->label.' grant from '.$user->email.'.';
        } else {
            $user->grantPermission($permission);

            $action = 'user_permission_granted';
            $message = 'Granted '.$permission->label.' to '.$user->email.'.';
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'record_type' => User::class,
            'record_id' => $user->id,
            'details' => $message,
        ]);

        return redirect()->route('admin.roles.index')->with('status', $message);
    }
}
