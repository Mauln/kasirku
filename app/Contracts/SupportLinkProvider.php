<?php

declare(strict_types=1);

namespace App\Contracts;

interface SupportLinkProvider
{
    /**
     * Mengembalikan URL kanal chat/bantuan merchant.
     */
    public function url(): string;
}
