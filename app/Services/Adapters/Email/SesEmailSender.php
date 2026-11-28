<?php

declare(strict_types=1);

namespace App\Services\Adapters\Email;

use App\Contracts\EmailSender;
use Illuminate\Support\Facades\Mail;

final class SesEmailSender implements EmailSender
{
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        Mail::mailer('ses')->raw($body, function ($message) use ($to, $subject): void {
            $message->to($to)->subject($subject);
        });

        return true;
    }
}
