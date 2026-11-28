<?php

declare(strict_types=1);

namespace App\Services\Adapters\Email;

use App\Contracts\EmailSender;

final class FakeEmailSender implements EmailSender
{
    /** @var array<int, array{to: string, subject: string, body: string, options: array<string, mixed>}> */
    private array $outbox = [];

    private bool $shouldFail = false;

    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        if ($this->shouldFail) {
            return false;
        }

        $this->outbox[] = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'options' => $options,
        ];

        return true;
    }

    public function forceFailure(bool $fail = true): void
    {
        $this->shouldFail = $fail;
    }

    /**
     * @return array<int, array{to: string, subject: string, body: string, options: array<string, mixed>}>
     */
    public function getOutbox(): array
    {
        return $this->outbox;
    }

    public function reset(): void
    {
        $this->outbox = [];
        $this->shouldFail = false;
    }
}
