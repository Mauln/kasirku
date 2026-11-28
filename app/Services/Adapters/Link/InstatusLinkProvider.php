<?php

declare(strict_types=1);

namespace App\Services\Adapters\Link;

use App\Contracts\StatusPageLinkProvider;

final class InstatusLinkProvider implements StatusPageLinkProvider
{
    public function __construct(private readonly string $url) {}

    public function url(): string
    {
        return $this->url !== '' ? $this->url : 'https://kasirku.instatus.com';
    }
}
