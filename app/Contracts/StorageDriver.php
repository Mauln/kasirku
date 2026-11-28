<?php

declare(strict_types=1);

namespace App\Contracts;

interface StorageDriver
{
    /**
     * Menyimpan konten file ke path tujuan dan mengembalikan path tersimpan.
     */
    public function put(string $path, string $content): string;

    /**
     * Menghasilkan URL akses untuk file tersimpan.
     */
    public function url(string $path): string;

    /**
     * Menghapus file dari storage berdasarkan path.
     */
    public function delete(string $path): bool;
}
