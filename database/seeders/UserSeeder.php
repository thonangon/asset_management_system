<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->assignRole(Roles::SUPER_ADMIN->value);
        $superAdmin->update(['email_verified_at' => now(), 'status' => 'active']);

        $itManager = User::firstOrCreate(
            ['email' => 'itmanager@example.com'],
            ['name' => 'IT Manager', 'password' => Hash::make('password')]
        );
        $itManager->assignRole(Roles::IT_MANAGER->value);
        $itManager->update(['email_verified_at' => now(), 'status' => 'active']);

        $financeManager = User::firstOrCreate(
            ['email' => 'financemanager@example.com'],
            ['name' => 'Finance Manager', 'password' => Hash::make('password')]
        );
        $financeManager->assignRole(Roles::FINANCE_MANAGER->value);
        $financeManager->update(['email_verified_at' => now(), 'status' => 'active']);

        $riskManager = User::firstOrCreate(
            ['email' => 'riskmanager@example.com'],
            ['name' => 'Risk Manager', 'password' => Hash::make('password')]
        );
        $riskManager->assignRole(Roles::RISK_MANAGER->value);
        $riskManager->update(['email_verified_at' => now(), 'status' => 'active']);

        $auditor = User::firstOrCreate(
            ['email' => 'auditor@example.com'],
            ['name' => 'Auditor', 'password' => Hash::make('password')]
        );
        $auditor->assignRole(Roles::AUDIT_MANAGER->value);
        $auditor->update(['email_verified_at' => now(), 'status' => 'active']);

        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            ['name' => 'Employee', 'password' => Hash::make('password')]
        );
        $employee->assignRole(Roles::EMPLOYEE->value);
        $employee->update(['email_verified_at' => now(), 'status' => 'active']);
    }
}
