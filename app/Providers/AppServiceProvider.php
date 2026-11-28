<?php

declare(strict_types=1);

namespace App\Providers;

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
use App\Services\Guard\ProductionGuardService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductionGuardService::class);

        $this->app->bind(EmailSender::class, function (): EmailSender {
            return match ((string) config('services.email.driver', 'log')) {
                'log', 'array' => new LogEmailSender,
                'fake' => new FakeEmailSender,
                'mailpit', 'smtp' => new MailpitEmailSender,
                'ses' => new SesEmailSender,
                default => throw new RuntimeException('Unknown email driver'),
            };
        });

        $this->app->bind(OtpSender::class, function ($app): OtpSender {
            return match ((string) config('services.otp.driver', 'log')) {
                'log' => new LogOtpSender,
                'fake' => new FakeOtpSender,
                'fonnte' => new FonnteOtpSender((array) config('services.otp.fonnte', [])),
                'email' => new EmailOtpSender($app->make(EmailSender::class)),
                'composite' => new CompositeOtpSender(
                    new FonnteOtpSender((array) config('services.otp.fonnte', [])),
                    new EmailOtpSender($app->make(EmailSender::class)),
                ),
                default => throw new RuntimeException('Unknown OTP driver'),
            };
        });

        $this->app->bind(StorageDriver::class, function (): StorageDriver {
            return match ((string) config('services.storage.driver', 'local')) {
                'local' => new LocalStorageDriver,
                'fake' => new FakeStorageDriver,
                's3', 'minio', 'r2' => new S3StorageDriver('s3'),
                default => throw new RuntimeException('Unknown storage driver'),
            };
        });

        $this->app->bind(ErrorReporter::class, function (): ErrorReporter {
            return match ((string) config('services.error_tracker.driver', 'log')) {
                'log' => new LogErrorReporter,
                'fake' => new FakeErrorReporter,
                'sentry', 'glitchtip' => new SentryErrorReporter((array) config('services.error_tracker', [])),
                default => throw new RuntimeException('Unknown error tracker driver'),
            };
        });

        $this->app->bind(SupportLinkProvider::class, function (): SupportLinkProvider {
            $url = (string) config('services.support.chat_url', 'mailto:support@kasirku.id');

            return match ((string) config('services.support.chat_driver', 'mailto')) {
                'mailto' => new MailtoLinkProvider($url),
                'chatwoot' => new ChatwootLinkProvider($url),
                default => throw new RuntimeException('Unknown support chat driver'),
            };
        });

        $this->app->bind(KnowledgeBaseLinkProvider::class, function (): KnowledgeBaseLinkProvider {
            $url = (string) config('services.support.kb_url', '/help');

            return match ((string) config('services.support.kb_driver', 'markdown')) {
                'markdown' => new StaticMarkdownLinkProvider($url),
                'bookstack' => new BookStackLinkProvider($url),
                default => throw new RuntimeException('Unknown knowledge base driver'),
            };
        });

        $this->app->bind(StatusPageLinkProvider::class, function (): StatusPageLinkProvider {
            $url = (string) config('services.support.status_url', 'https://kasirku.instatus.com');

            return match ((string) config('services.support.status_driver', 'instatus')) {
                'instatus' => new InstatusLinkProvider($url),
                'uptime_kuma' => new UptimeKumaLinkProvider($url),
                default => throw new RuntimeException('Unknown status page driver'),
            };
        });

        $this->app->bind(PrinterDriver::class, function (): PrinterDriver {
            return match ((string) config('services.printer.driver', 'log')) {
                'log' => new LogPrinterDriver,
                'fake' => new FakePrinterDriver,
                'bluetooth' => new BluetoothPrinterDriver,
                'css' => new CssPrintDriver,
                default => throw new RuntimeException('Unknown printer driver'),
            };
        });
    }

    public function boot(): void
    {
        if ($this->app->isProduction() && (bool) config('kasirku.guard_rules_enforced', false)) {
            $this->app->make(ProductionGuardService::class)->assertProductionSafe();
        }

        if ($this->app->bound('view')) {
            View::share('app', [
                'env' => $this->app->environment(),
                'deploy_env' => config('kasirku.deploy_env', 'local'),
                'version' => config('kasirku.version', '2.0.0-0b'),
                'git_commit' => config('kasirku.git_commit', 'local'),
                'is_production' => $this->app->environment('production'),
                'demo_mode' => (bool) config('kasirku.demo_mode', false),
                'show_env_badge' => ! $this->app->environment('production')
                    || (! $this->app->runningInConsole() && request()->is('admin/*')),
            ]);
        }
    }
}
