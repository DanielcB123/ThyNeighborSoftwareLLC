<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now()->utc();

        $modules = [
            [
                'module_key' => 'crm',
                'display_name' => 'CRM',
                'description' => 'Lead, customer, and pipeline workflows',
                'features' => ['crm.contacts', 'crm.pipeline'],
            ],
            [
                'module_key' => 'finance',
                'display_name' => 'Finance',
                'description' => 'Invoicing, payments, and expense tracking',
                'features' => ['finance.invoicing', 'finance.expenses'],
            ],
            [
                'module_key' => 'analytics',
                'display_name' => 'Analytics',
                'description' => 'KPI dashboards and performance summaries',
                'features' => ['analytics.kpi'],
            ],
            [
                'module_key' => 'content',
                'display_name' => 'Content',
                'description' => 'Website pages and forms',
                'features' => ['content.pages', 'content.forms'],
            ],
            [
                'module_key' => 'operations',
                'display_name' => 'Operations',
                'description' => 'Users, organizations, and locations',
                'features' => ['ops.location-management', 'ops.user-management'],
            ],
            [
                'module_key' => 'support',
                'display_name' => 'Support',
                'description' => 'Platform support access',
                'features' => ['support.session-access'],
            ],
        ];

        $moduleRecords = [];
        $featurePivotRecords = [];

        foreach ($modules as $module) {
            $moduleRecords[] = [
                'module_key' => $module['module_key'],
                'display_name' => $module['display_name'],
                'description' => $module['description'],
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach ($module['features'] as $featureKey) {
                $featurePivotRecords[] = [
                    'module_key' => $module['module_key'],
                    'feature_key' => $featureKey,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::connection('central')
            ->table('platform_modules')
            ->upsert(
                $moduleRecords,
                ['module_key'],
                ['display_name', 'description', 'is_active', 'updated_at']
            );

        DB::connection('central')
            ->table('platform_module_features')
            ->upsert(
                $featurePivotRecords,
                ['module_key', 'feature_key'],
                ['updated_at']
            );
    }
}
