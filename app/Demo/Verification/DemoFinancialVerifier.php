<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Demo\Services\DemoTenantDatabaseManager;
use App\Demo\Services\DemoTenantRegistry;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

final class DemoFinancialVerifier
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    /**
     * @return array{ok:bool,issues:list<string>}
     */
    public function verify(): array
    {
        $issues = [];

        $demoTenantCount = Tenant::query()->where('is_demo', true)->count();

        if ($demoTenantCount === 0) {
            $issues[] = 'No demo tenants are present, so finance verification cannot run.';

            return [
                'ok' => false,
                'issues' => $issues,
            ];
        }

        foreach ($this->tenantRegistry->all() as $scenario) {
            $tenant = Tenant::query()
                ->where('slug', $scenario->slug)
                ->with(['tenantDatabases' => function ($query): void {
                    $query->where('is_current', '=', true)->with('cluster');
                }])
                ->first();

            if ($tenant === null) {
                continue;
            }

            $tenantDatabase = $tenant->tenantDatabases->first();
            if ($tenantDatabase === null || $tenantDatabase->cluster === null) {
                $issues[] = sprintf('Tenant "%s" is missing current database metadata.', $tenant->slug);
                continue;
            }

            $connectionName = null;
            try {
                $connectionName = $this->tenantDatabaseManager->connectToDatabase(
                    databaseName: (string) $tenantDatabase->database_name,
                    secretReference: (string) $tenantDatabase->secret_reference,
                    cluster: $tenantDatabase->cluster,
                    createIfMissing: false
                );

                $invoiceCount = (int) DB::connection($connectionName)->table('tenant_invoices')->count();
                $paymentCount = (int) DB::connection($connectionName)->table('tenant_payments')->count();
                $expenseCount = (int) DB::connection($connectionName)->table('tenant_expenses')->count();

                if ($invoiceCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no invoices.', $tenant->slug);
                }
                if ($expenseCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no expenses.', $tenant->slug);
                }

                $invoiceTotal = (float) DB::connection($connectionName)->table('tenant_invoices')->sum('total_amount');
                $invoiceBalance = (float) DB::connection($connectionName)->table('tenant_invoices')->sum('balance_amount');
                $paymentTotal = (float) DB::connection($connectionName)->table('tenant_payments')->sum('amount');

                $computedBalance = round($invoiceTotal - $paymentTotal, 2);
                $persistedBalance = round($invoiceBalance, 2);

                if (abs($computedBalance - $persistedBalance) > 1.00) {
                    $issues[] = sprintf(
                        'Tenant "%s" finance mismatch: invoice_total - payments (%.2f) != persisted_balance (%.2f).',
                        $tenant->slug,
                        $computedBalance,
                        $persistedBalance
                    );
                }

                if ($paymentCount > 0 && $paymentTotal <= 0) {
                    $issues[] = sprintf('Tenant "%s" has non-positive payment totals.', $tenant->slug);
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" finance verification failed: %s',
                    $tenant->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }
}
