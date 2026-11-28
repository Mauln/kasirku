<?php

declare(strict_types=1);

namespace App\Services\Guard;

use App\Exceptions\ProductionGuardException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ProductionGuardService
{
    /**
     * Mengevaluasi 8 Guard Rules Production (Architecture §8.1).
     *
     * @param  array<string, mixed>|null  $overrideSnapshot
     * @return array<int, array{rule: int, env_key: string, passed: bool, actual: string, expected: string, message: string}>
     */
    public function evaluate(?array $overrideSnapshot = null): array
    {
        $cfg = $overrideSnapshot ?? $this->readCurrentSnapshot();

        $otpDriver = strtolower(trim((string) ($cfg['otp_driver'] ?? 'log')));
        $mailMailer = strtolower(trim((string) ($cfg['mail_mailer'] ?? 'log')));
        $appDebug = (bool) ($cfg['app_debug'] ?? true);
        $telescopeEnabled = (bool) ($cfg['telescope_enabled'] ?? false);
        $sessionDriver = strtolower(trim((string) ($cfg['session_driver'] ?? 'file')));
        $queueConnection = strtolower(trim((string) ($cfg['queue_connection'] ?? 'sync')));
        $errorDriver = strtolower(trim((string) ($cfg['error_driver'] ?? 'log')));
        $storageDriver = strtolower(trim((string) ($cfg['storage_driver'] ?? 'local')));
        $appUrl = trim((string) ($cfg['app_url'] ?? ''));
        $forceHttps = (bool) ($cfg['force_https'] ?? false);

        $credentialsMissing = $this->findMissingCredentials($cfg, $otpDriver, $errorDriver, $storageDriver);

        return [
            [
                'rule' => 1,
                'env_key' => 'OTP_DRIVER',
                'passed' => ! in_array($otpDriver, ['', 'log', 'fake'], true),
                'actual' => $otpDriver === '' ? '(empty)' : $otpDriver,
                'expected' => 'fonnte | email | composite (bukan log/fake)',
                'message' => 'OTP_DRIVER tidak boleh menggunakan log/fake di production.',
            ],
            [
                'rule' => 2,
                'env_key' => 'MAIL_MAILER',
                'passed' => ! in_array($mailMailer, ['', 'log', 'array'], true),
                'actual' => $mailMailer === '' ? '(empty)' : $mailMailer,
                'expected' => 'ses | postmark | smtp (bukan log/array)',
                'message' => 'MAIL_MAILER tidak boleh menggunakan log/array di production.',
            ],
            [
                'rule' => 3,
                'env_key' => 'APP_DEBUG',
                'passed' => $appDebug === false,
                'actual' => $appDebug ? 'true' : 'false',
                'expected' => 'false',
                'message' => 'APP_DEBUG wajib false di production.',
            ],
            [
                'rule' => 4,
                'env_key' => 'TELESCOPE_ENABLED',
                'passed' => $telescopeEnabled === false,
                'actual' => $telescopeEnabled ? 'true' : 'false',
                'expected' => 'false',
                'message' => 'TELESCOPE_ENABLED wajib false di production.',
            ],
            [
                'rule' => 5,
                'env_key' => 'SESSION_DRIVER',
                'passed' => $sessionDriver === 'redis',
                'actual' => $sessionDriver === '' ? '(empty)' : $sessionDriver,
                'expected' => 'redis',
                'message' => 'SESSION_DRIVER wajib redis sesuai Aturan Emas #11.',
            ],
            [
                'rule' => 6,
                'env_key' => 'QUEUE_CONNECTION',
                'passed' => ! in_array($queueConnection, ['', 'sync'], true),
                'actual' => $queueConnection === '' ? '(empty)' : $queueConnection,
                'expected' => 'redis (bukan sync)',
                'message' => 'QUEUE_CONNECTION tidak boleh sync di production.',
            ],
            [
                'rule' => 7,
                'env_key' => 'PROVIDER_CREDENTIALS',
                'passed' => $credentialsMissing === [],
                'actual' => $credentialsMissing === [] ? 'configured' : 'missing:' . implode(',', $credentialsMissing),
                'expected' => 'Semua kredensial provider aktif terisi (non-empty)',
                'message' => 'Kredensial provider non-fake wajib terisi lengkap.',
            ],
            [
                'rule' => 8,
                'env_key' => 'APP_URL & FORCE_HTTPS',
                'passed' => str_starts_with(strtolower($appUrl), 'https://') && $forceHttps === true,
                'actual' => sprintf('%s | FORCE_HTTPS=%s', $this->maskUrlPath($appUrl), $forceHttps ? 'true' : 'false'),
                'expected' => 'https://* dan FORCE_HTTPS=true',
                'message' => 'APP_URL wajib HTTPS dan middleware ForceHttps aktif.',
            ],
        ];
    }

    /**
     * Memastikan seluruh 8 guard lulus saat production enforcement aktif.
     *
     * @param  array<string, mixed>|null  $overrideSnapshot
     */
    public function assertProductionSafe(?array $overrideSnapshot = null): void
    {
        $results = $this->evaluate($overrideSnapshot);
        $failures = array_values(array_filter($results, static fn (array $item): bool => ! $item['passed']));

        if ($failures === []) {
            return;
        }

        $this->recordAuditFailure($failures);

        throw new ProductionGuardException($failures);
    }

    /**
     * @return array<string, mixed>
     */
    private function readCurrentSnapshot(): array
    {
        return [
            'otp_driver' => config('services.otp.driver', 'log'),
            'mail_mailer' => config('mail.default', 'log'),
            'app_debug' => (bool) config('app.debug', true),
            'telescope_enabled' => (bool) config('telescope.enabled', false),
            'session_driver' => config('session.driver', 'redis'),
            'queue_connection' => config('queue.default', 'redis'),
            'error_driver' => config('services.error_tracker.driver', 'log'),
            'storage_driver' => config('services.storage.driver', 'local'),
            'app_url' => (string) config('app.url', ''),
            'force_https' => (bool) config('kasirku.force_https', false),
            'fonnte_token' => (string) config('services.otp.fonnte.token', ''),
            'fonnte_url' => (string) config('services.otp.fonnte.url', ''),
            'sentry_dsn' => (string) config('services.error_tracker.dsn', ''),
            'aws_access_key_id' => (string) config('filesystems.disks.s3.key', ''),
            'aws_bucket' => (string) config('filesystems.disks.s3.bucket', ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    private function findMissingCredentials(array $cfg, string $otpDriver, string $errorDriver, string $storageDriver): array
    {
        $missing = [];

        if (in_array($otpDriver, ['fonnte', 'composite'], true)) {
            if (trim((string) ($cfg['fonnte_token'] ?? '')) === '') {
                $missing[] = 'FONNTE_TOKEN';
            }
            if (trim((string) ($cfg['fonnte_url'] ?? '')) === '') {
                $missing[] = 'FONNTE_URL';
            }
        }

        if (in_array($errorDriver, ['sentry', 'glitchtip'], true) && trim((string) ($cfg['sentry_dsn'] ?? '')) === '') {
            $missing[] = 'SENTRY_DSN';
        }

        if (in_array($storageDriver, ['s3', 'minio', 'r2'], true)) {
            if (trim((string) ($cfg['aws_access_key_id'] ?? '')) === '') {
                $missing[] = 'AWS_ACCESS_KEY_ID';
            }
            if (trim((string) ($cfg['aws_bucket'] ?? '')) === '') {
                $missing[] = 'AWS_BUCKET';
            }
        }

        return $missing;
    }

    /**
     * @param  array<int, array{rule: int, env_key: string, actual: string, expected: string, message: string}>  $failures
     */
    private function recordAuditFailure(array $failures): void
    {
        $summaryKeys = array_map(static fn (array $item): string => $item['env_key'], $failures);

        try {
            $app = app();
            if ($app->bound('log')) {
                $environment = method_exists($app, 'environment') ? $app->environment() : 'unknown';

                Log::critical('env_guard_failed', [
                    'action' => 'env_guard_failed',
                    'failed_keys' => $summaryKeys,
                    'environment' => $environment,
                ]);
            }

            if ($app->bound('db') && Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'actor_type' => 'system',
                    'action' => 'env_guard_failed',
                    'entity_type' => 'environment',
                    'metadata' => json_encode(['failed_keys' => $summaryKeys], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }
        } catch (Throwable) {
            // Fallback aman bila dipanggil sebelum container/Log/audit_logs siap.
        }
    }

    private function maskUrlPath(string $url): string
    {
        if ($url === '') {
            return '(empty)';
        }

        $parsed = parse_url($url);
        if (! is_array($parsed) || ! isset($parsed['scheme'], $parsed['host'])) {
            return '(invalid-url)';
        }

        return $parsed['scheme'] . '://' . $parsed['host'];
    }
}
