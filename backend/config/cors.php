<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',       // React local dev
        'http://127.0.0.1:3000',       // Alternate React local dev
        'http://localhost:5173',       // Vite default port
        'http://127.0.0.1:5173',       // Alternate Vite URL
        'https://localhost:5173',      // Vite HTTPS mode
        'https://127.0.0.1:5173',      // Vite HTTPS alternate
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
