<?php

declare(strict_types=1);

use App\Models\AddOn;
use App\Models\AdminUser;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
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
});
