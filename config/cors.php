<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini mengikuti best practice Laravel 12.
    | Origins DILARANG wildcard (*). Gunakan ENV untuk daftar origin yang diizinkan.
    |
    */

    // Jalur yang dikenakan CORS (minimalkan cakupan untuk keamanan)
    'paths' => [
        'api/*',
        'payment/*',
        'booking/payment/*',
        'security/*',
        'sanctum/csrf-cookie',
    ],

    // Metode yang diizinkan
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Origins yang diizinkan: baca dari ENV dan filter nilai kosong
    // Contoh ENV: CORS_ALLOWED_ORIGINS=http://localhost:5173,https://app.brand.com
    'allowed_origins' => (function () {
        $raw = env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', env('VITE_URL', '')));
        $list = array_map('trim', explode(',', (string) $raw));
        return array_values(array_filter($list, fn ($v) => !empty($v)));
    })(),

    // Pola origins (hindari jika tidak perlu)
    'allowed_origins_patterns' => [],

    // Header yang diizinkan
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Origin',
        'Accept',
        'X-CSRF-TOKEN',
        'X-API-KEY',
    ],

    // Header yang diekspos ke klien
    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],

    // Cache preflight
    'max_age' => 86400, // 24 jam

    // Dukung kredensial (cookie) untuk session-based auth
    'supports_credentials' => true,
];

