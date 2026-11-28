<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $basicFeatures = [
            'pos.transaction',
            'report.basic',
            'export.csv',
            'payment.custom_method',
            'tax.three_methods',
            'service_charge.rounding',
            'receipt.customization',
            'shift.report',
            'cashier.advanced_settings',
        ];

        $starterFeatures = [
            ...$basicFeatures,
            'pos.split_bill',
            'pos.void_refund',
            'inventory.tracking',
            'product.modifiers',
            'pos.credit_sale',
        ];

        $advanceFeatures = [
            ...$starterFeatures,
            'pos.table_management',
            'invoice.digital',
            'crm.loyalty',
            'promo.voucher',
        ];

        $primeFeatures = [
            ...$advanceFeatures,
            'report.advanced',
            'promo.scheduled',
        ];

        $allFeatureCodes = Feature::query()->pluck('code')->all();

        $matrix = [
            'trial' => $allFeatureCodes,
            'starter_basic' => $basicFeatures,
            'starter' => $starterFeatures,
            'advance' => $advanceFeatures,
            'prime' => $primeFeatures,
            'prime_plus' => $allFeatureCodes,
        ];

        $plans = Plan::query()->get()->keyBy('code');
        $features = Feature::query()->get();

        foreach ($matrix as $planCode => $enabledCodes) {
            $plan = $plans->get($planCode);
            if ($plan === null) {
                continue;
            }

            foreach ($features as $feature) {
                PlanFeature::query()->updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'is_enabled' => in_array($feature->code, $enabledCodes, true),
                    ]
                );
            }
        }
    }
}
