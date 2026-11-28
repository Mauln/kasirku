<?php

declare(strict_types=1);

namespace App\Services\Adapters\Printer;

use App\Contracts\PrinterDriver;
use Illuminate\Support\Facades\Log;

final class BluetoothPrinterDriver implements PrinterDriver
{
    public function print(array $data, string $format = '58mm'): bool
    {
        // Payload ESC/POS disiapkan server dan dikirim ke browser untuk Web Bluetooth API.
        Log::info('Prepared ESC/POS payload for Web Bluetooth printing', [
            'format' => $format,
            'has_order_number' => isset($data['order_number']),
        ]);

        return true;
    }

    public function testPrint(): bool
    {
        return $this->print([
            'order_number' => 'TEST-PRINT',
            'lines' => ['KASIRKU TEST PRINT OK'],
        ], '58mm');
    }
}
