<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;

class LoginRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $config = config('rate_limiting.login');
        $maxAttempts = $config['max_attempts'] ?? 5;
        $decayMinutes = $config['decay_minutes'] ?? 15;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = (int) ceil($seconds / 60);

            // Log suspicious activity
            \Log::warning('Login rate limit exceeded', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'user_agent' => $request->userAgent(),
                'available_in' => $seconds
            ]);

            // Redirect ke halaman login dengan flash message agar tampil elegan
            return redirect()->route('login')
                ->with('rate_limit_blocked', true)
                ->with('blocked_seconds', $seconds)
                ->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $minutes . ' menit.');
        }
        
        $response = $next($request);
        
        // If login failed (redirect back with errors), increment the rate limiter
        if ($response->isRedirect() && $request->session()->has('errors')) {
            RateLimiter::hit($key, $decayMinutes * 60);
            
            // Log failed login attempt if configured
            if (config('rate_limiting.log_events', true)) {
                \Log::info('Failed login attempt', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                    'attempts_remaining' => $maxAttempts - RateLimiter::attempts($key)
                ]);
            }
        }
        
        // If login successful, clear the rate limiter
        if ($response->isRedirect() && !$request->session()->has('errors')) {
            RateLimiter::clear($key);
            
            if (config('rate_limiting.log_events', true)) {
                \Log::info('Successful login', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email')
                ]);
            }
        }
        
        return $response;
    }
    
    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $email = $request->input('email', '');
        $ip = $request->ip();

        // Samakan format key dengan LoginRequest::throttleKey agar konsisten
        return Str::transliterate(Str::lower($email) . '|' . $ip);
    }
}