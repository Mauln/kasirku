<?php

declare(strict_types=1);

namespace App\Contracts;

interface EmailSender
{
    /**
     * Mengirim email transaksional atau notifikasi sistem.
     *
     * @param  array<string, mixed>  $options
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool;
}
