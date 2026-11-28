<?php

declare(strict_types=1);

namespace App\Services\Adapters\Error;

use App\Contracts\ErrorReporter;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LogErrorReporter implements ErrorReporter
{
    public function report(Throwable $exception, array $context = []): void
    {
        Log::error($exception->getMessage(), [
            'exception_class' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
        ]);
    }
}
