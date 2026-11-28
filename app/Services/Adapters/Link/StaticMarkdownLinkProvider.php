<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\KnowledgeBaseLinkProvider;

final class StaticMarkdownLinkProvider implements KnowledgeBaseLinkProvider
{
    public function __construct(private readonly string $url) {}

    public function url(): string
    {
        return $this->url !== '' ? $this->url : '/help';
    }
}
