<?php

return [

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    // Default guard used when a guard isn't explicitly passed.
    // Matches the "auth:sanctum" middleware used across the API routes.
    'default_guard_name' => 'sanctum',

    'display_permission_in_exception' => true,
    'display_role_in_exception' => true,
    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],

    /*
     * Credentials for the auto-bootstrapped super_admin account.
     * This account is created automatically on first boot (no seeder required).
     */
    'super_admin' => [
        'name' => 'Super Admin',
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
    ],

];
