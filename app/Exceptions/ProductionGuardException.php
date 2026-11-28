<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ProductionGuardException extends RuntimeException
{
    /**
     * @param  array<int, array{rule: int, env_key: string, actual: string, expected: string, message: string}>  $violations
     */
    public function __construct(private readonly array $violations)
    {
        $keys = array_map(static fn (array $item): string => $item['env_key'], $violations);

        parent::__construct(
            'Production guard rules failed for environment configuration: '.implode(', ', $keys)
        );
    }

    /**
     * @return array<int, array{rule: int, env_key: string, actual: string, expected: string, message: string}>
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
