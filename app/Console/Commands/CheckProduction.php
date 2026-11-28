<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exceptions\ProductionGuardException;
use App\Services\Guard\ProductionGuardService;
use Illuminate\Console\Command;

final class CheckProduction extends Command
{
    protected $signature = 'app:check-production {--dry-run : Tampilkan laporan evaluasi tanpa menggagalkan exit code CI}';

    protected $description = 'Evaluasi 8 Guard Rules Production Kasirku sebelum aplikasi berjalan atau deploy';

    public function handle(ProductionGuardService $guardService): int
    {
        $results = $guardService->evaluate();

        $rows = array_map(static fn (array $item): array => [
            '#' . $item['rule'],
            $item['env_key'],
            $item['passed'] ? 'PASS' : 'FAIL',
            $item['actual'],
            $item['expected'],
        ], $results);

        $this->table(['Rule', 'Env Key', 'Status', 'Actual', 'Expected'], $rows);

        try {
            $guardService->assertProductionSafe();
            $this->info('Semua 8 Guard Rules Production LULUS.');

            return self::SUCCESS;
        } catch (ProductionGuardException $exception) {
            $this->error($exception->getMessage());

            if ((bool) $this->option('dry-run')) {
                $this->warn('Mode --dry-run aktif: laporan kegagalan dicatat untuk validasi CI.');

                return self::SUCCESS;
            }

            return self::FAILURE;
        }
    }
}
