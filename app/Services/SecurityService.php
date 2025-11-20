<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SecurityService
{
    /**
     * Detect SQL injection patterns in input
     */
    public function detectSqlInjection(string $input): bool
    {
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bCREATE\b.*\bTABLE\b)/i',
            '/(\bALTER\b.*\bTABLE\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bSP_\w+)/i',
            '/(\bXP_\w+)/i',
            '/(\'.*\'.*=.*\'.*\')/i',
            '/(--|\#|\/\*|\*\/)/i',
            '/(\bOR\b.*=.*)/i',
            '/(\bAND\b.*=.*)/i',
            '/(1=1|1=0)/i',
            '/(\'\s*OR\s*\')/i',
            '/(\'\s*AND\s*\')/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect XSS patterns in input
     */
    public function detectXss(string $input): bool
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>/i',
            '/<applet[^>]*>.*?<\/applet>/is',
            '/<meta[^>]*>/i',
            '/<img[^>]*onerror[^>]*>/i',
            '/<[^>]*on\w+\s*=\s*["\'][^"\']*["\'][^>]*>/i',
            '/\bon\w+\s*=\s*["\']?[^"\'\s>]*["\']?/i', // Event handlers without HTML tags
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            '/<svg[^>]*onload[^>]*>/i',
            '/<[^>]*style\s*=\s*["\'][^"\']*expression\s*\([^"\']*["\'][^>]*>/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect path traversal patterns in input
     */
    public function detectPathTraversal(string $input): bool
    {
        $patterns = [
            '/\.\.\//',
            '/\.\.\\\\/',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
            '/\.\.%2f/i',
            '/\.\.%5c/i',
            '/%252e%252e%252f/i',
            '/\.\.%252f/i',
            '/\.\.%c0%af/i',
            '/\.\.%c1%9c/i',
            '/\.\.%c0%9v/i',
            '/\.\.%c0%qf/i',
            '/\.\.%c1%8s/i',
            '/\.\.%c1%9c/i',
            '/\.\.%c1%pc/i',
            '/\.\.%c1%1c/i',
            '/\.\.%c0%2f/i',
            '/\.\.%c0%af/i',
            '/\.\.%c1%9c/i',
            '/\.\.%c1%af/i',
            '/\.\.%252f/i',
            '/\.\.%255c/i',
            '/\.\.\/\.\.\//i',
            '/\.\.\\\\\.\.\\\\/',
            '/\.\.%u2215/i',
            '/\.\.%u2216/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect command injection patterns in input
     */
    public function detectCommandInjection(string $input): bool
    {
        $patterns = [
            '/[;&|`$(){}[\]<>]/i',
            '/\b(cat|ls|pwd|id|whoami|uname|ps|netstat|ifconfig|ping|nslookup|dig|wget|curl|nc|telnet|ssh|ftp|tftp|scp|rsync|tar|gzip|gunzip|zip|unzip|rar|unrar|7z|chmod|chown|chgrp|su|sudo|passwd|useradd|userdel|groupadd|groupdel|mount|umount|fdisk|df|du|free|top|htop|kill|killall|pkill|nohup|screen|tmux|crontab|at|batch|mail|sendmail|mailx|mutt|pine|elm|vi|vim|nano|emacs|pico|joe|ed|sed|awk|grep|find|locate|which|whereis|file|strings|hexdump|od|xxd|base64|openssl|gpg|md5sum|sha1sum|sha256sum|sha512sum)\b/i',
            '/\$\([^)]*\)/i',
            '/`[^`]*`/i',
            '/\$\{[^}]*\}/i',
            '/\|\s*\w+/i',
            '/&&\s*\w+/i',
            '/;\s*\w+/i',
            '/\|\|\s*\w+/i',
            '/>\s*\/\w+/i',
            '/<\s*\/\w+/i',
            '/\s*2>&1/i',
            '/\s*>&/i',
        ];

        // Skip detection for normal file names
        if (preg_match('/^[\w\-\.]+\.(txt|pdf|doc|docx|xlsx|jpg|jpeg|png|gif|svg|mp4|mp3|zip|rar|7z)$/i', $input)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user agent is suspicious
     */
    public function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/w3af/i',
            '/zap/i',
            '/burp/i',
            '/python-requests/i',
            '/curl/i',
            '/wget/i',
            '/scanner/i',
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/harvester/i',
            '/extractor/i',
            '/libwww/i',
            '/lwp/i',
            '/mechanize/i',
            '/httpclient/i',
            '/okhttp/i',
            '/apache-httpclient/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Increase threat score for an IP
     */
    public function increaseThreatScore(string $ip, int $score): void
    {
        $currentScore = $this->getThreatScore($ip);
        $newScore = $currentScore + $score;
        
        Cache::put("threat_score:{$ip}", $newScore, now()->addHours(24));
        
        if ($newScore >= 100) {
            $this->addToBlacklist($ip);
        }
    }

    /**
     * Get threat score for an IP
     */
    public function getThreatScore(string $ip): int
    {
        return Cache::get("threat_score:{$ip}", 0);
    }

    /**
     * Check if IP is blocked
     */
    public function isBlocked(string $ip): bool
    {
        if ($this->isWhitelisted($ip)) {
            return false;
        }

        return $this->isBlacklisted($ip) || $this->getThreatScore($ip) >= 100;
    }

    /**
     * Add IP to whitelist
     */
    public function addToWhitelist(string $ip): void
    {
        $whitelist = Cache::get('ip_whitelist', []);
        $whitelist[] = $ip;
        $ttlMinutes = config('security.scanner.whitelist_ttl_minutes', 1440);
        Cache::put('ip_whitelist', array_unique($whitelist), now()->addMinutes($ttlMinutes));
    }

    /**
     * Check if IP is whitelisted
     */
    public function isWhitelisted(string $ip): bool
    {
        $whitelist = Cache::get('ip_whitelist', []);
        return in_array($ip, $whitelist);
    }

    /**
     * Add IP to blacklist
     */
    public function addToBlacklist(string $ip): void
    {
        $blacklist = Cache::get('ip_blacklist', []);
        $blacklist[] = $ip;
        Cache::put('ip_blacklist', array_unique($blacklist), now()->addDays(7));
    }

    /**
     * Check if IP is blacklisted
     */
    public function isBlacklisted(string $ip): bool
    {
        $blacklist = Cache::get('ip_blacklist', []);
        return in_array($ip, $blacklist);
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedLogin(string $ip): void
    {
        $key = "failed_logins:{$ip}";
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, now()->addMinutes(15));
    }

    /**
     * Check if IP has too many failed login attempts (brute force)
     */
    public function isBruteForceAttempt(string $ip): bool
    {
        $key = "failed_logins:{$ip}";
        $attempts = Cache::get($key, 0);
        return $attempts >= 5;
    }

    /**
     * Reset failed login attempts for IP
     */
    public function resetFailedLogins(string $ip): void
    {
        $key = "failed_logins:{$ip}";
        Cache::forget($key);
    }

    /**
     * Validate file upload security
     */
    public function validateFileUpload(string $filename, string $mimeType, int $size, array $allowedTypes, int $maxSize): bool
    {
        // Check file size
        if ($size > $maxSize) {
            return false;
        }

        // Check MIME type
        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        // Check for dangerous file extensions
        $dangerousExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'jspx',
            'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar', 'pl', 'py',
            'rb', 'sh', 'cgi', 'htaccess', 'htpasswd'
        ];

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($extension, $dangerousExtensions)) {
            return false;
        }

        // Check for double extensions
        if (preg_match('/\.(php|asp|jsp|exe|bat|cmd|scr|vbs|js)\./i', $filename)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize input to prevent XSS
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        return hash('sha256', Str::random(40) . time());
    }

    /**
     * Store CSRF token
     */
    public function storeCsrfToken(string $token): void
    {
        $tokens = Cache::get('csrf_tokens', []);
        $tokens[] = $token;
        
        // Keep only last 10 tokens
        if (count($tokens) > 10) {
            $tokens = array_slice($tokens, -10);
        }
        
        Cache::put('csrf_tokens', $tokens, now()->addHours(2));
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(string $token): bool
    {
        $tokens = Cache::get('csrf_tokens', []);
        return in_array($token, $tokens);
    }

    /**
     * Encrypt sensitive data
     */
    public function encryptData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptData(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }

    /**
     * Hash password securely
     */
    public function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }

    /**
     * Generate secure random string
     */
    public function generateSecureRandomString(int $length): string
    {
        return Str::random($length);
    }

    /**
     * Validate IP address
     */
    public function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if rate limited
     */
    public function isRateLimited(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $attempts = Cache::get("rate_limit:{$key}", 0);
        return $attempts >= $maxAttempts;
    }

    /**
     * Increment rate limit counter
     */
    public function incrementRateLimit(string $key, int $decayMinutes): void
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $attempts + 1, now()->addMinutes($decayMinutes));
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, string $ip, string $userAgent, array $details = []): void
    {
        Log::channel('security')->warning("Security Event: {$event}", [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'details' => $details
        ]);
    }

    /**
     * Clean expired threat scores
     */
    public function cleanExpiredThreatScores(): void
    {
        // This would typically be handled by cache expiration
        // But we can implement manual cleanup if needed
        $keys = Cache::get('threat_score_keys', []);
        
        foreach ($keys as $key) {
            if (!Cache::has($key)) {
                // Remove from tracking
                $keys = array_filter($keys, fn($k) => $k !== $key);
            }
        }
        
        Cache::put('threat_score_keys', $keys, now()->addDays(1));
    }

    /**
     * Get security statistics
     */
    public function getSecurityStatistics(): array
    {
        $blacklist = Cache::get('ip_blacklist', []);
        $whitelist = Cache::get('ip_whitelist', []);
        
        // Count high threat IPs
        $highThreatCount = 0;
        $totalThreats = 0;
        
        $keys = Cache::get('threat_score_keys', []);
        foreach ($keys as $key) {
            $score = Cache::get($key, 0);
            if ($score > 0) {
                $totalThreats++;
                if ($score >= 80) {
                    $highThreatCount++;
                }
            }
        }

        return [
            'total_threats' => $totalThreats,
            'blocked_ips' => count($blacklist),
            'whitelisted_ips' => count($whitelist),
            'high_threat_ips' => $highThreatCount,
        ];
    }

    public function resolveClientIp(Request $request): string
    {
        $cfIp = $request->header('CF-Connecting-IP');
        if ($cfIp && $this->isValidIpAddress($cfIp)) {
            return $cfIp;
        }

        $xff = $request->header('X-Forwarded-For');
        if ($xff) {
            foreach (explode(',', $xff) as $candidate) {
                $ip = trim($candidate);
                if ($this->isValidIpAddress($ip)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

}

