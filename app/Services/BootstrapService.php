<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class BootstrapService
{
    /**
     * Ensure a super_admin role and user exist so the system is never locked out,
     * even when no seeders have been run.
     */
    public function ensureSuperAdmin(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $guard = 'sanctum';

        $role = Role::query()->where('name', 'super_admin')->where('guard_name', $guard)->first();
        if (! $role) {
            $role = Role::create(['name' => 'super_admin', 'guard_name' => $guard]);
        }

        $email = config('permission.super_admin.email');
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => config('permission.super_admin.name', 'Super Admin'),
                'email' => $email,
                'password' => Hash::make(config('permission.super_admin.password')),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        if (! $user->hasRole('super_admin')) {
            $user->assignRole($role);
        }
    }
}
