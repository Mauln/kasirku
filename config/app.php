<?php

return [
    'name' => env('APP_NAME', 'Kasirku'),

    'env' => env('APP_ENV', 'local'),

    'deploy_env' => env('DEPLOY_ENV', 'local'),

    'version' => env('APP_VERSION', '0.1.0'),

    'git_commit' => env('GIT_COMMIT', ''),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost:8000'),

    'guard_rules_enforced' => (bool) env('GUARD_RULES_ENFORCED', false),

    'demo_mode' => (bool) env('DEMO_MODE', false),

    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'locale' => env('APP_LOCALE', 'id'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'id'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
