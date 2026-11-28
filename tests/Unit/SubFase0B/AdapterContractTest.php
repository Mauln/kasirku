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
use App\Services\Adapters\Email\LogEmailSender;
use App\Services\Adapters\Email\MailpitEmailSender;
use App\Services\Adapters\Email\SesEmailSender;
use App\Services\Adapters\Error\FakeErrorReporter;
use App\Services\Adapters\Error\LogErrorReporter;
use App\Services\Adapters\Error\SentryErrorReporter;
use App\Services\Adapters\Link\BookStackLinkProvider;
use App\Services\Adapters\Link\ChatwootLinkProvider;
use App\Services\Adapters\Link\InstatusLinkProvider;
use App\Services\Adapters\Link\MailtoLinkProvider;
use App\Services\Adapters\Link\StaticMarkdownLinkProvider;
use App\Services\Adapters\Link\UptimeKumaLinkProvider;
use App\Services\Adapters\Otp\CompositeOtpSender;
use App\Services\Adapters\Otp\EmailOtpSender;
use App\Services\Adapters\Otp\FakeOtpSender;
use App\Services\Adapters\Otp\FonnteOtpSender;
use App\Services\Adapters\Otp\LogOtpSender;
use App\Services\Adapters\Printer\BluetoothPrinterDriver;
use App\Services\Adapters\Printer\CssPrintDriver;
use App\Services\Adapters\Printer\FakePrinterDriver;
use App\Services\Adapters\Printer\LogPrinterDriver;
use App\Services\Adapters\Storage\FakeStorageDriver;
use App\Services\Adapters\Storage\LocalStorageDriver;
use App\Services\Adapters\Storage\S3StorageDriver;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

it('resolves all 8 contracts across fake, log, and production driver bindings', function (): void {
    config()->set('services.email.driver', 'fake');
    config()->set('services.otp.driver', 'fake');
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
    expect(app(SupportLinkProvider::class)->url())->toBe('mailto:support@kasirku.id');
    expect(app(KnowledgeBaseLinkProvider::class)->url())->toBe('/help');
    expect(app(StatusPageLinkProvider::class)->url())->toBe('https://kasirku.instatus.com');

    config()->set('services.email.driver', 'log');
    expect(app(EmailSender::class))->toBeInstanceOf(LogEmailSender::class);
    config()->set('services.email.driver', 'mailpit');
    expect(app(EmailSender::class))->toBeInstanceOf(MailpitEmailSender::class);
    config()->set('services.email.driver', 'ses');
    expect(app(EmailSender::class))->toBeInstanceOf(SesEmailSender::class);

    config()->set('services.otp.fonnte', ['token' => 'tok', 'url' => 'https://api.fonnte.com/send']);
    config()->set('services.otp.driver', 'log');
    expect(app(OtpSender::class))->toBeInstanceOf(LogOtpSender::class);
    config()->set('services.otp.driver', 'email');
    expect(app(OtpSender::class))->toBeInstanceOf(EmailOtpSender::class);
    config()->set('services.otp.driver', 'fonnte');
    expect(app(OtpSender::class))->toBeInstanceOf(FonnteOtpSender::class);
    config()->set('services.otp.driver', 'composite');
    expect(app(OtpSender::class))->toBeInstanceOf(CompositeOtpSender::class);

    config()->set('services.storage.driver', 'local');
    expect(app(StorageDriver::class))->toBeInstanceOf(LocalStorageDriver::class);
    config()->set('services.storage.driver', 's3');
    expect(app(StorageDriver::class))->toBeInstanceOf(S3StorageDriver::class);

    config()->set('services.error_tracker.driver', 'log');
    expect(app(ErrorReporter::class))->toBeInstanceOf(LogErrorReporter::class);
    config()->set('services.error_tracker.dsn', 'https://public@sentry.io/1');
    config()->set('services.error_tracker.driver', 'sentry');
    expect(app(ErrorReporter::class))->toBeInstanceOf(SentryErrorReporter::class);

    config()->set('services.support.chat_driver', 'chatwoot');
    expect(app(SupportLinkProvider::class))->toBeInstanceOf(ChatwootLinkProvider::class);
    config()->set('services.support.kb_driver', 'bookstack');
    expect(app(KnowledgeBaseLinkProvider::class))->toBeInstanceOf(BookStackLinkProvider::class);
    config()->set('services.support.status_driver', 'uptime_kuma');
    expect(app(StatusPageLinkProvider::class))->toBeInstanceOf(UptimeKumaLinkProvider::class);

    config()->set('services.printer.driver', 'log');
    expect(app(PrinterDriver::class))->toBeInstanceOf(LogPrinterDriver::class);
    config()->set('services.printer.driver', 'bluetooth');
    expect(app(PrinterDriver::class))->toBeInstanceOf(BluetoothPrinterDriver::class);
    config()->set('services.printer.driver', 'css');
    expect(app(PrinterDriver::class))->toBeInstanceOf(CssPrintDriver::class);
});

