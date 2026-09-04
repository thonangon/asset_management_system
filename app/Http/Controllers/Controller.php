<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse; //
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send success response.
     *
     * @param mixed $result
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function sendResponse($result, $message, $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send error response.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        // Ensure the status code is a valid integer and not 0
        // If $code is 0 or not a valid HTTP status, default to 500
        $statusCode = (int) $code;
        if ($statusCode < 100 || $statusCode >= 600 || $statusCode === 0) {
            $statusCode = 500; // Default to 500 Internal Server Error for invalid codes
        }

        return response()->json($response, $statusCode);
    }
}
