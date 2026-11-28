<?php

declare(strict_types=1);

use App\Contracts\EmailSender;
use App\Contracts\ErrorReporter;
use App\Contracts\KnowledgeBaseLinkProvider;
use App\Contracts\OtpSender;
use App\Contracts\PrinterDriver;
use App\Contracts\StatusPageLinkProvider;
use App\Contracts\StorageDriver;
use App\Contracts\SupportLinkProvider;
use App\Services\Adapters\Email\FakeEmailSender;
use App\Services\Adapters\Error\FakeErrorReporter;
use App\Services\Adapters\Otp\CompositeOtpSender;
use App\Services\Adapters\Otp\EmailOtpSender;
use App\Services\Adapters\Otp\FakeOtpSender;
use App\Services\Adapters\Printer\FakePrinterDriver;
use App\Services\Adapters\Storage\FakeStorageDriver;
use App\Services\Adapters\Storage\LocalStorageDriver;

it('resolves all 8 contracts from Laravel container via config drivers', function (): void {
    config()->set('services.otp.driver', 'fake');
    config()->set('services.email.driver', 'fake');
    config()->set('services.storage.driver', 'fake');
    config()->set('services.error_tracker.driver', 'fake');
    config()->set('services.printer.driver', 'fake');
    config()->set('services.support.chat_driver', 'mailto');
    config()->set('services.support.chat_url', 'mailto:support@kasirku.id');
    config()->set('services.support.kb_driver', 'markdown');
    config()->set('services.support.kb_url', '/help');
    config()->set('services.support.status_driver', 'instatus');
    config()->set('services.support.status_url', 'https://kasirku.instatus.com');

    expect(app(OtpSender::class))->toBeInstanceOf(FakeOtpSender::class);
    expect(app(EmailSender::class))->toBeInstanceOf(FakeEmailSender::class);
    expect(app(StorageDriver::class))->toBeInstanceOf(FakeStorageDriver::class);
    expect(app(ErrorReporter::class))->toBeInstanceOf(FakeErrorReporter::class);
    expect(app(PrinterDriver::class))->toBeInstanceOf(FakePrinterDriver::class);
    expect(app(SupportLinkProvider::class)->url())->toStartWith('mailto:');
    expect(app(KnowledgeBaseLinkProvider::class)->url())->toBe('/help');
    expect(app(StatusPageLinkProvider::class)->url())->toContain('instatus');
});

it('falls back to EmailOtpSender when primary OTP fails in CompositeOtpSender', function (): void {
    $primary = new FakeOtpSender;
    $primary->forceFailure(true);

    $email = new FakeEmailSender;
    $composite = new CompositeOtpSender($primary, new EmailOtpSender($email));

    $ok = $composite->send('merchant@example.com', '123456', 'whatsapp');

    expect($ok)->toBeTrue();
    expect($email->getOutbox())->toHaveCount(1);
    expect($email->getOutbox()[0]['to'])->toBe('merchant@example.com');
});

it('blocks directory traversal on LocalStorageDriver', function (): void {
    $driver = new LocalStorageDriver('local');

    expect(fn () => $driver->put('../../.env', 'x'))->toThrow(InvalidArgumentException::class);
});
