<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\StatusPageLinkProvider;
use RuntimeException;

final class UptimeKumaLinkProvider implements StatusPageLinkProvider
{
    public function __construct(private readonly string $baseUrl)
    {
        if (trim($baseUrl) === '') {
            throw new RuntimeException('STATUS_URL wajib diisi untuk driver Uptime Kuma.');
        }
    }

    public function url(): string
    {
        return $this->baseUrl;
    }
}
