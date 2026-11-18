<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kasirku Adapter Configurations (8 Adapters + Analytics)
    |--------------------------------------------------------------------------
    | Sesuai Architecture §5: semua layanan eksternal diakses melalui interface
    | di app/Contracts/ dengan driver yang dikonfigurasi lewat .env.
    */

    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'),
        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
            'url' => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        ],
    ],

    'email' => [
        'driver' => env('MAIL_MAILER', 'log'),
    ],

    'storage' => [
        'driver' => env('STORAGE_DRIVER', 'local'),
        'r2' => [
            'account_id' => env('R2_ACCOUNT_ID'),
            'access_key_id' => env('R2_ACCESS_KEY_ID'),
            'secret_access_key' => env('R2_SECRET_ACCESS_KEY'),
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
        ],
    ],

    'support' => [
        'driver' => env('SUPPORT_CHAT_DRIVER', 'mailto'),
        'chat_driver' => env('SUPPORT_CHAT_DRIVER', 'mailto'),
        'kb_driver' => env('KB_DRIVER', 'markdown'),
        'status_driver' => env('STATUS_DRIVER', 'instatus'),
        'chat_url' => env('SUPPORT_CHAT_URL', 'mailto:support@kasirku.id'),
        'kb_url' => env('KB_URL', '/help'),
        'status_url' => env('STATUS_URL', 'https://kasirku.instatus.com'),
        'instatus' => [
            'api_key' => env('INSTATUS_API_KEY'),
            'page_id' => env('INSTATUS_PAGE_ID'),
        ],
    ],

    'error_tracker' => [
        'driver' => env('ERROR_TRACKER_DRIVER', 'log'),
    ],

    'printer' => [
        'driver' => env('PRINTER_DRIVER', 'log'),
    ],

    'analytics' => [
        'driver' => env('ANALYTICS_DRIVER', 'database'),
        'enabled' => (bool) env('ANALYTICS_ENABLED', true),
        'queue' => env('ANALYTICS_QUEUE', 'analytics'),
        'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 90),
    ],
];
