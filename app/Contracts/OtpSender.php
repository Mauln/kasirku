<?php

declare(strict_types=1);

namespace App\Contracts;

interface OtpSender
{
    /**
     * Mengirim kode OTP ke target pengguna melalui kanal tertentu.
     */
    public function send(string $target, string $code, string $channel = 'whatsapp'): bool;
}
