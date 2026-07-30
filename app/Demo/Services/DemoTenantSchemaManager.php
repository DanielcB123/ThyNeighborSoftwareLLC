<?php

declare(strict_types=1);

namespace App\Demo\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DemoTenantSchemaManager
{
    public function ensureSchema(string $connectionName): void
    {
        $schema = Schema::connection($connectionName);

        if (! $schema->hasTable('tenant_metadata')) {
            $schema->create('tenant_metadata', function (Blueprint $table): void {
                $table->id();
                $table->char('tenant_public_id', 26)->unique('uq_tenant_metadata_public_id');
                $table->string('tenant_slug')->unique('uq_tenant_metadata_slug');
                $table->string('tenant_display_name');
                $table->string('tenant_domain')->unique('uq_tenant_metadata_domain');
                $table->string('dataset_version', 30);
                $table->string('dataset_profile', 32);
                $table->date('reference_date');
                $table->timestamp('seeded_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_permissions')) {
            $schema->create('tenant_permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('permission_key')->unique('uq_tenant_permissions_key');
                $table->string('display_name');
                $table->string('description')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_roles')) {
            $schema->create('tenant_roles', function (Blueprint $table): void {
                $table->id();
                $table->string('role_key')->unique('uq_tenant_roles_key');
                $table->string('display_name');
                $table->string('scope', 32);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_role_permissions')) {
            $schema->create('tenant_role_permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('role_key');
                $table->string('permission_key');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['role_key', 'permission_key'], 'uq_tenant_role_permissions_role_permission');
            });
        }

        if (! $schema->hasTable('tenant_users')) {
            $schema->create('tenant_users', function (Blueprint $table): void {
                $table->id();
                $table->char('user_public_id', 26)->unique('uq_tenant_users_public_id');
                $table->string('name');
                $table->string('email')->unique('uq_tenant_users_email');
                $table->string('password');
                $table->string('role_key', 64);
                $table->string('scope', 32)->default('tenant');
                $table->boolean('mfa_enabled')->default(false);
                $table->boolean('is_demo')->default(true);
                $table->string('status', 32)->default('active');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_settings')) {
            $schema->create('tenant_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('setting_key')->unique('uq_tenant_settings_key');
                $table->text('setting_value')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_organizations')) {
            $schema->create('tenant_organizations', function (Blueprint $table): void {
                $table->id();
                $table->string('organization_key')->unique('uq_tenant_organizations_key');
                $table->string('parent_organization_key')->nullable();
                $table->string('name');
                $table->string('scope_type', 32);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_locations')) {
            $schema->create('tenant_locations', function (Blueprint $table): void {
                $table->id();
                $table->string('location_key')->unique('uq_tenant_locations_key');
                $table->string('organization_key');
                $table->string('name');
                $table->string('region', 32)->default('national');
                $table->string('status', 32)->default('active');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['organization_key', 'status'], 'idx_tenant_locations_org_status');
            });
        }

        if (! $schema->hasTable('tenant_customers')) {
            $schema->create('tenant_customers', function (Blueprint $table): void {
                $table->id();
                $table->string('customer_key')->unique('uq_tenant_customers_key');
                $table->string('location_key');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('status', 32)->default('active');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['location_key', 'status'], 'idx_tenant_customers_location_status');
            });
        }

        if (! $schema->hasTable('tenant_leads')) {
            $schema->create('tenant_leads', function (Blueprint $table): void {
                $table->id();
                $table->string('lead_key')->unique('uq_tenant_leads_key');
                $table->string('customer_key')->nullable();
                $table->string('location_key');
                $table->string('status', 32)->default('new');
                $table->string('source', 64)->default('website');
                $table->decimal('estimated_value', 12, 2)->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['location_key', 'status'], 'idx_tenant_leads_location_status');
            });
        }

        if (! $schema->hasTable('tenant_invoices')) {
            $schema->create('tenant_invoices', function (Blueprint $table): void {
                $table->id();
                $table->string('invoice_key')->unique('uq_tenant_invoices_key');
                $table->string('customer_key');
                $table->string('location_key');
                $table->string('status', 32)->default('draft');
                $table->date('issued_at');
                $table->date('due_at');
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('balance_amount', 12, 2)->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['status', 'issued_at'], 'idx_tenant_invoices_status_issued');
            });
        }

        if (! $schema->hasTable('tenant_payments')) {
            $schema->create('tenant_payments', function (Blueprint $table): void {
                $table->id();
                $table->string('payment_key')->unique('uq_tenant_payments_key');
                $table->string('invoice_key');
                $table->date('paid_at');
                $table->decimal('amount', 12, 2);
                $table->string('method', 32)->default('ach');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['invoice_key', 'paid_at'], 'idx_tenant_payments_invoice_paid');
            });
        }

        if (! $schema->hasTable('tenant_expenses')) {
            $schema->create('tenant_expenses', function (Blueprint $table): void {
                $table->id();
                $table->string('expense_key')->unique('uq_tenant_expenses_key');
                $table->string('location_key');
                $table->string('category', 64);
                $table->date('incurred_at');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('vendor_name');
                $table->string('description')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['category', 'incurred_at'], 'idx_tenant_expenses_category_incurred');
            });
        }

        if (! $schema->hasTable('tenant_kpi_daily')) {
            $schema->create('tenant_kpi_daily', function (Blueprint $table): void {
                $table->id();
                $table->date('kpi_date');
                $table->string('location_key');
                $table->string('metric_key', 64);
                $table->decimal('value_decimal', 15, 4)->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['kpi_date', 'location_key', 'metric_key'], 'uq_tenant_kpi_daily_metric');
                $table->index(['metric_key', 'kpi_date'], 'idx_tenant_kpi_daily_metric_date');
            });
        }

        if (! $schema->hasTable('tenant_pages')) {
            $schema->create('tenant_pages', function (Blueprint $table): void {
                $table->id();
                $table->string('page_slug')->unique('uq_tenant_pages_slug');
                $table->string('title');
                $table->string('status', 32)->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_forms')) {
            $schema->create('tenant_forms', function (Blueprint $table): void {
                $table->id();
                $table->string('form_slug')->unique('uq_tenant_forms_slug');
                $table->string('name');
                $table->string('status', 32)->default('draft');
                $table->boolean('is_public')->default(false);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! $schema->hasTable('tenant_form_submissions')) {
            $schema->create('tenant_form_submissions', function (Blueprint $table): void {
                $table->id();
                $table->string('form_slug');
                $table->timestamp('submitted_at')->nullable();
                $table->string('contact_name');
                $table->string('contact_email')->nullable();
                $table->longText('payload_json')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['form_slug', 'submitted_at'], 'idx_tenant_form_submissions_form_submitted');
            });
        }
    }

    public function resetData(string $connectionName, bool $preserveIdentity): void
    {
        $tables = $preserveIdentity
            ? [
                'tenant_organizations',
                'tenant_locations',
                'tenant_customers',
                'tenant_leads',
                'tenant_invoices',
                'tenant_payments',
                'tenant_expenses',
                'tenant_kpi_daily',
                'tenant_pages',
                'tenant_forms',
                'tenant_form_submissions',
            ]
            : [
                'tenant_role_permissions',
                'tenant_roles',
                'tenant_permissions',
                'tenant_users',
                'tenant_settings',
                'tenant_organizations',
                'tenant_locations',
                'tenant_customers',
                'tenant_leads',
                'tenant_invoices',
                'tenant_payments',
                'tenant_expenses',
                'tenant_kpi_daily',
                'tenant_pages',
                'tenant_forms',
                'tenant_form_submissions',
            ];

        $databaseName = (string) DB::connection($connectionName)->getDatabaseName();
        DB::connection($connectionName)->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::connection($connectionName)->table($table)->delete();

            // Reset auto-increment deterministically to keep IDs compact for repeated seeds.
            DB::connection($connectionName)->statement(sprintf(
                'ALTER TABLE `%s`.`%s` AUTO_INCREMENT = 1',
                $databaseName,
                $table
            ));
        }

        DB::connection($connectionName)->statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
