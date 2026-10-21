<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the permission catalogue and the default grants for each role.
     */
    public function run(): void
    {
        foreach (Permission::catalogue() as $name => $meta) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'description' => $meta['description']],
            );
        }

        foreach (array_keys(User::ROLES) as $role) {
            $permissionIds = Permission::query()
                ->whereIn('name', Permission::defaultsForRole($role))
                ->pluck('id');

            DB::table('permission_role')->where('role', $role)->delete();

            DB::table('permission_role')->insert($permissionIds->map(fn (int $permissionId): array => [
                'role' => $role,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());

            Permission::markRoleConfigured($role);
        }
    }
}
