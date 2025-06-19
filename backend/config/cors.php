<?php
// config/cors.php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        env('FRONTEND_URL'),
        env('FRONTEND_PROD_URL'),

        env('APP_ENV') === 'local' ? 'http://localhost' : null,
        env('APP_ENV') === 'local' ? 'http://localhost:5173' : null,
        env('APP_ENV') === 'production' ? 'https://animalsciencedays.org' : null,
    ]),

    'allowed_origins_patterns' => [
        env('APP_ENV') === 'local' ? '/^http:\/\/.*\.localhost(:\d+)?$/' : null,
        env('APP_ENV') === 'production' ? '/^https:\/\/.*\.animalsciencedays\.org$/' : null,
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];