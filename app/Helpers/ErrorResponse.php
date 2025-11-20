<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ErrorResponse
{
    /**
     * Generate standardized JSON error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param string|null $errorCode Application-specific error code
     * @param array $data Additional error data
     * @param bool $log Whether to log the error
     * @return JsonResponse
     */
    public static function json(
        string $message, 
        int $code = 500, 
        ?string $errorCode = null, 
        array $data = [],
        bool $log = true
    ): JsonResponse {
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        // Add error code if provided
        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        // Add additional data if provided
        if (!empty($data)) {
            $response['data'] = $data;
        }

        // Add debug information in development
        if (config('app.debug') && $code >= 500) {
            $response['debug'] = [
                'environment' => config('app.env'),
                'trace_id' => uniqid('err_', true)
            ];
        }

        // Log error if requested
        if ($log) {
            $logLevel = $code >= 500 ? 'error' : 'warning';
            Log::{$logLevel}('API Error Response', [
                'message' => $message,
                'code' => $code,
                'error_code' => $errorCode,
                'data' => $data,
                'url' => request()->url(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
            ]);
        }

        return response()->json($response, $code);
    }

    /**
     * Generate validation error response
     *
     * @param array $errors Validation errors
     * @param string $message Main error message
     * @return JsonResponse
     */
    public static function validation(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::json(
            message: $message,
            code: 422,
            errorCode: 'VALIDATION_ERROR',
            data: ['errors' => $errors],
            log: false // Don't log validation errors
        );
    }

    /**
     * Generate authentication error response
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::json(
            message: $message,
            code: 401,
            errorCode: 'UNAUTHORIZED',
            log: false
        );
    }

    /**
     * Generate forbidden error response
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::json(
            message: $message,
            code: 403,
            errorCode: 'FORBIDDEN',
            log: false
        );
    }

    /**
     * Generate not found error response
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::json(
            message: $message,
            code: 404,
            errorCode: 'NOT_FOUND',
            log: false
        );
    }

    /**
     * Generate payment error response
     *
     * @param string $message Error message
     * @param array $data Additional payment data
     * @return JsonResponse
     */
    public static function paymentError(string $message, array $data = []): JsonResponse
    {
        return self::json(
            message: $message,
            code: 400,
            errorCode: 'PAYMENT_ERROR',
            data: $data
        );
    }

    /**
     * Generate rate limiting error response
     *
     * @param string $message Error message
     * @param int $retryAfter Seconds to wait before retry
     * @return JsonResponse
     */
    public static function rateLimited(string $message = 'Rate limit exceeded', int $retryAfter = 60): JsonResponse
    {
        return self::json(
            message: $message,
            code: 429,
            errorCode: 'RATE_LIMITED',
            data: ['retry_after' => $retryAfter]
        );
    }

    /**
     * Generate server error response
     *
     * @param string $message Error message
     * @param \Throwable|null $exception Exception instance for logging
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal server error', ?\Throwable $exception = null): JsonResponse
    {
        // Log the exception if provided
        if ($exception) {
            Log::error('Server Error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => request()->url(),
                'method' => request()->method(),
                'user_id' => auth()->id(),
            ]);
        }

        return self::json(
            message: $message,
            code: 500,
            errorCode: 'SERVER_ERROR'
        );
    }
}