<?php

use App\Models\User;

return [


    'defaults' => [
        'guard' => 'sanctum',
        'passwords' => 'users',
    ],


    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Used by the "auth:sanctum" middleware on every API route.
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],


    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

    /**
     * Default role assigned to newly self-registered users.
     * Only assigned if a role with this name exists in the database.
     */
    'default_role' => env('DEFAULT_ROLE', 'employee'),

];
