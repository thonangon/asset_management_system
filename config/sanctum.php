<?php

use Laravel\Sanctum\Sanctum;

return [

    'stateful' => explode(',', (string) env(
        'SANCTUM_STATEFUL_DOMAINS',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
    )),

    'guard' => ['web'],

    // Personal access tokens never expire by default. Set
    // SANCTUM_TOKEN_EXPIRATION (in minutes) in .env to enforce expiry —
    // e.g. 43200 for 30 days.
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION') ? (int) env('SANCTUM_TOKEN_EXPIRATION') : null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
