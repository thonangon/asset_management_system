<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Authenticated routes (any logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
});

// Admin-only: user management, requires specific permissions
Route::middleware(['auth:sanctum', 'permission:view users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'permission:manage users'])->group(function () {
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles']);
    Route::post('/users/{user}/permissions', [UserController::class, 'grantPermission']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

// Role management: restricted to the admin role directly
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::get('/permissions', [RoleController::class, 'permissions']);
});

// Example resource protected by granular permissions
Route::middleware(['auth:sanctum', 'permission:edit articles'])
    ->put('/articles/{id}', fn (int $id) => response()->json(['message' => "Article {$id} updated."]));

Route::middleware(['auth:sanctum', 'permission:delete articles'])
    ->delete('/articles/{id}', fn (int $id) => response()->json(['message' => "Article {$id} deleted."]));
