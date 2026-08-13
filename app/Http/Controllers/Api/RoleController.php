<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Role::with('permissions:id,name')->get(['id', 'name'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'sanctum']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json($role->load('permissions:id,name'), 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json($role->load('permissions:id,name'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['admin', 'user'], true)) {
            return response()->json(['message' => 'This role cannot be deleted.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    public function permissions(): JsonResponse
    {
        return response()->json(Permission::all(['id', 'name']));
    }
}
