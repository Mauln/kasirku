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

it('rejects dev/unsafe drivers, missing credentials, and invalid URLs', function (): void {
    $guard = new ProductionGuardService;

    $unsafe = [
        'otp_driver' => 'fonnte',
        'mail_mailer' => 'log',
        'app_debug' => true,
        'telescope_enabled' => true,
        'session_driver' => 'file',
        'queue_connection' => 'sync',
        'error_driver' => 'sentry',
        'storage_driver' => 's3',
        'app_url' => 'not-a-valid-url',
        'force_https' => false,
        'fonnte_token' => '',
        'fonnte_url' => '',
        'sentry_dsn' => '',
        'aws_access_key_id' => '',
        'aws_bucket' => '',
    ];

    try {
        $guard->assertProductionSafe($unsafe);
        $this->fail('Expected ProductionGuardException was not thrown.');
    } catch (ProductionGuardException $exception) {
        $violations = $exception->violations();
        $failedKeys = array_column($violations, 'env_key');

        expect($failedKeys)->toContain('MAIL_MAILER', 'APP_DEBUG', 'TELESCOPE_ENABLED', 'SESSION_DRIVER', 'QUEUE_CONNECTION', 'PROVIDER_CREDENTIALS', 'APP_URL & FORCE_HTTPS');
    }

    $emptyUrlResults = $guard->evaluate([
        'otp_driver' => 'email',
        'mail_mailer' => 'ses',
        'app_debug' => false,
        'telescope_enabled' => false,
        'session_driver' => 'redis',
        'queue_connection' => 'redis',
        'error_driver' => 'log',
        'storage_driver' => 'local',
        'app_url' => '',
        'force_https' => true,
    ]);
    expect($emptyUrlResults[7]['actual'])->toContain('(empty)');
});

it('reads current config snapshot and runs app:check-production command in dry-run and strict modes', function (): void {
    config()->set('services.otp.driver', 'log');
    config()->set('mail.default', 'log');
    config()->set('app.debug', true);
    config()->set('app.url', 'http://localhost');
    config()->set('kasirku.force_https', false);

    $this->artisan('app:check-production', ['--dry-run' => true])->assertSuccessful();
    $this->artisan('app:check-production')->assertFailed();

    config()->set('services.otp.driver', 'email');
    config()->set('mail.default', 'ses');
    config()->set('app.debug', false);
    config()->set('telescope.enabled', false);
    config()->set('session.driver', 'redis');
    config()->set('queue.default', 'redis');
    config()->set('services.error_tracker.driver', 'log');
    config()->set('services.storage.driver', 'local');
    config()->set('app.url', 'https://kasirku.id');
    config()->set('kasirku.force_https', true);

    $this->artisan('app:check-production')->assertSuccessful();
});
