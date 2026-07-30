<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoTenantSeedContextData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class DemoTenantDataSeeder
{
    public function seedCore(DemoTenantSeedContextData $context): void
    {
        DB::connection($context->connectionName)
            ->table('tenant_metadata')
            ->upsert(
                [[
                    'tenant_public_id' => $context->tenantPublicId,
                    'tenant_slug' => $context->tenantSlug,
                    'tenant_display_name' => $context->tenantDisplayName,
                    'tenant_domain' => $context->tenantDomain,
                    'dataset_version' => $context->datasetVersion,
                    'dataset_profile' => $context->profile->value,
                    'reference_date' => $context->referenceDate->toDateString(),
                    'seeded_at' => now()->utc(),
                    'created_at' => now()->utc(),
                    'updated_at' => now()->utc(),
                ]],
                ['tenant_public_id'],
                [
                    'tenant_slug',
                    'tenant_display_name',
                    'tenant_domain',
                    'dataset_version',
                    'dataset_profile',
                    'reference_date',
                    'seeded_at',
                    'updated_at',
                ]
            );
    }

    public function seedPermissions(DemoTenantSeedContextData $context): void
    {
        $permissionsConfig = require database_path('demo/permissions.php');
        $timestamp = now()->utc();

        $permissionRecords = [];
        foreach (($permissionsConfig['tenant_permissions'] ?? []) as $permissionKey) {
            $permissionRecords[] = [
                'permission_key' => (string) $permissionKey,
                'display_name' => $this->humanizePermissionKey((string) $permissionKey),
                'description' => 'Demo permission seed',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_permissions')
            ->upsert(
                $permissionRecords,
                ['permission_key'],
                ['display_name', 'description', 'updated_at']
            );
    }

    public function seedRoles(DemoTenantSeedContextData $context): void
    {
        $permissionsConfig = require database_path('demo/permissions.php');
        $tenantRoles = is_array($permissionsConfig['tenant_roles'] ?? null)
            ? $permissionsConfig['tenant_roles']
            : [];

        $timestamp = now()->utc();
        $roleRecords = [];
        $pivotRecords = [];

        foreach ($tenantRoles as $role) {
            if (! is_array($role)) {
                continue;
            }

            $roleKey = (string) ($role['key'] ?? '');
            if ($roleKey === '') {
                continue;
            }

            $roleRecords[] = [
                'role_key' => $roleKey,
                'display_name' => (string) ($role['name'] ?? $roleKey),
                'scope' => (string) ($role['scope'] ?? 'tenant'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach (($role['permissions'] ?? []) as $permissionKey) {
                $pivotRecords[] = [
                    'role_key' => $roleKey,
                    'permission_key' => (string) $permissionKey,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::connection($context->connectionName)
            ->table('tenant_roles')
            ->upsert(
                $roleRecords,
                ['role_key'],
                ['display_name', 'scope', 'updated_at']
            );

        DB::connection($context->connectionName)
            ->table('tenant_role_permissions')
            ->upsert(
                $pivotRecords,
                ['role_key', 'permission_key'],
                ['updated_at']
            );
    }

    public function seedOrganization(DemoTenantSeedContextData $context): void
    {
        $timestamp = now()->utc();
        $organizationRecords = [];
        $existingUnitKeys = [];

        foreach ($context->scenario->organizationUnits as $unit) {
            $unitKey = (string) ($unit['key'] ?? '');
            if ($unitKey === '') {
                continue;
            }

            $existingUnitKeys[] = $unitKey;
            $organizationRecords[] = [
                'organization_key' => $unitKey,
                'parent_organization_key' => (string) ($unit['parent'] ?? '') ?: null,
                'name' => (string) ($unit['name'] ?? strtoupper($unitKey)),
                'scope_type' => (string) ($unit['scope'] ?? 'department'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (! in_array('root', $existingUnitKeys, true)) {
            $organizationRecords[] = [
                'organization_key' => 'root',
                'parent_organization_key' => null,
                'name' => $context->tenantDisplayName,
                'scope_type' => 'company',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_organizations')
            ->upsert(
                $organizationRecords,
                ['organization_key'],
                ['parent_organization_key', 'name', 'scope_type', 'updated_at']
            );

        $organizationKeys = DB::connection($context->connectionName)
            ->table('tenant_organizations')
            ->pluck('organization_key')
            ->map('strval')
            ->all();

        $targetLocations = max(1, $context->targets['locations']);
        $locationRecords = [];

        for ($index = 1; $index <= $targetLocations; $index++) {
            $locationKey = sprintf(
                '%s-%03d',
                $context->scenario->locationPrefix,
                $index
            );

            $locationRecords[] = [
                'location_key' => $locationKey,
                'organization_key' => (string) ($context->randomizer->pick($organizationKeys) ?? 'root'),
                'name' => sprintf('%s Location %d', $context->tenantDisplayName, $index),
                'region' => $this->regionForIndex($index, $context->scenario->key),
                'status' => $context->randomizer->bool(92) ? 'active' : 'maintenance',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_locations')
            ->upsert(
                $locationRecords,
                ['location_key'],
                ['organization_key', 'name', 'region', 'status', 'updated_at']
            );
    }

    public function seedUsers(DemoTenantSeedContextData $context): void
    {
        $roles = DB::connection($context->connectionName)
            ->table('tenant_roles')
            ->pluck('role_key')
            ->map('strval')
            ->all();

        $existing = [];
        $timestamp = now()->utc();
        $rows = [];

        foreach ($context->scenario->users as $index => $entry) {
            $email = strtolower(trim((string) ($entry['email'] ?? '')));
            if ($email === '' || isset($existing[$email])) {
                continue;
            }

            $existing[$email] = true;
            $rows[] = [
                'user_public_id' => $this->deterministicPublicId($context, 'preset-user-'.$index),
                'name' => (string) ($entry['name'] ?? $email),
                'email' => $email,
                'password' => Hash::make($context->password),
                'role_key' => (string) ($entry['role'] ?? 'owner'),
                'scope' => (string) ($entry['scope'] ?? 'tenant'),
                'mfa_enabled' => (bool) ($entry['mfa_required'] ?? false),
                'is_demo' => true,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $targetUsers = max(count($rows), $context->targets['users']);

        for ($index = count($rows) + 1; $index <= $targetUsers; $index++) {
            $email = strtolower(sprintf(
                '%s.staff%03d@%s',
                str_replace('-', '', $context->tenantSlug),
                $index,
                $context->tenantDomain
            ));

            if (isset($existing[$email])) {
                continue;
            }

            $existing[$email] = true;
            $roleKey = (string) ($context->randomizer->pick($roles) ?? 'owner');

            $rows[] = [
                'user_public_id' => $this->deterministicPublicId($context, 'generated-user-'.$index),
                'name' => sprintf('%s Team Member %03d', $context->tenantDisplayName, $index),
                'email' => $email,
                'password' => Hash::make($context->password),
                'role_key' => $roleKey,
                'scope' => $this->scopeForRole($roleKey),
                'mfa_enabled' => $context->randomizer->bool(45),
                'is_demo' => true,
                'status' => 'active',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_users')
            ->upsert(
                $rows,
                ['email'],
                ['name', 'password', 'role_key', 'scope', 'mfa_enabled', 'status', 'updated_at']
            );
    }

    public function seedSettings(DemoTenantSeedContextData $context): void
    {
        $settings = [
            'tenant.display_name' => $context->tenantDisplayName,
            'tenant.domain' => $context->tenantDomain,
            'tenant.vertical' => $context->scenario->vertical,
            'tenant.locale' => $context->scenario->locale,
            'tenant.timezone' => $context->scenario->timezone,
            'demo.dataset.version' => $context->datasetVersion,
            'demo.dataset.profile' => $context->profile->value,
            'demo.dataset.reference_date' => $context->referenceDate->toDateString(),
            'demo.dataset.seeded_at' => now()->utc()->toIso8601String(),
        ];

        $timestamp = now()->utc();

        $rows = [];
        foreach ($settings as $key => $value) {
            $rows[] = [
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_settings')
            ->upsert(
                $rows,
                ['setting_key'],
                ['setting_value', 'updated_at']
            );
    }

    public function seedContent(DemoTenantSeedContextData $context): void
    {
        if (($context->skipFlags['skip-content'] ?? false) === true) {
            return;
        }

        $contentScenario = require database_path('demo/content-scenarios.php');
        $timestamp = now()->utc();

        $pageRows = [];
        foreach (($contentScenario['published_pages'] ?? []) as $index => $page) {
            if (! is_array($page)) {
                continue;
            }

            $slug = trim((string) ($page['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $pageRows[] = [
                'page_slug' => $slug,
                'title' => (string) ($page['title'] ?? strtoupper($slug)),
                'status' => 'published',
                'published_at' => $context->referenceDate->addDays($index),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_pages')
            ->upsert(
                $pageRows,
                ['page_slug'],
                ['title', 'status', 'published_at', 'updated_at']
            );
    }

    public function seedCrm(DemoTenantSeedContextData $context): void
    {
        $locationKeys = DB::connection($context->connectionName)
            ->table('tenant_locations')
            ->orderBy('id')
            ->pluck('location_key')
            ->map('strval')
            ->all();

        if ($locationKeys === []) {
            return;
        }

        $timestamp = now()->utc();

        $customerRows = [];
        $customerKeys = [];
        $targetCustomers = max(1, $context->targets['customers']);

        for ($index = 1; $index <= $targetCustomers; $index++) {
            $customerKey = sprintf('cust-%06d', $index);
            $customerKeys[] = $customerKey;

            $customerRows[] = [
                'customer_key' => $customerKey,
                'location_key' => (string) ($context->randomizer->pick($locationKeys) ?? $locationKeys[0]),
                'name' => sprintf('%s Customer %06d', $context->tenantDisplayName, $index),
                'email' => strtolower(sprintf('customer%06d@%s', $index, $context->tenantDomain)),
                'phone' => sprintf(
                    '+1-555-%03d-%04d',
                    $context->randomizer->int(100, 999),
                    $context->randomizer->int(1000, 9999)
                ),
                'status' => $context->randomizer->bool(95) ? 'active' : 'inactive',
                'created_at' => $context->referenceDate->copy()->subDays($context->randomizer->int(0, 730)),
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($customerRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_customers')
                ->upsert(
                    $batch,
                    ['customer_key'],
                    ['location_key', 'name', 'email', 'phone', 'status', 'updated_at']
                );
        }

        $leadRows = [];
        $targetLeads = max(1, $context->targets['leads']);
        $leadStatuses = ['new', 'qualified', 'proposal', 'won', 'lost'];

        for ($index = 1; $index <= $targetLeads; $index++) {
            $customerKey = (string) ($context->randomizer->pick($customerKeys) ?? '');
            $leadRows[] = [
                'lead_key' => sprintf('lead-%06d', $index),
                'customer_key' => $context->randomizer->bool(70) ? $customerKey : null,
                'location_key' => (string) ($context->randomizer->pick($locationKeys) ?? $locationKeys[0]),
                'status' => (string) ($context->randomizer->pick($leadStatuses) ?? 'new'),
                'source' => (string) ($context->randomizer->pick($context->scenario->leadSources) ?? 'website'),
                'estimated_value' => $this->randomMoney($context, 150, 9500),
                'created_at' => $context->referenceDate->copy()->subDays($context->randomizer->int(0, 730)),
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($leadRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_leads')
                ->upsert(
                    $batch,
                    ['lead_key'],
                    ['customer_key', 'location_key', 'status', 'source', 'estimated_value', 'updated_at']
                );
        }
    }

    public function seedForms(DemoTenantSeedContextData $context): void
    {
        if (($context->skipFlags['skip-content'] ?? false) === true) {
            return;
        }

        $contentScenario = require database_path('demo/content-scenarios.php');
        $timestamp = now()->utc();

        $formRows = [];
        foreach (($contentScenario['public_forms'] ?? []) as $form) {
            if (! is_array($form)) {
                continue;
            }

            $slug = trim((string) ($form['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $formRows[] = [
                'form_slug' => $slug,
                'name' => (string) ($form['name'] ?? strtoupper($slug)),
                'status' => 'published',
                'is_public' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::connection($context->connectionName)
            ->table('tenant_forms')
            ->upsert(
                $formRows,
                ['form_slug'],
                ['name', 'status', 'is_public', 'updated_at']
            );

        $formSlugs = array_map(
            static fn (array $form): string => (string) ($form['slug'] ?? ''),
            array_values(array_filter(($contentScenario['public_forms'] ?? []), static fn (mixed $item): bool => is_array($item)))
        );

        $submissionRows = [];
        $targetSubmissions = max(20, (int) round($context->targets['leads'] * 0.35));

        for ($index = 1; $index <= $targetSubmissions; $index++) {
            $formSlug = (string) ($context->randomizer->pick($formSlugs) ?? 'contact-us');
            $name = sprintf('Form Contact %05d', $index);
            $email = strtolower(sprintf('form.contact.%05d@%s', $index, $context->tenantDomain));

            $submissionRows[] = [
                'form_slug' => $formSlug,
                'submitted_at' => $context->referenceDate->copy()->subDays($context->randomizer->int(0, 365)),
                'contact_name' => $name,
                'contact_email' => $email,
                'payload_json' => json_encode([
                    'name' => $name,
                    'email' => $email,
                    'message' => 'Demo inquiry payload',
                    'source' => $formSlug,
                ], JSON_UNESCAPED_SLASHES),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($submissionRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_form_submissions')
                ->insert($batch);
        }
    }

    public function seedFinance(DemoTenantSeedContextData $context): void
    {
        if (($context->skipFlags['skip-finance'] ?? false) === true) {
            return;
        }

        $financialScenario = require database_path('demo/financial-scenarios.php');
        $invoiceStatuses = array_values(array_map(
            'strval',
            is_array($financialScenario['invoice_statuses'] ?? null) ? $financialScenario['invoice_statuses'] : ['sent', 'paid', 'overdue']
        ));
        $paymentMethods = array_values(array_map(
            'strval',
            is_array($financialScenario['payment_methods'] ?? null) ? $financialScenario['payment_methods'] : ['ach']
        ));
        $expenseCategories = array_values(array_map(
            'strval',
            is_array($financialScenario['expense_categories'] ?? null) ? $financialScenario['expense_categories'] : ['payroll']
        ));

        $ticketMin = (float) ($financialScenario['ticket_amount_range']['min'] ?? 125.00);
        $ticketMax = (float) ($financialScenario['ticket_amount_range']['max'] ?? 24500.00);
        $expenseMin = (float) ($financialScenario['expense_amount_range']['min'] ?? 55.00);
        $expenseMax = (float) ($financialScenario['expense_amount_range']['max'] ?? 9800.00);

        $customerKeys = DB::connection($context->connectionName)
            ->table('tenant_customers')
            ->pluck('customer_key')
            ->map('strval')
            ->all();
        $locationKeys = DB::connection($context->connectionName)
            ->table('tenant_locations')
            ->pluck('location_key')
            ->map('strval')
            ->all();

        if ($customerKeys === [] || $locationKeys === []) {
            return;
        }

        $timestamp = now()->utc();
        $invoiceRows = [];
        $invoiceBalances = [];
        $targetInvoices = max(1, $context->targets['invoices']);

        for ($index = 1; $index <= $targetInvoices; $index++) {
            $invoiceKey = sprintf('inv-%06d', $index);
            $issuedAt = $context->referenceDate->copy()->subDays($context->randomizer->int(0, 540));
            $totalAmount = $this->randomMoney($context, $ticketMin, $ticketMax);
            $status = (string) ($context->randomizer->pick($invoiceStatuses) ?? 'sent');

            $invoiceRows[] = [
                'invoice_key' => $invoiceKey,
                'customer_key' => (string) ($context->randomizer->pick($customerKeys) ?? $customerKeys[0]),
                'location_key' => (string) ($context->randomizer->pick($locationKeys) ?? $locationKeys[0]),
                'status' => $status,
                'issued_at' => $issuedAt->toDateString(),
                'due_at' => $issuedAt->copy()->addDays(30)->toDateString(),
                'total_amount' => $totalAmount,
                'balance_amount' => $totalAmount,
                'created_at' => $issuedAt,
                'updated_at' => $timestamp,
            ];

            $invoiceBalances[$invoiceKey] = $totalAmount;
        }

        foreach (array_chunk($invoiceRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_invoices')
                ->upsert(
                    $batch,
                    ['invoice_key'],
                    [
                        'customer_key',
                        'location_key',
                        'status',
                        'issued_at',
                        'due_at',
                        'total_amount',
                        'balance_amount',
                        'updated_at',
                    ]
                );
        }

        $invoiceKeys = array_keys($invoiceBalances);
        $paymentRows = [];
        $targetPayments = min(max(0, $context->targets['payments']), count($invoiceKeys) * 2);

        for ($index = 1; $index <= $targetPayments; $index++) {
            $invoiceKey = (string) ($context->randomizer->pick($invoiceKeys) ?? $invoiceKeys[0]);
            $currentBalance = (float) ($invoiceBalances[$invoiceKey] ?? 0.0);

            if ($currentBalance <= 0.0) {
                continue;
            }

            $fractionBasisPoints = $context->randomizer->int(3000, 10000);
            $paymentAmount = round(($currentBalance * $fractionBasisPoints) / 10000, 2);
            if ($paymentAmount <= 0) {
                $paymentAmount = min(1.00, $currentBalance);
            }

            if ($paymentAmount > $currentBalance) {
                $paymentAmount = $currentBalance;
            }

            $invoiceBalances[$invoiceKey] = round($currentBalance - $paymentAmount, 2);

            $paymentRows[] = [
                'payment_key' => sprintf('pay-%06d', $index),
                'invoice_key' => $invoiceKey,
                'paid_at' => $context->referenceDate->copy()->subDays($context->randomizer->int(0, 365))->toDateString(),
                'amount' => $paymentAmount,
                'method' => (string) ($context->randomizer->pick($paymentMethods) ?? 'ach'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($paymentRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_payments')
                ->upsert(
                    $batch,
                    ['payment_key'],
                    ['invoice_key', 'paid_at', 'amount', 'method', 'updated_at']
                );
        }

        foreach ($invoiceBalances as $invoiceKey => $remainingBalance) {
            $status = $remainingBalance <= 0.0 ? 'paid' : ($remainingBalance > 0 ? 'sent' : 'draft');
            DB::connection($context->connectionName)
                ->table('tenant_invoices')
                ->where('invoice_key', '=', $invoiceKey)
                ->update([
                    'balance_amount' => max(0, $remainingBalance),
                    'status' => $status,
                    'updated_at' => $timestamp,
                ]);
        }

        $expenseRows = [];
        $targetExpenses = max(1, $context->targets['expenses']);

        for ($index = 1; $index <= $targetExpenses; $index++) {
            $expenseRows[] = [
                'expense_key' => sprintf('exp-%06d', $index),
                'location_key' => (string) ($context->randomizer->pick($locationKeys) ?? $locationKeys[0]),
                'category' => (string) ($context->randomizer->pick($expenseCategories) ?? 'payroll'),
                'incurred_at' => $context->referenceDate->copy()->subDays($context->randomizer->int(0, 365))->toDateString(),
                'amount' => $this->randomMoney($context, $expenseMin, $expenseMax),
                'vendor_name' => (string) ($context->randomizer->pick($context->scenario->expenseVendors) ?? 'General Vendor'),
                'description' => 'Demo operating expense',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($expenseRows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_expenses')
                ->upsert(
                    $batch,
                    ['expense_key'],
                    ['location_key', 'category', 'incurred_at', 'amount', 'vendor_name', 'description', 'updated_at']
                );
        }
    }

    public function seedAnalytics(DemoTenantSeedContextData $context): void
    {
        if (($context->skipFlags['skip-analytics'] ?? false) === true) {
            return;
        }

        $kpiScenario = require database_path('demo/kpi-scenarios.php');
        $metrics = array_values(array_map(
            'strval',
            is_array($kpiScenario['kpi_metrics'] ?? null) ? $kpiScenario['kpi_metrics'] : ['revenue']
        ));
        $metricRanges = is_array($kpiScenario['metric_ranges'] ?? null)
            ? $kpiScenario['metric_ranges']
            : [];
        $kpiDays = max(1, $context->targets['kpi_days']);

        $rows = [];
        $timestamp = now()->utc();

        for ($dayOffset = 0; $dayOffset < $kpiDays; $dayOffset++) {
            $date = $context->referenceDate->copy()->subDays($dayOffset)->toDateString();

            foreach ($metrics as $metricKey) {
                $range = is_array($metricRanges[$metricKey] ?? null)
                    ? $metricRanges[$metricKey]
                    : ['min' => 1, 'max' => 100];

                $rows[] = [
                    'kpi_date' => $date,
                    'location_key' => 'portfolio',
                    'metric_key' => $metricKey,
                    'value_decimal' => $this->randomMoney(
                        $context,
                        (float) ($range['min'] ?? 1),
                        (float) ($range['max'] ?? 100)
                    ),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        foreach (array_chunk($rows, 1000) as $batch) {
            DB::connection($context->connectionName)
                ->table('tenant_kpi_daily')
                ->upsert(
                    $batch,
                    ['kpi_date', 'location_key', 'metric_key'],
                    ['value_decimal', 'updated_at']
                );
        }
    }

    public function seedVertical(DemoTenantSeedContextData $context): void
    {
        DB::connection($context->connectionName)
            ->table('tenant_settings')
            ->upsert(
                [[
                    'setting_key' => 'tenant.vertical.profile',
                    'setting_value' => $context->scenario->vertical,
                    'created_at' => now()->utc(),
                    'updated_at' => now()->utc(),
                ]],
                ['setting_key'],
                ['setting_value', 'updated_at']
            );
    }

    private function deterministicPublicId(DemoTenantSeedContextData $context, string $seed): string
    {
        return substr(strtoupper(hash('sha256', $context->tenantPublicId.$seed.$context->datasetVersion)), 0, 26);
    }

    private function scopeForRole(string $roleKey): string
    {
        return match ($roleKey) {
            'regional-admin', 'marketing-manager' => 'region',
            'location-manager', 'dispatcher', 'technician-lead' => 'location',
            'enterprise-admin', 'finance-lead', 'operations-vp' => 'enterprise',
            default => 'tenant',
        };
    }

    private function humanizePermissionKey(string $permissionKey): string
    {
        $normalized = str_replace(['.', '_'], ' ', $permissionKey);

        return ucwords($normalized);
    }

    private function regionForIndex(int $index, string $tenantKey): string
    {
        $regionsByTenant = [
            'small' => ['coastal', 'metro'],
            'regional' => ['north', 'south', 'triangle', 'coastal'],
            'enterprise' => ['east', 'central', 'west', 'southwest', 'northwest'],
        ];

        $regions = $regionsByTenant[$tenantKey] ?? ['national'];

        return $regions[($index - 1) % count($regions)];
    }

    private function randomMoney(DemoTenantSeedContextData $context, float $min, float $max): float
    {
        if ($max < $min) {
            return round($min, 2);
        }

        $minCents = (int) round($min * 100);
        $maxCents = (int) round($max * 100);
        $amountCents = $context->randomizer->int($minCents, $maxCents);

        return round($amountCents / 100, 2);
    }
}
