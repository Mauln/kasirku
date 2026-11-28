<?php

declare(strict_types=1);

namespace App\Services\Adapters\Storage;

use App\Contracts\StorageDriver;
use InvalidArgumentException;

final class FakeStorageDriver implements StorageDriver
{
    /** @var array<string, string> */
    private array $files = [];

    public function put(string $path, string $content): string
    {
        $normalized = $this->normalize($path);
        $this->files[$normalized] = $content;

        return $normalized;
    }

    public function url(string $path): string
    {
        return 'https://fake-storage.kasirku.test/' . $this->normalize($path);
    }

    public function delete(string $path): bool
    {
        $normalized = $this->normalize($path);
        if (! array_key_exists($normalized, $this->files)) {
            return false;
        }

        unset($this->files[$normalized]);

        return true;
    }

    public function get(string $path): ?string
    {
        return $this->files[$this->normalize($path)] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->files;
    }

    private function normalize(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($clean === '' || str_contains($clean, '..')) {
            throw new InvalidArgumentException('Path storage tidak valid.');
        }

        return $clean;
    }
}
