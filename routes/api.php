<?php

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OccupationController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('user')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->post('delete-account', [AuthController::class, 'deleteAccount']);
});

// Authenticated routes (any logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
});

// User listing: requires "view users" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_USERS->value])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});

// User management: requires "manage users" permission
// Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_USERS->value])->group(function () {
//     Route::post('/users', [UserController::class, 'store']);
//     Route::put('/users/{user}/roles', [UserController::class, 'updateRoles']);
//     Route::post('/users/{user}/permissions', [UserController::class, 'grantPermission']);
//     Route::delete('/users/{user}', [UserController::class, 'destroy']);
// });

// Role management: restricted to super_admin role
Route::middleware(['auth:sanctum', 'role:' . Roles::SUPER_ADMIN->value])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    Route::post('/permissions', [RoleController::class, 'storePermission']);
});

// Employee listing: requires "view employees" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_EMPLOYEES->value])->group(function () {
    Route::get('/employees/list', [EmployeeController::class, 'index']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
});

// Employee management: requires "manage employees" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_EMPLOYEES->value])->group(function () {
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
});

// Organization listing: requires "view organizations" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_ORGANIZATIONS->value])->group(function () {
    Route::get('/organizations/list', [OrganizationController::class, 'index']);
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
});

// Organization management: requires "manage organizations" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_ORGANIZATIONS->value])->group(function () {
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::put('/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy']);
});

// Department listing: requires "view departments" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_DEPARTMENTS->value])->group(function () {
    Route::get('/departments/list', [DepartmentController::class, 'index']);
    Route::get('/departments/{department}', [DepartmentController::class, 'show']);
});

// Department management: requires "manage departments" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_DEPARTMENTS->value])->group(function () {
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_OCCUPATIONS->value])->group(function () {
    Route::get('/occupations', [OccupationController::class, 'index']);
    Route::post('/occupations', [OccupationController::class, 'store']);
    Route::get('/occupations/{occupation}', [OccupationController::class, 'show']);
    Route::put('/occupations/{occupation}', [OccupationController::class, 'edit']);
    Route::delete('/occupations/{occupation}', [OccupationController::class, 'destroy']);
});