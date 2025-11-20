<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class RateLimitLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Log successful requests if they have rate limit headers
            if ($response->headers->has('X-RateLimit-Limit')) {
                $this->logRateLimitInfo($request, $response, 'success');
            }
            
            return $response;
        } catch (ThrottleRequestsException $e) {
            // Log rate limit exceeded
            $this->logRateLimitInfo($request, null, 'exceeded', $e);
            
            throw $e;
        }
    }
    
    /**
     * Log rate limiting information.
     */
    protected function logRateLimitInfo(Request $request, ?Response $response, string $status, ?ThrottleRequestsException $exception = null): void
    {
        if (!config('rate_limiting.log_events', true)) {
            return;
        }
        
        $logData = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->url(),
            'status' => $status,
        ];
        
        if ($request->user()) {
            $logData['user_id'] = $request->user()->id;
            $logData['user_email'] = $request->user()->email;
        }
        
        if ($response) {
            $logData['rate_limit'] = [
                'limit' => $response->headers->get('X-RateLimit-Limit'),
                'remaining' => $response->headers->get('X-RateLimit-Remaining'),
                'reset' => $response->headers->get('X-RateLimit-Reset'),
            ];
        }
        
        if ($exception) {
            $logData['retry_after'] = $exception->getHeaders()['Retry-After'] ?? null;
        }
        
        if ($status === 'exceeded') {
            Log::warning('Rate limit exceeded', $logData);
        } else {
            Log::info('Rate limit check', $logData);
        }
    }
}