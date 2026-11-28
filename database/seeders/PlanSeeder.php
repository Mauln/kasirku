<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'trial',
                'name' => 'Trial 14 Hari',
                'monthly_price' => 0.00,
                'yearly_price' => null,
                'max_pos' => 999,
                'max_employees' => 999,
                'max_admins' => 999,
                'trial_days' => 14,
                'is_trial_plan' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'starter_basic',
                'name' => 'Starter Basic',
                'monthly_price' => 149000.00,
                'yearly_price' => 1484040.00,
                'max_pos' => 1,
                'max_employees' => 3,
                'max_admins' => 1,
                'trial_days' => 0,
                'is_trial_plan' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'starter',
                'name' => 'Starter',
                'monthly_price' => 249000.00,
                'yearly_price' => 2480040.00,
                'max_pos' => 2,
                'max_employees' => 5,
                'max_admins' => 1,
                'trial_days' => 0,
                'is_trial_plan' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'advance',
                'name' => 'Advance',
                'monthly_price' => 449000.00,
                'yearly_price' => 4472040.00,
                'max_pos' => 3,
                'max_employees' => 10,
                'max_admins' => 2,
                'trial_days' => 0,
                'is_trial_plan' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'prime',
                'name' => 'Prime',
                'monthly_price' => 899000.00,
                'yearly_price' => 8954040.00,
                'max_pos' => 5,
                'max_employees' => 20,
                'max_admins' => 5,
                'trial_days' => 0,
                'is_trial_plan' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'prime_plus',
                'name' => 'Prime Plus',
                'monthly_price' => 1499000.00,
                'yearly_price' => 14930040.00,
                'max_pos' => 5,
                'max_employees' => 25,
                'max_admins' => 5,
                'trial_days' => 0,
                'is_trial_plan' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
