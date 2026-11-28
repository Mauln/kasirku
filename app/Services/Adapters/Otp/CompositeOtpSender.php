<?php

declare(strict_types=1);

namespace App\Services\Adapters\Otp;

use App\Contracts\OtpSender;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CompositeOtpSender implements OtpSender
{
    public function __construct(
        private readonly OtpSender $primary,
        private readonly OtpSender $fallback,
    ) {}

    public function send(string $target, string $code, string $channel = 'whatsapp'): bool
    {
        try {
            if ($this->primary->send($target, $code, $channel)) {
                return true;
            }
        } catch (Throwable $exception) {
            $this->logSafe('warning', 'Primary OTP sender threw exception, falling back to email', [
                'exception' => $exception->getMessage(),
                'audit_event' => 'otp.fallback',
            ]);
        }

        $this->logSafe('notice', 'otp.fallback triggered', [
            'primary_channel' => $channel,
            'fallback_channel' => 'email',
        ]);

        try {
            $fallbackOk = $this->fallback->send($target, $code, 'email');
        } catch (Throwable $exception) {
            $this->logSafe('error', 'otp.fallback_failed', [
                'exception' => $exception->getMessage(),
                'severity' => 'P2',
            ]);

            return false;
        }

        if (! $fallbackOk) {
            $this->logSafe('error', 'otp.fallback_failed', [
                'reason' => 'Fallback OTP sender returned false',
                'severity' => 'P2',
            ]);
        }

        return $fallbackOk;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logSafe(string $level, string $message, array $context = []): void
    {
        try {
            if (app()->bound('log')) {
                Log::{$level}($message, $context);
            }
        } catch (Throwable) {
            // Abaikan bila dijalankan di unit test terisolasi tanpa facade root.
        }
    }
}
