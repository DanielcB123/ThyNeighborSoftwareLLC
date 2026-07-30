<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Demo\Services\DemoTenantDatabaseManager;
use App\Models\Central\Tenant;
use App\Models\Central\TenantDomain;

final class DemoTenantIsolationVerifier
{
    public function __construct(
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    /**
     * @return array{ok:bool,issues:list<string>}
     */
    public function verify(): array
    {
        $issues = [];

        $demoTenants = Tenant::query()
            ->where('is_demo', true)
            ->with('tenantDatabases')
            ->get();

        $databaseNames = [];

        foreach ($demoTenants as $tenant) {
            foreach ($tenant->tenantDatabases as $tenantDatabase) {
                $databaseName = (string) $tenantDatabase->database_name;

                if (isset($databaseNames[$databaseName])) {
                    $issues[] = sprintf(
                        'Duplicate tenant database detected for "%s" and "%s": %s',
                        $databaseNames[$databaseName],
                        $tenant->slug,
                        $databaseName
                    );
                } else {
                    $databaseNames[$databaseName] = (string) $tenant->slug;
                }

                $tenantDatabase->loadMissing('cluster');

                if ($tenantDatabase->cluster === null) {
                    $issues[] = sprintf(
                        'Tenant "%s" is missing an active database cluster association.',
                        $tenant->slug
                    );
                    continue;
                }

                try {
                    $this->tenantDatabaseManager->connectToDatabase(
                        databaseName: $databaseName,
                        secretReference: (string) $tenantDatabase->secret_reference,
                        cluster: $tenantDatabase->cluster,
                        createIfMissing: false
                    );
                } catch (\Throwable $exception) {
                    $issues[] = sprintf(
                        'Tenant "%s" database "%s" is unreachable: %s',
                        $tenant->slug,
                        $databaseName,
                        $exception->getMessage()
                    );
                } finally {
                    $this->tenantDatabaseManager->purgeTenantConnection($databaseName);
                }
            }
        }

        $duplicateDomains = TenantDomain::query()
            ->select('domain')
            ->groupBy('domain')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('domain')
            ->map('strval')
            ->all();

        foreach ($duplicateDomains as $domain) {
            $issues[] = sprintf('Duplicate domain mapping detected: %s', $domain);
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }
}
