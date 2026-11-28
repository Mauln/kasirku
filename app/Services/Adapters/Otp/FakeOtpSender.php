<?php

declare(strict_types=1);

namespace App\Services\Adapters\Otp;

use App\Contracts\OtpSender;

final class FakeOtpSender implements OtpSender
{
    /** @var array<int, array{target: string, code: string, channel: string}> */
    private array $sent = [];

    private bool $shouldFail = false;

    public function send(string $target, string $code, string $channel = 'whatsapp'): bool
    {
        if ($this->shouldFail) {
            return false;
        }

        $this->sent[] = [
            'target' => $target,
            'code' => $code,
            'channel' => $channel,
        ];

        return true;
    }

    public function forceFailure(bool $fail = true): void
    {
        $this->shouldFail = $fail;
    }

    /**
     * @return array<int, array{target: string, code: string, channel: string}>
     */
    public function getSent(): array
    {
        return $this->sent;
    }

    public function reset(): void
    {
        $this->sent = [];
        $this->shouldFail = false;
    }
}
