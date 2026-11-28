<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\SupportLinkProvider;

final class MailtoLinkProvider implements SupportLinkProvider
{
    public function __construct(private readonly string $url) {}

    public function url(): string
    {
        return $this->url !== '' ? $this->url : 'mailto:support@kasirku.id';
    }
}
