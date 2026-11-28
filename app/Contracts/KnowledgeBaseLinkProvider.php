<?php

declare(strict_types=1);

namespace App\Contracts;

interface KnowledgeBaseLinkProvider
{
    /**
     * Mengembalikan URL pusat panduan / knowledge base.
     */
    public function url(): string;
}
