<?php

declare(strict_types=1);

namespace App\Services\Adapters\Storage;

use App\Contracts\StorageDriver;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class S3StorageDriver implements StorageDriver
{
    public function __construct(private readonly string $disk = 's3') {}

    public function put(string $path, string $content): string
    {
        $normalized = $this->sanitizeRelativePath($path);
        Storage::disk($this->disk)->put($normalized, $content);

        return $normalized;
    }

    public function url(string $path): string
    {
        $normalized = $this->sanitizeRelativePath($path);

        return Storage::disk($this->disk)->temporaryUrl($normalized, now()->addMinutes(15));
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
            throw new InvalidArgumentException('Path storage S3 tidak valid.');
        }

        return $trimmed;
    }
}
