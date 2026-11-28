<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Seeder;

class AddOnSeeder extends Seeder
{
    public function run(): void
    {
        $addOns = [
            [
                'code' => 'extra_pos',
                'name' => 'Tambahan Perangkat POS',
                'unit_price' => 49000.00,
                'unit' => 'device/bulan',
                'is_active' => true,
            ],
            [
                'code' => 'extra_employee',
                'name' => 'Tambahan Akun Karyawan Kasir',
                'unit_price' => 15000.00,
                'unit' => 'employee/bulan',
                'is_active' => true,
            ],
            [
                'code' => 'extra_admin',
                'name' => 'Tambahan Hak Akses Admin',
                'unit_price' => 35000.00,
                'unit' => 'admin/bulan',
                'is_active' => true,
            ],
        ];

        foreach ($addOns as $addOn) {
            AddOn::query()->updateOrCreate(
                ['code' => $addOn['code']],
                $addOn
            );
        }
    }
}
