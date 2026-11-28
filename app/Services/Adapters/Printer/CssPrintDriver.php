<?php

declare(strict_types=1);

namespace App\Services\Adapters\Printer;

use App\Contracts\PrinterDriver;
use Illuminate\Support\Facades\Log;

final class CssPrintDriver implements PrinterDriver
{
    public function print(array $data, string $format = '80mm'): bool
    {
        Log::info('Prepared CSS @media print fallback payload', [
            'format' => $format,
            'order_number' => $data['order_number'] ?? null,
        ]);

        return true;
    }

    public function testPrint(): bool
    {
        return $this->print(['order_number' => 'CSS-TEST-PRINT'], '80mm');
    }
}
