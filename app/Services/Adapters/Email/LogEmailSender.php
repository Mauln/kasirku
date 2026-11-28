<?php

declare(strict_types=1);

namespace App\Services\Adapters\Email;

use App\Contracts\EmailSender;
use Illuminate\Support\Facades\Log;

final class LogEmailSender implements EmailSender
{
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        Log::info('Email dispatched via log driver', [
            'to' => $to,
            'subject' => $subject,
            'body_bytes' => strlen($body),
            'options' => $options,
        ]);

        return true;
    }
}
