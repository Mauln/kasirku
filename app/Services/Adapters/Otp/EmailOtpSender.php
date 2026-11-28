<?php

declare(strict_types=1);

namespace App\Services\Adapters\Otp;

use App\Contracts\EmailSender;
use App\Contracts\OtpSender;

final class EmailOtpSender implements OtpSender
{
    public function __construct(private readonly EmailSender $emailSender) {}

    public function send(string $target, string $code, string $channel = 'email'): bool
    {
        return $this->emailSender->send(
            to: $target,
            subject: 'Kode Verifikasi OTP Kasirku',
            body: sprintf('Kode OTP Anda adalah %s. Berlaku selama 5 menit.', $code),
            options: ['channel' => $channel, 'type' => 'otp']
        );
    }
}
