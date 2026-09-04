<?php

use App\Http\Resources\UserResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

if (!function_exists('throw_msg')) {
    /**
     * Throw an exception with a custom error message.
     *
     * @param string $message
     * @param int $code
     * @throws \Exception
     */
    function throw_msg(string $message, int $code = 400)
    {
        throw new \Exception($message, $code);
    }

    function generateUniqueCode($limit = 10, $prefix = ''): string
    {
        $code = substr(number_format(time() * rand(), 0, '', ''), 0, $limit);
        // return $code;
        return $prefix . $code;
    }

    function createUserToken($user): array
    {
        $expires_in = config('sanctum.expiration');
        $expiresAt = $expires_in ? now()->addMinutes($expires_in) : null;
        $accessToken = $user->createToken('authToken', ["*"], $expiresAt)->plainTextToken;
        $refreshToken = Str::random(60);
        Cache::forever('refresh_token_' . $refreshToken, $user->id);
        return [
            'access_token' => $accessToken,
            'expires_in_min' => $expires_in,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user)
        ];
    }
    
}
