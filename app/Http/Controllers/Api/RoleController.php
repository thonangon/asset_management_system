<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'sanctum'),
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'exists:permissions,name',
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json($role->load('permissions:id,name'), 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if ($role->name === Roles::SUPER_ADMIN->value) {
            return response()->json(['message' => 'The super_admin role cannot be renamed.'], 422);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'sanctum')->ignore($role->id),
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'exists:permissions,name',
            ],
        ]);

        if (! empty($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json($role->load('permissions:id,name'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->name === Roles::SUPER_ADMIN->value) {
            return response()->json(['message' => 'The super_admin role cannot be deleted.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    public function permissions(): JsonResponse
    {
        return response()->json(Permission::all(['id', 'name']));
    }

    public function storePermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->where('guard_name', 'sanctum'),
            ],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'sanctum',
        ]);

        return response()->json($permission, 201);
    }
}
