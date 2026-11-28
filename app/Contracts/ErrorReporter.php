<?php

declare(strict_types=1);

namespace App\Contracts;

use Throwable;

interface ErrorReporter
{
    /**
     * Melaporkan exception ke layanan observability atau log internal.
     *
     * @param  array<string, mixed>  $context
     */
    public function report(Throwable $exception, array $context = []): void;
}
