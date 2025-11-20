<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application, including HPKP, CSP reporting, and other security features.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | HTTP Public Key Pinning (HPKP)
    |--------------------------------------------------------------------------
    |
    | HPKP allows you to pin specific public keys to prevent man-in-the-middle
    | attacks. Only enable this in production with valid SSL certificates.
    |
    */
    'enable_hpkp' => env('SECURITY_ENABLE_HPKP', false),
    
    'hpkp_pins' => [
        // Add your certificate pins here
        // Example: 'YLh1dUR9y6Kja30RrAn7JKnbQG/uEtLMkBgFF2Fuihg='
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CSP directives and reporting endpoints.
    |
    */
    'csp' => [
        'report_only' => env('CSP_REPORT_ONLY', true),
        'report_uri' => env('CSP_REPORT_URI', '/security/csp-report'),
        
        // Additional trusted domains
        'trusted_domains' => [
            'script' => [
                'https://cdn.tailwindcss.com',
                'https://app.sandbox.midtrans.com',
                'https://app.midtrans.com',
            ],
            'style' => [
                'https://fonts.googleapis.com',
                'https://cdn.tailwindcss.com',
            ],
            'font' => [
                'https://fonts.gstatic.com',
            ],
            'connect' => [
                'https://api.sandbox.midtrans.com',
                'https://api.midtrans.com',
            ],
            'frame' => [
                'https://www.youtube.com',
                'https://www.youtube-nocookie.com',
                'https://app.sandbox.midtrans.com',
                'https://app.midtrans.com',
            ],
            'media' => [
                'https://www.youtube.com',
                'https://www.youtube-nocookie.com',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure various security headers behavior.
    |
    */
    'headers' => [
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => env('HSTS_PRELOAD', true),
        
        'expect_ct_max_age' => env('EXPECT_CT_MAX_AGE', 86400), // 24 hours
        'expect_ct_enforce' => env('EXPECT_CT_ENFORCE', true),
        
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure security monitoring and alerting.
    |
    */
    'monitoring' => [
        'log_suspicious_headers' => env('SECURITY_LOG_SUSPICIOUS_HEADERS', true),
        'log_xss_attempts' => env('SECURITY_LOG_XSS_ATTEMPTS', true),
        'log_sql_injection_attempts' => env('SECURITY_LOG_SQL_INJECTION', true),
        
        'alert_thresholds' => [
            'suspicious_requests_per_minute' => env('SECURITY_ALERT_THRESHOLD', 10),
            'failed_login_attempts' => env('SECURITY_FAILED_LOGIN_THRESHOLD', 5),
        ],
        
        'notification_channels' => [
            'slack' => env('SECURITY_SLACK_WEBHOOK'),
            'email' => env('SECURITY_ALERT_EMAIL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Security
    |--------------------------------------------------------------------------
    |
    | Security-related rate limiting configuration.
    |
    */
    'rate_limiting' => [
        'security_scan_attempts' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        'suspicious_activity' => [
            'max_attempts' => 5,
            'decay_minutes' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Security configuration for file uploads.
    |
    */
    'file_upload' => [
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
        ],
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB
        'scan_uploads' => env('SCAN_UPLOADS', false),
        'quarantine_suspicious' => env('QUARANTINE_SUSPICIOUS_FILES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Security configuration for API endpoints.
    |
    */
    'api' => [
        'require_https' => env('API_REQUIRE_HTTPS', true),
        'cors_allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
        'api_key_header' => env('API_KEY_HEADER', 'X-API-Key'),
        'rate_limit_by_user' => env('API_RATE_LIMIT_BY_USER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | Security configuration for database operations.
    |
    */
    'database' => [
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'detect_sql_injection' => env('DETECT_SQL_INJECTION', true),
        'log_failed_connections' => env('LOG_FAILED_DB_CONNECTIONS', true),
    ],

    // Security Scanner configuration
    'scanner' => [
        'blocking_enabled' => env('SECURITY_SCANNER_BLOCKING_ENABLED', true),
        'blocking_threshold' => env('SECURITY_BLOCKING_THRESHOLD', 120),
        'score_decay_minutes' => env('SECURITY_SCORE_DECAY_MINUTES', 30),
        // Add public routes to exempt list and merge with ENV overrides to reduce false positives
        'exempt_routes' => array_filter(array_unique(array_merge(
            ['/login','/register','/password/*','/auth/*','/courses','/course/*'],
            explode(',', env('SECURITY_EXEMPT_ROUTES', ''))
        ))),
        // Enable auto-whitelist on login and set TTL in minutes
        'login_auto_whitelist_enabled' => env('SECURITY_LOGIN_WHITELIST_ENABLED', true),
        'whitelist_ttl_minutes' => env('SECURITY_WHITELIST_TTL_MINUTES', 1440),
        // Only block on state-changing methods; allow GET by default
        'block_on_methods' => explode(',', env('SECURITY_BLOCK_ON_METHODS', 'POST,PUT,PATCH,DELETE')),
        // Sensitive routes (always considered for blocking even on GET)
        'sensitive_route_patterns' => array_filter(array_unique(array_merge(
            ['/dashboard*','/profile*','/checkout*','/payment*','/admin/*'],
            explode(',', env('SECURITY_SENSITIVE_ROUTE_PATTERNS', ''))
        ))),
        'ua_allowed_patterns' => [
            '/Android.*(Chrome|WebView)/i',
            '/iPhone.*Safari/i',
            '/Mozilla.*Mobile/i',
        ],
    ],
];