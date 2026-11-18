<?php

return [
    'expired_grace_days' => (int) env('EXPIRED_GRACE_DAYS', 7),
    'subscription_redirect_url' => env('SUBSCRIPTION_REDIRECT_URL', '/app/subscription'),

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

    'promotions' => [
        'voucher_max_per_tenant' => (int) env('VOUCHER_MAX_PER_TENANT', 100),
    ],
];
