<?php

declare(strict_types=1);

namespace App\Contracts;

interface StatusPageLinkProvider
{
    /**
     * Mengembalikan URL status page layanan.
     */
    public function url(): string;
}
