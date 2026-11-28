<?php

declare(strict_types=1);

namespace App\Contracts;

interface PrinterDriver
{
    /**
     * Mengirim payload struk ke driver printer thermal atau fallback cetak.
     *
     * @param  array<string, mixed>  $data
     */
    public function print(array $data, string $format = '58mm'): bool;

    /**
     * Menjalankan test print diagnostik perangkat.
     */
    public function testPrint(): bool;
}
