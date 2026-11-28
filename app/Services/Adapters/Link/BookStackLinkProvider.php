<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\KnowledgeBaseLinkProvider;
use RuntimeException;

final class BookStackLinkProvider implements KnowledgeBaseLinkProvider
{
    public function __construct(private readonly string $baseUrl)
    {
        if (trim($baseUrl) === '') {
            throw new RuntimeException('KB_URL wajib diisi untuk driver BookStack.');
        }
    }

    public function url(): string
    {
        return $this->baseUrl;
    }
}
