<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Izinkan akses dari frontend Next.js Anda
    'allowed_origins' => [env('APP_URL_FRONTEND', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // WAJIB TRUE agar cookie lintas port diperbolehkan masuk
    'supports_credentials' => true,
];