it('throws RuntimeException when resolving unknown drivers for any contract', function (): void {
    $keys = [
        ['services.email.driver', EmailSender::class],
        ['services.otp.driver', OtpSender::class],
        ['services.storage.driver', StorageDriver::class],
        ['services.error_tracker.driver', ErrorReporter::class],
        ['services.support.chat_driver', SupportLinkProvider::class],
        ['services.support.kb_driver', KnowledgeBaseLinkProvider::class],
        ['services.support.status_driver', StatusPageLinkProvider::class],
        ['services.printer.driver', PrinterDriver::class],
    ];

    foreach ($keys as [$configKey, $contract]) {
        config()->set($configKey, 'invalid-driver-xyz');
        expect(fn () => app($contract))->toThrow(RuntimeException::class);
    }
});

it('exercises all OTP adapters including CompositeOtpSender fallback and error handling', function (): void {
    $fakeOtp = new FakeOtpSender;
    expect($fakeOtp->send('08123456789', '111222'))->toBeTrue();
    expect($fakeOtp->getSent())->toHaveCount(1);
    $fakeOtp->forceFailure(true);
    expect($fakeOtp->send('08123456789', '111222'))->toBeFalse();
    $fakeOtp->reset();
    expect($fakeOtp->getSent())->toBeEmpty();

    $logOtp = new LogOtpSender;
    expect($logOtp->send('1234', '999999'))->toBeTrue();
    expect($logOtp->send('081234567890', '999999'))->toBeTrue();

    Http::fake([
        'api.fonnte.com/*' => Http::sequence()
            ->push(['status' => true], 200)
            ->push(['status' => false], 500),
    ]);

    $fonnte = new FonnteOtpSender(['token' => 'secret-token', 'url' => 'https://api.fonnte.com/send']);
    expect($fonnte->send('08123456789', '123456'))->toBeTrue();
    expect($fonnte->send('08123456789', '123456'))->toBeFalse();

    expect(fn () => new FonnteOtpSender(['token' => '', 'url' => '']))->toThrow(RuntimeException::class);
    expect(fn () => new FonnteOtpSender(['token' => 'tok', 'url' => 'http://insecure.test']))->toThrow(RuntimeException::class);

    $primaryOk = new FakeOtpSender;
    $emailOk = new FakeEmailSender;
    $compositeSuccess = new CompositeOtpSender($primaryOk, new EmailOtpSender($emailOk));
    expect($compositeSuccess->send('merchant@example.com', '654321'))->toBeTrue();
    expect($emailOk->getOutbox())->toBeEmpty();

    $primaryThrows = new class implements OtpSender
    {
        public function send(string $target, string $code, string $channel = 'whatsapp'): bool
        {
            throw new RuntimeException('Primary gateway down');
        }
    };
    $fallbackEmail = new FakeEmailSender;
    $compositeFallback = new CompositeOtpSender($primaryThrows, new EmailOtpSender($fallbackEmail));
    expect($compositeFallback->send('merchant@example.com', '123456', 'whatsapp'))->toBeTrue();
    expect($fallbackEmail->getOutbox())->toHaveCount(1);

    $fallbackFails = new FakeEmailSender;
    $fallbackFails->forceFailure(true);
    $primaryFails = new FakeOtpSender;
    $primaryFails->forceFailure(true);
    expect((new CompositeOtpSender($primaryFails, new EmailOtpSender($fallbackFails)))->send('a@b.c', '111111'))->toBeFalse();

    $fallbackThrows = new class implements OtpSender
    {
        public function send(string $target, string $code, string $channel = 'whatsapp'): bool
        {
            throw new RuntimeException('Fallback gateway down');
        }
    };
    expect((new CompositeOtpSender($primaryFails, $fallbackThrows))->send('a@b.c', '111111'))->toBeFalse();
});

