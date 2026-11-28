<?php

declare(strict_types=1);

namespace App\Services\Adapters\Printer;

use App\Contracts\PrinterDriver;

final class FakePrinterDriver implements PrinterDriver
{
    /** @var array<int, array{format: string, data: array<string, mixed>}> */
    private array $jobs = [];

    private int $testPrintCount = 0;

    public function print(array $data, string $format = '58mm'): bool
    {
        $this->jobs[] = [
            'format' => $format,
            'data' => $data,
        ];

        return true;
    }

    public function testPrint(): bool
    {
        $this->testPrintCount++;

        return true;
    }

    /**
     * @return array<int, array{format: string, data: array<string, mixed>}>
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function getTestPrintCount(): int
    {
        return $this->testPrintCount;
    }
}
