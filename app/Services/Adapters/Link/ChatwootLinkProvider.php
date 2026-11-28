<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\SupportLinkProvider;
use RuntimeException;

final class ChatwootLinkProvider implements SupportLinkProvider
{
    public function __construct(private readonly string $baseUrl)
    {
        if (trim($baseUrl) === '') {
            throw new RuntimeException('SUPPORT_CHAT_URL wajib diisi untuk driver Chatwoot.');
        }
    }

    public function url(): string
    {
        return $this->baseUrl;
    }
}