it('exercises all email, storage, error, link, and printer adapters safely', function (): void {
    Mail::fake();
    expect((new LogEmailSender)->send('a@b.id', 'Subj', 'Body'))->toBeTrue();
    expect((new MailpitEmailSender)->send('a@b.id', 'Subj', 'Body'))->toBeTrue();
    expect((new SesEmailSender)->send('a@b.id', 'Subj', 'Body'))->toBeTrue();

    $fakeEmail = new FakeEmailSender;
    expect($fakeEmail->send('a@b.id', 'Subj', 'Body'))->toBeTrue();
    expect($fakeEmail->getOutbox())->toHaveCount(1);
    $fakeEmail->forceFailure(true);
    expect($fakeEmail->send('a@b.id', 'Subj', 'Body'))->toBeFalse();
    $fakeEmail->reset();
    expect($fakeEmail->getOutbox())->toBeEmpty();

    Storage::fake('local');
    $localStorage = new LocalStorageDriver('local');
    expect($localStorage->put('/receipts/r1.txt', 'OK'))->toBe('receipts/r1.txt');
    expect($localStorage->url('receipts/r1.txt'))->toContain('receipts/r1.txt');
    expect($localStorage->delete('receipts/r1.txt'))->toBeTrue();
    expect(fn () => $localStorage->put('../../.env', 'x'))->toThrow(InvalidArgumentException::class);

    $fakeStorage = new FakeStorageDriver;
    expect($fakeStorage->put('exports/sales.csv', 'a,b'))->toBe('exports/sales.csv');
    expect($fakeStorage->url('exports/sales.csv'))->toBe('https://fake-storage.kasirku.test/exports/sales.csv');
    expect($fakeStorage->get('exports/sales.csv'))->toBe('a,b');
    expect($fakeStorage->all())->toHaveKey('exports/sales.csv');
    expect($fakeStorage->delete('exports/sales.csv'))->toBeTrue();
    expect($fakeStorage->delete('exports/sales.csv'))->toBeFalse();
    expect(fn () => $fakeStorage->put('../secret', 'x'))->toThrow(InvalidArgumentException::class);

    Storage::fake('s3');
    Storage::disk('s3')->buildTemporaryUrlsUsing(
        fn (string $path, DateTimeInterface $expiration): string => 'https://s3.kasirku.test/'.$path.'?exp='.$expiration->getTimestamp()
    );
    $s3 = new S3StorageDriver('s3');
    expect($s3->put('invoices/i1.pdf', 'PDF'))->toBe('invoices/i1.pdf');
    expect($s3->url('invoices/i1.pdf'))->toContain('invoices/i1.pdf');
    expect($s3->delete('invoices/i1.pdf'))->toBeTrue();
    expect(fn () => $s3->put('../bad', 'x'))->toThrow(InvalidArgumentException::class);

    $ex = new RuntimeException('Simulated error');
    (new LogErrorReporter)->report($ex, ['tenant_id' => 1]);
    $fakeError = new FakeErrorReporter;
    $fakeError->report($ex, ['tenant_id' => 1]);
    expect($fakeError->getReported())->toHaveCount(1);
    $fakeError->reset();
    expect($fakeError->getReported())->toBeEmpty();
    (new SentryErrorReporter(['dsn' => 'https://public@sentry.io/1']))->report($ex);
    expect(fn () => new SentryErrorReporter(['dsn' => '']))->toThrow(RuntimeException::class);

    expect((new MailtoLinkProvider(''))->url())->toBe('mailto:support@kasirku.id');
    expect((new ChatwootLinkProvider('https://chat.kasirku.id'))->url())->toBe('https://chat.kasirku.id');
    expect((new StaticMarkdownLinkProvider(''))->url())->toBe('/help');
    expect((new BookStackLinkProvider('https://kb.kasirku.id'))->url())->toBe('https://kb.kasirku.id');
    expect((new InstatusLinkProvider(''))->url())->toBe('https://kasirku.instatus.com');
    expect((new UptimeKumaLinkProvider('https://status.kasirku.id'))->url())->toBe('https://status.kasirku.id');

    $fakePrinter = new FakePrinterDriver;
    expect($fakePrinter->print(['order_number' => 'INV-1']))->toBeTrue();
    expect($fakePrinter->testPrint())->toBeTrue();
    expect($fakePrinter->getJobs())->toHaveCount(1);
    expect($fakePrinter->getTestPrintCount())->toBe(1);

    foreach ([new LogPrinterDriver, new BluetoothPrinterDriver, new CssPrintDriver] as $printer) {
        expect($printer->print(['order_number' => 'INV-2']))->toBeTrue();
        expect($printer->testPrint())->toBeTrue();
    }
});
