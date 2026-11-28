<?php

declare(strict_types=1);

namespace App\Services\Adapters\Printer;

use App\Contracts\PrinterDriver;
use Illuminate\Support\Facades\Log;

final class LogPrinterDriver implements PrinterDriver
{
    public function print(array $data, string $format = '58mm'): bool
    {
        Log::info('Receipt printed via log printer driver', [
            'format' => $format,
            'order_number' => $data['order_number'] ?? null,
        ]);

        return true;
    }

    public function testPrint(): bool
    {
        Log::info('Test print executed via log printer driver');

        return true;
    }
}
