<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List users with their roles. Requires "view users" permission.
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email')
            ->paginate(15);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Replace a user's roles wholesale. Requires "manage users" permission.
     * Body: { "roles": ["editor", "viewer"] }
     */
    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles updated.',
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Grant a single direct permission to a user, bypassing roles.
     * Body: { "permission": "delete articles" }
     */
    public function grantPermission(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'permission' => ['required', Rule::exists('permissions', 'name')],
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
