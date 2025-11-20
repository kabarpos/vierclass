<?php

return [
    'serverKey'    => env('MIDTRANS_SERVER_KEY'),
    'clientKey'    => env('MIDTRANS_CLIENT_KEY'),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'isSanitized'  => env('MIDTRANS_SANITIZE', true),
    'is3ds'        => env('MIDTRANS_3DS', true),
    
    // Flexible environment switching
    'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
    
    // Sandbox configuration
    'sandbox' => [
        'serverKey' => env('MIDTRANS_SANDBOX_SERVER_KEY', env('MIDTRANS_SERVER_KEY')),
        'clientKey' => env('MIDTRANS_SANDBOX_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY')),
        'baseUrl'   => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
        'snapUrl'   => 'https://app.sandbox.midtrans.com/snap/snap.js',
    ],
    
    // Production configuration
    'production' => [
        'serverKey' => env('MIDTRANS_PRODUCTION_SERVER_KEY', env('MIDTRANS_SERVER_KEY')),
        'clientKey' => env('MIDTRANS_PRODUCTION_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY')),
        'baseUrl'   => 'https://app.midtrans.com/snap/v1/transactions',
        'snapUrl'   => 'https://app.midtrans.com/snap/snap.js',
    ],
];
