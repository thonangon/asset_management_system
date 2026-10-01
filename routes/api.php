<?php

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Http\Controllers\Api\AssetAssignmentController;
use App\Http\Controllers\Api\AssetCategoryController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetTransferController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DepreciationController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OccupationController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarrantyController;
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

// Location listing: requires "view locations" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_LOCATIONS->value])->group(function () {
    Route::get('/locations/list', [LocationController::class, 'index']);
    Route::get('/locations/{location}', [LocationController::class, 'show']);
});

// Location management: requires "manage locations" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_LOCATIONS->value])->group(function () {
    Route::post('/locations', [LocationController::class, 'store']);
    Route::put('/locations/{location}', [LocationController::class, 'update']);
    Route::delete('/locations/{location}', [LocationController::class, 'destroy']);
});

// Asset category listing: requires "view categories" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_CATEGORIES->value])->group(function () {
    Route::get('/asset-categories/list', [AssetCategoryController::class, 'index']);
    Route::get('/asset-categories/{assetCategory}', [AssetCategoryController::class, 'show']);
});

// Asset category management: requires "manage categories" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_CATEGORIES->value])->group(function () {
    Route::post('/asset-categories', [AssetCategoryController::class, 'store']);
    Route::put('/asset-categories/{assetCategory}', [AssetCategoryController::class, 'update']);
    Route::delete('/asset-categories/{assetCategory}', [AssetCategoryController::class, 'destroy']);
});

// Asset listing: requires "view assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_ASSETS->value])->group(function () {
    Route::get('/assets/list', [AssetController::class, 'index']);
    Route::get('/assets/{asset}', [AssetController::class, 'show']);
});

// Asset management: requires "manage assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_ASSETS->value])->group(function () {
    Route::post('/assets', [AssetController::class, 'store']);
    Route::put('/assets/{asset}', [AssetController::class, 'update']);
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy']);
});

// Warranty & Depreciation listing: requires "view asset history" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_ASSET_HISTORY->value])->group(function () {
    Route::get('/warranties/list', [WarrantyController::class, 'index']);
    Route::get('/warranties/{warranty}', [WarrantyController::class, 'show']);
    Route::get('/depreciations/list', [DepreciationController::class, 'index']);
    Route::get('/depreciations/{depreciation}', [DepreciationController::class, 'show']);
});

// Asset assignment listing: requires "view assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_ASSETS->value])->group(function () {
    Route::get('/asset-assignments/list', [AssetAssignmentController::class, 'index']);
    Route::get('/asset-assignments/{assetAssignment}', [AssetAssignmentController::class, 'show']);
});

// Asset assignment management: requires "manage assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_ASSETS->value])->group(function () {
    Route::post('/asset-assignments', [AssetAssignmentController::class, 'store']);
    Route::put('/asset-assignments/{assetAssignment}', [AssetAssignmentController::class, 'update']);
    Route::delete('/asset-assignments/{assetAssignment}', [AssetAssignmentController::class, 'destroy']);
});

// Asset transfer listing: requires "view assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::VIEW_ASSETS->value])->group(function () {
    Route::get('/asset-transfers/list', [AssetTransferController::class, 'index']);
    Route::get('/asset-transfers/{assetTransfer}', [AssetTransferController::class, 'show']);
});

// Asset transfer management: requires "manage assets" permission
Route::middleware(['auth:sanctum', 'permission:' . Permissions::MANAGE_ASSETS->value])->group(function () {
    Route::post('/asset-transfers', [AssetTransferController::class, 'store']);
    Route::put('/asset-transfers/{assetTransfer}', [AssetTransferController::class, 'update']);
    Route::delete('/asset-transfers/{assetTransfer}', [AssetTransferController::class, 'destroy']);
});