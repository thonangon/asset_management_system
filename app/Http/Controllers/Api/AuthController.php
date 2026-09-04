<?php

namespace App\Http\Controllers\Api;

use App\Enums\HttpResponse;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Register a new user and assign the default "employee" role.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $defaultRole = config('auth.default_role', 'employee');
        if (Role::where('name', $defaultRole)->where('guard_name', 'sanctum')->exists()) {
            $user->assignRole($defaultRole);
        }

        $token = $user->createToken($request->userAgent() ?? 'api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
            'token' => $token,
        ], 201);
    }
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), [], HttpResponse::VALIDATION_ERROR->value);
            }

            $loginInput = $request->input('email');
            $password = $request->input('password');
            $loginTypes = [];

            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $loginTypes['email'] = $loginInput;
            } else {
                return throw_msg('Please enter a valid email', HttpResponse::VALIDATION_ERROR->value);
            }

            $loginTypes['password'] = $password;
            $user = User::where('email', $loginTypes['email'])->first();

            if (!$user || !Hash::check($loginTypes['password'], $user->password)) {
                return $this->sendError(
                    'Unauthenticated user',
                    ['error' => 'Invalid credentials'],
                    HttpResponse::UNAUTHORIZED->value
                );
            }
            /***
             * Check if email or phone is verified here if needed
             */
            //require at least one verification
            if (is_null($user->email_verified_at)) {
                return $this->sendError(
                    'Account not verified. Please verify email or phone.',
                    [],
                    HttpResponse::UNAUTHORIZED->value
                );
            }

            // check disable account
            if ($user->status !== 'active') throw_msg('Your account has been temporarily disabled', HttpResponse::VALIDATION_ERROR->value);

            $result =  createUserToken($user);
            return $this->sendResponse($result, 'Authenticated');
        } catch (Exception $error) {
            return $this->sendError(
                $error->getMessage(),
                [],
                $error->getCode()
            );
        }
    }

    public function refreshToken(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'refresh_token' => 'required|string'
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), [], HttpResponse::VALIDATION_ERROR->value);
            }

            $refreshToken = $request->input('refresh_token');
            $userId = Cache::get('refresh_token_' . $refreshToken);
            if (!$userId) throw_msg('Invalid or expired refresh token', HttpResponse::UNAUTHORIZED->value);

            $user = User::find($userId);
            if (!$user) throw_msg('user not found', HttpResponse::NOT_FOUND->value);

            Cache::forget('refresh_token_' . $refreshToken);
            $result = createUserToken($user);

            return $this->sendResponse($result, 'Token refreshed');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode());
        }
    }

    public function logout()
    {
        $user = User::find(Auth::user()->id);

        $user->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|int|exists:users,id',
                'email' => 'required|string|email|max:255',
            ]);

            $user = User::where('id', $request->employee_id)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->email);
                })->first();
            if (!$user) throw_msg('Member not found', HttpResponse::NOT_FOUND->value);

            // $user->delete();
            $user->update([
                'is_active' => false,
            ]);

            return $this->sendResponse([], 'Account deleted successfully');
        } catch (Exception $e) {
            return $this->sendError(
                'Account deletion failed',
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out from all devices.']);
    }

    /**
     * Return the authenticated user plus their roles/permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
