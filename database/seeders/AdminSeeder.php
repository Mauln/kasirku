<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('SEED_ADMIN_EMAIL', ''));
        if ($email === '') {
            $email = 'superadmin@kasirku.local';
        }
        $rawPassword = trim((string) env('SEED_ADMIN_PASSWORD', ''));

        if ($rawPassword === '') {
            if (app()->isProduction()) {
                throw new RuntimeException('SEED_ADMIN_PASSWORD wajib diisi pada environment production.');
            }

            $rawPassword = Str::random(24);
        }

        AdminUser::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin Kasirku',
                'password_hash' => Hash::make($rawPassword),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
