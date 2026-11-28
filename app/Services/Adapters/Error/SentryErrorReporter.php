<?php

declare(strict_types=1);

namespace App\Services\Adapters\Error;

use App\Contracts\ErrorReporter;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class SentryErrorReporter implements ErrorReporter
{
    private readonly string $dsn;

    /**
     * @param  array{dsn?: string|null}  $config
     */
    public function __construct(array $config)
    {
        $dsn = trim((string) ($config['dsn'] ?? ''));
        if ($dsn === '') {
            throw new RuntimeException('SENTRY_DSN atau GLITCHTIP_DSN wajib diisi saat menggunakan driver sentry.');
        }

        $this->dsn = $dsn;
    }

    public function report(Throwable $exception, array $context = []): void
    {
        if (function_exists('\Sentry\captureException')) {
            \Sentry\captureException($exception);

            return;
        }

        Log::error('Error captured for Sentry/GlitchTip forwarding', [
            'dsn_configured' => $this->dsn !== '',
            'exception_class' => $exception::class,
            'message' => $exception->getMessage(),
            'context' => $context,
        ]);
    }
}
