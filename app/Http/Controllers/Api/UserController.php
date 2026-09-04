<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email')
            ->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'status' => $validated['status'] ?? 'active',
        ]);

        if (! empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        if (! empty($validated['permissions'])) {
            $user->givePermissionTo($validated['permissions']);
        }

        return response()->json([
            'message' => 'User created.',
            'user' => $user->only('id', 'name', 'email', 'status'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'exists:roles,name',
            ],
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles updated.',
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function grantPermission(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'permission' => [
                'required',
                'exists:permissions,name',
            ],
        ]);

        $user->givePermissionTo($validated['permission']);

        return response()->json([
            'message' => 'Permission granted.',
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
