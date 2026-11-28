<?php

declare(strict_types=1);

namespace App\Services\Adapters\Otp;

use App\Contracts\OtpSender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class FonnteOtpSender implements OtpSender
{
    private readonly string $token;

    private readonly string $endpoint;

    /**
     * @param  array{token?: string|null, url?: string|null}  $config
     */
    public function __construct(array $config)
    {
        $token = trim((string) ($config['token'] ?? ''));
        $endpoint = trim((string) ($config['url'] ?? ''));

        if ($token === '' || $endpoint === '') {
            throw new RuntimeException('Konfigurasi Fonnte OTP belum lengkap (FONNTE_TOKEN / FONNTE_URL).');
        }

        $parsed = parse_url($endpoint);
        if (! is_array($parsed) || ($parsed['scheme'] ?? '') !== 'https') {
            throw new RuntimeException('FONNTE_URL wajib menggunakan skema HTTPS yang valid.');
        }

        $this->token = $token;
        $this->endpoint = $endpoint;
    }

    public function send(string $target, string $code, string $channel = 'whatsapp'): bool
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => $this->token,
            ])
            ->post($this->endpoint, [
                'target' => $target,
                'message' => sprintf('Kode OTP Kasirku Anda: %s. Berlaku 5 menit, jangan bagikan ke siapa pun.', $code),
            ]);

        if (! $response->successful()) {
            Log::warning('Fonnte OTP delivery returned non-2xx status', [
                'status' => $response->status(),
                'channel' => $channel,
            ]);

            return false;
        }

        return true;
    }
}
