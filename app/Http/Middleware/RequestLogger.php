<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = Str::uuid()->toString();
        
        $debug = (bool) config('app.debug');
        if ($debug) {
            $this->logRequest($request, $requestId);
        }
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        $this->logResponse($request, $response, $requestId, $duration);
        
        return $response;
    }
    
    private function logRequest(Request $request, string $requestId): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'payload' => $this->sanitizePayload($request->all()),
            'timestamp' => now()->toISOString(),
        ];
        
        Log::channel('daily')->info('HTTP Request', $logData);
    }
    
    private function logResponse(Request $request, Response $response, string $requestId, float $duration): void
    {
        $logData = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            'timestamp' => now()->toISOString(),
        ];
        
        // Log slow requests
        if ($duration > 1000) { // > 1 second
            Log::channel('daily')->warning('Slow Request Detected', array_merge($logData, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]));
        }
        
        // Log error responses
        if ($response->getStatusCode() >= 400) {
            Log::channel('daily')->error('HTTP Error Response', array_merge($logData, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]));
        } else {
            if (config('app.debug')) {
                Log::channel('daily')->info('HTTP Response', $logData);
            }
        }
    }
    
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }
        
        return $headers;
    }
    
    private function sanitizePayload(array $payload): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***REDACTED***';
            }
        }
        
        return $payload;
    }
}
