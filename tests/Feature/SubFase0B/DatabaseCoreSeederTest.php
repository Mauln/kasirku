<?php

declare(strict_types=1);

use App\Models\AddOn;
use App\Models\AdminSession;
use App\Models\AdminUser;
use App\Models\Feature;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\PlanFeature;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates all 7 core tables in shared and admin/internal domains', function (): void {
    foreach (['plans', 'features', 'plan_features', 'add_ons', 'admin_users', 'admin_sessions', 'partners'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});

it('seeds 6 plans, 21 features, 126 plan_features, 3 add_ons, and 1 admin idempotently', function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Plan::query()->count())->toBe(6);
    expect(Feature::query()->count())->toBe(21);
    expect(PlanFeature::query()->count())->toBe(126);
    expect(AddOn::query()->count())->toBe(3);
    expect(AdminUser::query()->where('role', 'super_admin')->count())->toBe(1);

    $starterBasic = Plan::query()->where('code', 'starter_basic')->firstOrFail();
    expect($starterBasic->features()->wherePivot('is_enabled', true)->count())->toBe(9);

    $firstFeature = Feature::query()->firstOrFail();
    expect($firstFeature->plans()->count())->toBe(6);

    $pivotRow = PlanFeature::query()->firstOrFail();
    expect($pivotRow->plan)->toBeInstanceOf(Plan::class);
    expect($pivotRow->feature)->toBeInstanceOf(Feature::class);

    $admin = AdminUser::query()->firstOrFail();
    $session = AdminSession::query()->create([
        'session_id' => 'sess-subfase-0b-test-0001',
        'admin_user_id' => $admin->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PestTestAgent',
        'mfa_verified_at' => now(),
    ]);
    expect($admin->sessions()->count())->toBe(1);
    expect($session->adminUser->id)->toBe($admin->id);

    $partner = Partner::query()->create([
        'name' => 'Komunitas UMKM Nusantara',
        'code' => 'UMKM-01',
        'email' => 'partner@kasirku.test',
        'commission_rate' => 12.50,
        'is_active' => true,
    ]);
    expect($partner->is_active)->toBeTrue();
});

it('requires SEED_ADMIN_PASSWORD in production when running AdminSeeder', function (): void {
    $previousPassword = $_ENV['SEED_ADMIN_PASSWORD'] ?? null;
    putenv('SEED_ADMIN_PASSWORD=');
    $_ENV['SEED_ADMIN_PASSWORD'] = '';

    $originalEnv = app()->environment();
    app()->detectEnvironment(static fn (): string => 'production');

    try {
        expect(fn () => (new AdminSeeder)->run())->toThrow(RuntimeException::class);
    } finally {
        app()->detectEnvironment(static fn (): string => $originalEnv);
        if ($previousPassword === null) {
            putenv('SEED_ADMIN_PASSWORD');
            unset($_ENV['SEED_ADMIN_PASSWORD']);
        } else {
            putenv('SEED_ADMIN_PASSWORD='.$previousPassword);
            $_ENV['SEED_ADMIN_PASSWORD'] = $previousPassword;
        }
    }
});
