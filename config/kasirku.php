<?php

declare(strict_types=1);

return [
    'version' => env('APP_VERSION', '2.0.0-0b'),
    'git_commit' => env('GIT_COMMIT', 'local'),
    'deploy_env' => env('DEPLOY_ENV', 'local'),
    'demo_mode' => (bool) env('APP_DEMO_MODE', false),
    'guard_rules_enforced' => (bool) env('GUARD_RULES_ENFORCED', false),
    'force_https' => (bool) env('FORCE_HTTPS', false),

    'pos' => [
        'pin_lockout_attempts' => (int) env('POS_PIN_LOCKOUT_ATTEMPTS', 5),
        'pin_lockout_minutes' => (int) env('POS_PIN_LOCKOUT_MINUTES', 15),
        'device_session_days' => (int) env('POS_DEVICE_SESSION_DAYS', 30),
        'hold_limit' => (int) env('POS_HOLD_LIMIT', 10),
        'offline_cart_key_prefix' => env('POS_OFFLINE_CART_KEY_PREFIX', 'kasirku_cart_'),
    ],

    'credit_sale' => [
        'default_limit' => (int) env('CREDIT_SALE_DEFAULT_LIMIT', 500000),
        'default_due_days' => (int) env('CREDIT_SALE_DEFAULT_DUE_DAYS', 30),
    ],

    'voucher' => [
        'max_per_tenant' => (int) env('VOUCHER_MAX_PER_TENANT', 100),
    ],
];
