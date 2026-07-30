<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now()->utc();
        $featureRecords = [
            ['feature_key' => 'crm.contacts', 'display_name' => 'CRM Contacts', 'description' => 'Customer and lead relationship management'],
            ['feature_key' => 'crm.pipeline', 'display_name' => 'CRM Pipeline', 'description' => 'Lead tracking and conversion workflow'],
            ['feature_key' => 'finance.invoicing', 'display_name' => 'Invoicing', 'description' => 'Invoices and receivable workflows'],
            ['feature_key' => 'finance.expenses', 'display_name' => 'Expense Tracking', 'description' => 'Categorized operating expenses'],
            ['feature_key' => 'analytics.kpi', 'display_name' => 'KPI Dashboards', 'description' => 'Daily metric tracking and dashboards'],
            ['feature_key' => 'content.pages', 'display_name' => 'Content Pages', 'description' => 'Tenant website page publishing'],
            ['feature_key' => 'content.forms', 'display_name' => 'Public Forms', 'description' => 'Public lead and support forms'],
            ['feature_key' => 'ops.location-management', 'display_name' => 'Location Management', 'description' => 'Organization and location hierarchy'],
            ['feature_key' => 'ops.user-management', 'display_name' => 'User Management', 'description' => 'Tenant user access and role assignment'],
            ['feature_key' => 'support.session-access', 'display_name' => 'Support Access', 'description' => 'Support staff session access'],
        ];

        $rows = array_map(static fn (array $feature) => [
            'feature_key' => $feature['feature_key'],
            'display_name' => $feature['display_name'],
            'description' => $feature['description'],
            'is_active' => true,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $featureRecords);

        DB::connection('central')
            ->table('platform_features')
            ->upsert(
                $rows,
                ['feature_key'],
                ['display_name', 'description', 'is_active', 'updated_at']
            );
    }
}
