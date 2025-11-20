<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityScanner
{
    protected \App\Services\SecurityService $security;
    protected array $exemptRoutes;
    protected int $blockingThreshold;
    protected int $scoreDecayMinutes;
    protected bool $blockingEnabled;
    // New: control blocking scope
    protected array $blockOnMethods;
    protected array $sensitiveRoutes;

    public function __construct(\App\Services\SecurityService $security)
    {
        $this->security = $security;
        $this->exemptRoutes = config('security.scanner.exempt_routes', ['/login','/register','/password/*','/auth/*']);
        $this->blockingThreshold = config('security.scanner.blocking_threshold', app()->environment('production') ? 100 : 50);
        $this->scoreDecayMinutes = config('security.scanner.score_decay_minutes', 30);
        $this->blockingEnabled = config('security.scanner.blocking_enabled', true);
        // New: load configs
        $this->blockOnMethods = array_map('strtoupper', config('security.scanner.block_on_methods', ['POST','PUT','PATCH','DELETE']));
        $this->sensitiveRoutes = config('security.scanner.sensitive_route_patterns', ['/dashboard*','/profile*','/checkout*','/payment*','/admin/*']);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip security scanning in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }
        
        // Skip security scanning for localhost in development
        if (app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1', 'localhost'])) {
            return $next($request);
        }
        
        // Skip scanning for static assets and service worker to avoid false positives
        if ($this->isStaticAsset($request)) {
            return $next($request);
        }

        // Skip scanning for exempt routes to reduce false positives
        foreach ($this->exemptRoutes as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }
        
        // Check for various security threats
        $this->scanForSqlInjection($request);
        $this->scanForXssAttempts($request);
        $this->scanForPathTraversal($request);
        $this->scanForCommandInjection($request);
        $this->scanForFileInclusion($request);
        $this->scanForSuspiciousUserAgents($request);
        $this->scanForBotActivity($request);
        $this->scanForBruteForceAttempts($request);
        
        // Check if IP should be blocked
        if ($this->shouldBlockRequest($request)) {
            return $this->blockRequest($request);
        }
        
        return $next($request);
    }
    
    /**
     * Scan for SQL injection attempts
     */
    private function scanForSqlInjection(Request $request): void
    {
        if (!config('security.database.detect_sql_injection', true)) {
            return;
        }
        
        $sqlPatterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b.*\bwhere\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bor\b.*1\s*=\s*1)/i',
            '/(\band\b.*1\s*=\s*1)/i',
            '/(\bor\b.*\btrue\b)/i',
            '/(\bunion\b.*\ball\b.*\bselect\b)/i',
            '/(\'.*\bor\b.*\'.*=.*\')/i',
            '/(\bhaving\b.*\bcount\b.*\*)/i',
            '/(\bexec\b.*\bxp_)/i',
            '/(\bsp_executesql\b)/i',
        ];
        
        $allInput = array_merge(
            $request->query->all(),
            $request->request->all(),
            $request->headers->all()
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat('sql_injection', $request, [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                        ]);
                        
                        $this->incrementThreatScore($request, 'sql_injection', 10);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Scan for XSS attempts
     */
    private function scanForXssAttempts(Request $request): void
    {
        if (!config('security.monitoring.log_xss_attempts', true)) {
            return;
        }
        
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<applet[^>]*>/i',
            '/<meta[^>]*>/i',
            '/<link[^>]*>/i',
            '/expression\s*\(/i',
            '/vbscript:/i',
            '/data:text\/html/i',
            '/<svg[^>]*onload/i',
            '/<img[^>]*onerror/i',
        ];
        
        $allInput = array_merge(
            $request->query->all(),
            $request->request->all()
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat('xss_attempt', $request, [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                        ]);
                        
                        $this->incrementThreatScore($request, 'xss', 8);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Scan for path traversal attempts
     */
    private function scanForPathTraversal(Request $request): void
    {
        $pathTraversalPatterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/',
            '/%2e%2e\\\\/',
            '/\.\.\%2f/',
            '/\.\.\%5c/',
            '/\.\.%252f/',
            '/\.\.%255c/',
        ];
        
        $allInput = array_merge(
            $request->query->all(),
            $request->request->all(),
            [$request->getPathInfo()]
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($pathTraversalPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat('path_traversal', $request, [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                        ]);
                        
                        $this->incrementThreatScore($request, 'path_traversal', 7);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Scan for command injection attempts
     */
    private function scanForCommandInjection(Request $request): void
    {
        $commandPatterns = [
            '/;\s*(cat|ls|pwd|whoami|id|uname)/i',
            '/\|\s*(cat|ls|pwd|whoami|id|uname)/i',
            '/&&\s*(cat|ls|pwd|whoami|id|uname)/i',
            '/`[^`]*`/',
            '/\$\([^)]*\)/',
            '/;\s*rm\s+-rf/i',
            '/;\s*wget\s+/i',
            '/;\s*curl\s+/i',
            '/;\s*nc\s+/i',
            '/;\s*netcat\s+/i',
        ];
        
        $allInput = array_merge(
            $request->query->all(),
            $request->request->all()
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($commandPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat('command_injection', $request, [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                        ]);
                        
                        $this->incrementThreatScore($request, 'command_injection', 9);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Scan for file inclusion attempts
     */
    private function scanForFileInclusion(Request $request): void
    {
        $fileInclusionPatterns = [
            '/\/etc\/passwd/i',
            '/\/etc\/shadow/i',
            '/\/proc\/self\/environ/i',
            '/\/proc\/version/i',
            '/\/windows\/system32/i',
            '/php:\/\/filter/i',
            '/php:\/\/input/i',
            '/data:\/\/text/i',
            '/file:\/\/\//i',
            '/expect:\/\//i',
        ];
        
        $allInput = array_merge(
            $request->query->all(),
            $request->request->all()
        );
        
        foreach ($allInput as $key => $value) {
            if (is_string($value)) {
                foreach ($fileInclusionPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat('file_inclusion', $request, [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern,
                        ]);
                        
                        $this->incrementThreatScore($request, 'file_inclusion', 8);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Scan for suspicious user agents
     */
    private function scanForSuspiciousUserAgents(Request $request): void
    {
        $userAgent = $request->userAgent();
        
        if (!$userAgent) {
            $this->logSecurityThreat('missing_user_agent', $request);
            $this->incrementThreatScore($request, 'suspicious_ua', 3);
            return;
        }

        // Allow common mobile/browser user agents to reduce false positives
        $allowedPatterns = config('security.scanner.ua_allowed_patterns', []);
        foreach ($allowedPatterns as $allowed) {
            if (@preg_match($allowed, $userAgent)) {
                return;
            }
        }
        
        $suspiciousPatterns = [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/masscan/i',
            '/zap/i',
            '/burp/i',
            '/acunetix/i',
            '/nessus/i',
            '/openvas/i',
            '/w3af/i',
            '/havij/i',
            '/pangolin/i',
            '/python-requests/i',
            '/curl/i',
            '/wget/i',
            '/libwww/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $this->logSecurityThreat('suspicious_user_agent', $request, [
                    'user_agent' => $userAgent,
                    'pattern' => $pattern,
                ]);
                
                $this->incrementThreatScore($request, 'suspicious_ua', 6);
                break;
            }
        }
    }
    
    /**
     * Scan for bot activity
     */
    private function scanForBotActivity(Request $request): void
    {
        $ip = $this->security->resolveClientIp($request);
        $userAgent = $request->userAgent();
        
        // More lenient request frequency for production
        $requestThreshold = app()->environment('production') ? 120 : 60;
        
        // Check request frequency
        $requestKey = "requests_per_minute_{$ip}";
        $requestCount = $this->cacheGet($requestKey, 0);
        
        if ($requestCount > $requestThreshold) { // More lenient for production
            $this->logSecurityThreat('high_frequency_requests', $request, [
                'requests_per_minute' => $requestCount,
            ]);
            
            $this->incrementThreatScore($request, 'bot_activity', 5);
        }
        
        $this->cachePut($requestKey, $requestCount + 1, 60);
        
        // Check for bot-like behavior patterns
        $botPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/harvester/i',
        ];
        
        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                // This might be legitimate, so lower score
                $this->incrementThreatScore($request, 'bot_activity', 2);
                break;
            }
        }
    }
    
    /**
     * Scan for brute force attempts
     */
    private function scanForBruteForceAttempts(Request $request): void
    {
        $ip = $this->security->resolveClientIp($request);
        $path = $request->getPathInfo();
        
        // More lenient thresholds for production
        $loginThreshold = app()->environment('production') ? 20 : 10;
        $adminThreshold = app()->environment('production') ? 10 : 5;
        
        // Check for login attempts (POST only)
        if ($request->isMethod('POST') && (str_contains($path, '/login') || str_contains($path, '/auth'))) {
            $loginKey = "login_attempts_{$ip}";
            $attempts = $this->cacheGet($loginKey, 0);
            
            if ($attempts > $loginThreshold) { // More lenient for production
                $this->logSecurityThreat('brute_force_login', $request, [
                    'attempts' => $attempts,
                ]);
                
                $this->incrementThreatScore($request, 'brute_force', 8);
            }
            
            $this->cachePut($loginKey, $attempts + 1, 3600); // 1 hour
        }
        
        // Check for admin panel access attempts (only when unauthenticated)
        if (!auth()->check() && (str_contains($path, '/admin') || str_contains($path, '/wp-admin'))) {
            $adminKey = "admin_attempts_{$ip}";
            $attempts = $this->cacheGet($adminKey, 0);
            
            if ($attempts > $adminThreshold) {
                $this->logSecurityThreat('admin_brute_force', $request, [
                    'attempts' => $attempts,
                ]);
                
                $this->incrementThreatScore($request, 'brute_force', 7);
            }
            
            $this->cachePut($adminKey, $attempts + 1, 3600);
        }
    }
    
    /**
     * Check if request should be blocked
     */
    private function shouldBlockRequest(Request $request): bool
    {
        if (!$this->blockingEnabled) {
            return false;
        }

        $ip = $this->security->resolveClientIp($request);

        // Bypass blocking for authenticated admin/super-admin users to prevent lockouts
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'getRoleNames')) {
                $roleNames = $user->getRoleNames()->map(fn($n) => strtolower($n));
                if ($roleNames->isEmpty() && method_exists($user, 'roles')) {
                    $roleNames = $user->roles->pluck('name')->map(fn($n) => strtolower($n));
                }
                if ($roleNames->contains('admin') || $roleNames->contains('super-admin')) {
                    return false;
                }
            }
        }

        // Bypass blocking for whitelisted IPs
        $whitelist = $this->cacheGet('ip_whitelist', []);
        if (in_array($ip, $whitelist)) {
            return false;
        }

        // Always block explicit blacklist regardless of method
        $blockedIps = $this->cacheGet('ip_blacklist', []);
        if (in_array($ip, $blockedIps)) {
            return true;
        }

        $threatScore = $this->cacheGet("threat_score:{$ip}", 0);
        $blockingThreshold = $this->blockingThreshold;

        // Determine if request should be considered for blocking
        $method = strtoupper($request->method());
        $isSensitive = false;
        foreach ($this->sensitiveRoutes as $pattern) {
            if ($request->is($pattern)) { $isSensitive = true; break; }
        }
        $shouldConsider = in_array($method, $this->blockOnMethods) || $isSensitive;

        // For non-sensitive GET/HEAD/OPTIONS, do not block on score; just log
        if (!$shouldConsider) {
            return false;
        }

        // Block if threat score is too high for considered requests
        if ($threatScore >= $blockingThreshold) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the request is for static assets or service worker
     */
    private function isStaticAsset(Request $request): bool
    {
        // Skip common static paths and service worker
        return $request->is('sw.js')
            || $request->is('favicon.ico')
            || $request->is('robots.txt')
            || $request->is('assets/*')
            || $request->is('css/*')
            || $request->is('js/*')
            || $request->is('images/*')
            || $request->is('fonts/*')
            || $request->is('vendor/*')
            || $request->is('livewire/*');
    }
    
    /**
     * Block the request
     */
    private function blockRequest(Request $request): Response
    {
        $this->logSecurityThreat('request_blocked', $request, [
            'reason' => 'High threat score or blocked IP',
        ]);
        
        return response('Access Denied', 429);
    }
    
    /**
     * Log security threat
     */
    private function logSecurityThreat(string $type, Request $request, array $additional = []): void
    {
        Log::channel('security')->warning("Security threat detected: {$type}", array_merge([
            'type' => $type,
            'ip' => $this->security->resolveClientIp($request),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'timestamp' => now(),
        ], $additional));
    }
    
    /**
     * Increment threat score for IP
     */
    private function incrementThreatScore(Request $request, string $type, int $score): void
    {
        $ip = $this->security->resolveClientIp($request);
        $key = "threat_score:{$ip}";
        $currentScore = $this->cacheGet($key, 0);
        $newScore = $currentScore + $score;
        
        // Store threat score with decay based on configuration
        $this->cachePut($key, $newScore, now()->addMinutes($this->scoreDecayMinutes));
        
        // Track threat type
        $typeKey = "threat_type:{$type}:{$ip}";
        $currentTypeCount = $this->cacheGet($typeKey, 0);
        $this->cachePut($typeKey, $currentTypeCount + 1, now()->addMinutes($this->scoreDecayMinutes));
        
        // If score exceeds blocking threshold, add to blocklist temporarily
        if ($newScore >= $this->blockingThreshold) {
            $blockedIps = $this->cacheGet('ip_blacklist', []);
            $blockedIps[] = $ip;
            $this->cachePut('ip_blacklist', array_unique($blockedIps), 7200); // 2 hours
            
            Log::channel('security')->critical('IP blocked due to high threat score', [
                'ip' => $ip,
                'threat_score' => $newScore,
                'threat_type' => $type,
            ]);
        }
    }

    private function cacheGet(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::get($key, $default);
        } catch (\Throwable $e) {
            try { return Cache::store('file')->get($key, $default); } catch (\Throwable $e2) { return $default; }
        }
    }

    private function cachePut(string $key, mixed $value, mixed $ttl): void
    {
        try {
            Cache::put($key, $value, $ttl);
        } catch (\Throwable $e) {
            try { Cache::store('file')->put($key, $value, $ttl); } catch (\Throwable $e2) { }
        }
    }
}
