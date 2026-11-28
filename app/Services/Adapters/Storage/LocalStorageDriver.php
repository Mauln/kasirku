<?php

declare(strict_types=1);

namespace App\Services\Adapters\Storage;

use App\Contracts\StorageDriver;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class LocalStorageDriver implements StorageDriver
{
    public function __construct(private readonly string $disk = 'local') {}

    public function put(string $path, string $content): string
    {
        $normalized = $this->sanitizeRelativePath($path);
        Storage::disk($this->disk)->put($normalized, $content);

        return $normalized;
    }

    public function url(string $path): string
    {
        $normalized = $this->sanitizeRelativePath($path);

        return Storage::disk($this->disk)->url($normalized);
    }

    public function delete(string $path): bool
    {
        $normalized = $this->sanitizeRelativePath($path);

        return Storage::disk($this->disk)->delete($normalized);
    }

    private function sanitizeRelativePath(string $path): string
    {
        $trimmed = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($trimmed === '' || str_contains($trimmed, '..') || str_contains($trimmed, "\0")) {
            throw new InvalidArgumentException('Path storage tidak valid.');
        }

        return $trimmed;
    }
}
