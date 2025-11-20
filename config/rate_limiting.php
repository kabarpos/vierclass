<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for various
    | endpoints in the application. You can customize the limits
    | based on your application's needs.
    |
    */

    'login' => [
        'max_attempts' => 5,
        'decay_minutes' => 15,
        'lockout_duration' => 900, // 15 minutes in seconds
    ],

    'registration' => [
        'max_attempts' => 3,
        'decay_minutes' => 60,
    ],

    'password_reset' => [
        'max_attempts' => 3,
        'decay_minutes' => 60,
    ],

    'verification_resend' => [
        'max_attempts' => 3,
        'decay_minutes' => 60,
    ],

    'payment' => [
        'max_attempts' => 10,
        'decay_minutes' => 60,
    ],

    'api' => [
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    'webhook' => [
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that should be exempt from rate limiting.
    | Useful for trusted services, webhooks, or admin access.
    |
    */

    'ip_whitelist' => [
        // '127.0.0.1',
        // '::1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Headers
    |--------------------------------------------------------------------------
    |
    | Whether to include rate limiting headers in responses.
    |
    */

    'include_headers' => true,

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log rate limiting events for monitoring and security.
    |
    */

    'log_events' => true,
];