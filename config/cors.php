<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for cross-origin requests. Restrictive by default.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [env('APP_URL', 'http://localhost:8000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-CSRF-TOKEN',
        'Authorization',
        'X-Requested-With',
        'Accept',
    ],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'Retry-After',
    ],

    'max_age' => 3600,

    'supports_credentials' => true,
];
