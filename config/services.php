<?php

declare(strict_types=1);

return [
    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'),
        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
            'url' => env('FONNTE_URL'),
        ],
    ],

    'email' => [
        'driver' => env('MAIL_MAILER', 'log'),
    ],

    'storage' => [
        'driver' => env('STORAGE_DRIVER', 'local'),
    ],

    'support' => [
        'chat_driver' => env('SUPPORT_CHAT_DRIVER', 'mailto'),
        'kb_driver' => env('KB_DRIVER', 'markdown'),
        'status_driver' => env('STATUS_DRIVER', 'instatus'),
        'chat_url' => env('SUPPORT_CHAT_URL', 'mailto:support@kasirku.id'),
        'kb_url' => env('KB_URL', '/help'),
        'status_url' => env('STATUS_URL', 'https://kasirku.instatus.com'),
    ],

    'error_tracker' => [
        'driver' => env('ERROR_TRACKER_DRIVER', 'log'),
        'dsn' => env('SENTRY_DSN', env('GLITCHTIP_DSN')),
    ],

    'printer' => [
        'driver' => env('PRINTER_DRIVER', 'log'),
    ],

    'analytics' => [
        'enabled' => (bool) env('ANALYTICS_ENABLED', true),
        'queue' => env('ANALYTICS_QUEUE', 'analytics'),
        'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 90),
    ],
];
