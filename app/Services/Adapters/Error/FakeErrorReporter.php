<?php

declare(strict_types=1);

namespace App\Services\Adapters\Error;

use App\Contracts\ErrorReporter;
use Throwable;

final class FakeErrorReporter implements ErrorReporter
{
    /** @var array<int, array{exception: Throwable, context: array<string, mixed>}> */
    private array $reported = [];

    public function report(Throwable $exception, array $context = []): void
    {
        $this->reported[] = [
            'exception' => $exception,
            'context' => $context,
        ];
    }

    /**
     * @return array<int, array{exception: Throwable, context: array<string, mixed>}>
     */
    public function getReported(): array
    {
        return $this->reported;
    }

    public function reset(): void
    {
        $this->reported = [];
    }
}
