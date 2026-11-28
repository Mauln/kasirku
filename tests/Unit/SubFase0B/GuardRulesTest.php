<?php

declare(strict_types=1);

use App\Exceptions\ProductionGuardException;
use App\Services\Guard\ProductionGuardService;

it('passes all 8 production guard rules when configuration is production-safe', function (): void {
    $guard = new ProductionGuardService;

    $snapshot = [
        'otp_driver' => 'fonnte',
        'mail_mailer' => 'ses',
        'app_debug' => false,
        'telescope_enabled' => false,
        'session_driver' => 'redis',
        'queue_connection' => 'redis',
        'error_driver' => 'sentry',
        'storage_driver' => 's3',
        'app_url' => 'https://kasirku.id',
        'force_https' => true,
        'fonnte_token' => 'secret-token-value',
        'fonnte_url' => 'https://api.fonnte.com/send',
        'sentry_dsn' => 'https://example@sentry.io/1',
        'aws_access_key_id' => 'AKIAEXAMPLE',
        'aws_bucket' => 'kasirku-exports',
    ];

    $results = $guard->evaluate($snapshot);

    expect($results)->toHaveCount(8);
    expect(collect($results)->every(fn (array $item): bool => $item['passed']))->toBeTrue();
    $guard->assertProductionSafe($snapshot);
});

it('rejects dev/unsafe drivers and flags all failing rules', function (): void {
    $guard = new ProductionGuardService;

    $unsafe = [
        'otp_driver' => 'log',
        'mail_mailer' => 'log',
        'app_debug' => true,
        'telescope_enabled' => true,
        'session_driver' => 'file',
        'queue_connection' => 'sync',
        'error_driver' => 'sentry',
        'storage_driver' => 'local',
        'app_url' => 'http://localhost:8000',
        'force_https' => false,
        'sentry_dsn' => '',
    ];

    expect(fn () => $guard->assertProductionSafe($unsafe))
        ->toThrow(ProductionGuardException::class);
});
