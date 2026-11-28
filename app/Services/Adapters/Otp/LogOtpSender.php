<?php

declare(strict_types=1);

namespace App\Services\Adapters\Otp;

use App\Contracts\OtpSender;
use Illuminate\Support\Facades\Log;

final class LogOtpSender implements OtpSender
{
    public function send(string $target, string $code, string $channel = 'whatsapp'): bool
    {
        Log::info('OTP dispatched via log driver', [
            'channel' => $channel,
            'target_masked' => $this->maskTarget($target),
            'code_length' => strlen($code),
        ]);

        return true;
    }

    private function maskTarget(string $target): string
    {
        $length = strlen($target);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($target, 0, 2).str_repeat('*', $length - 4).substr($target, -2);
    }
}
