<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Models\Central\Tenant;

final class DemoTenantIsolationVerifier
{
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
            }
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }
}
