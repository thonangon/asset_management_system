<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        $permissions = [
            'view users',
            'manage users',
            'manage roles',
            'view articles',
            'edit articles',
            'delete articles',
            'publish articles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
        $editor->syncPermissions(['view articles', 'edit articles', 'publish articles']);

        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
        $user->syncPermissions(['view articles']);
    }
}
