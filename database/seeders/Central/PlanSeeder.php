<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now()->utc();

        $plans = [
            [
                'plan_key' => 'small-business-starter',
                'display_name' => 'Small Business Starter',
                'monthly_price_cents' => 29900,
                'features' => [
                    'crm.contacts',
                    'crm.pipeline',
                    'finance.invoicing',
                    'content.pages',
                    'content.forms',
                ],
            ],
            [
                'plan_key' => 'regional-growth',
                'display_name' => 'Regional Growth',
                'monthly_price_cents' => 89900,
                'features' => [
                    'crm.contacts',
                    'crm.pipeline',
                    'finance.invoicing',
                    'finance.expenses',
                    'analytics.kpi',
                    'content.pages',
                    'content.forms',
                    'ops.location-management',
                    'ops.user-management',
                ],
            ],
            [
                'plan_key' => 'enterprise-nationwide',
                'display_name' => 'Enterprise Nationwide',
                'monthly_price_cents' => 249900,
                'features' => [
                    'crm.contacts',
                    'crm.pipeline',
                    'finance.invoicing',
                    'finance.expenses',
                    'analytics.kpi',
                    'content.pages',
                    'content.forms',
                    'ops.location-management',
                    'ops.user-management',
                    'support.session-access',
                ],
            ],
        ];

        $planRecords = [];
        $featurePivotRecords = [];

        foreach ($plans as $plan) {
            $planRecords[] = [
                'plan_key' => $plan['plan_key'],
                'display_name' => $plan['display_name'],
                'monthly_price_cents' => $plan['monthly_price_cents'],
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach ($plan['features'] as $featureKey) {
                $featurePivotRecords[] = [
                    'plan_key' => $plan['plan_key'],
                    'feature_key' => $featureKey,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::connection('central')
            ->table('platform_plans')
            ->upsert(
                $planRecords,
                ['plan_key'],
                ['display_name', 'monthly_price_cents', 'is_active', 'updated_at']
            );

        DB::connection('central')
            ->table('platform_plan_features')
            ->upsert(
                $featurePivotRecords,
                ['plan_key', 'feature_key'],
                ['updated_at']
            );
    }
}
