<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Izinkan akses dari frontend Next.js (mendukung APP_URL_FRONTEND dari .env + domain produksi BBPOM)
    'allowed_origins' => array_values(array_unique(array_filter([
        env('APP_URL_FRONTEND', 'http://localhost:3000'),
        'https://bbpommataram.id',
        'https://www.bbpommataram.id',
        'http://localhost:3000',
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // WAJIB TRUE agar cookie lintas port diperbolehkan masuk
    'supports_credentials' => true,
];