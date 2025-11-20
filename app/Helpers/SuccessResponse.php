<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class SuccessResponse
{
    /**
     * Generate standardized JSON success response
     *
     * @param string $message Success message
     * @param mixed $data Response data
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    public static function json(string $message = 'Success', $data = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        // Add data if provided
        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Generate resource created response
     *
     * @param string $message Success message
     * @param mixed $data Created resource data
     * @return JsonResponse
     */
    public static function created(string $message = 'Resource created successfully', $data = null): JsonResponse
    {
        return self::json($message, $data, 201);
    }

    /**
     * Generate resource updated response
     *
     * @param string $message Success message
     * @param mixed $data Updated resource data
     * @return JsonResponse
     */
    public static function updated(string $message = 'Resource updated successfully', $data = null): JsonResponse
    {
        return self::json($message, $data, 200);
    }

    /**
     * Generate resource deleted response
     *
     * @param string $message Success message
     * @return JsonResponse
     */
    public static function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return self::json($message, null, 200);
    }

    /**
     * Generate payment success response
     *
     * @param string $message Success message
     * @param array $data Payment data
     * @return JsonResponse
     */
    public static function paymentSuccess(string $message = 'Payment processed successfully', array $data = []): JsonResponse
    {
        return self::json($message, $data, 200);
    }
}