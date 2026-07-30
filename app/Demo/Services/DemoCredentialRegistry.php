<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Data\DemoTenantScenarioData;
use App\Models\Central\Tenant;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class DemoCredentialRegistry
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    public function credentials(string $tenantOption, ?string $role = null): Collection
    {
        $configuration = DemoConfigurationData::fromConfig();
        $normalizedRole = is_string($role) && trim($role) !== '' ? strtolower(trim($role)) : null;

        return $this->tenantRegistry
            ->resolveSelection($tenantOption)
            ->flatMap(function (DemoTenantScenarioData $tenant) use ($configuration): array {
                $limitationByRole = $this->buildRoleLimitationMap($tenant);
                $mfaByRole = $this->buildRoleMfaMap($tenant);

                $tenantRecord = Tenant::query()
                    ->where('slug', $tenant->slug)
                    ->with(['tenantDatabases' => function ($query): void {
                        $query->where('is_current', '=', true)->with('cluster');
                    }])
                    ->first();

                if ($tenantRecord === null || $tenantRecord->tenantDatabases->isEmpty()) {
                    return $this->fallbackScenarioCredentialRows($tenant, $configuration, $limitationByRole, $mfaByRole);
                }

                $tenantDatabase = $tenantRecord->tenantDatabases->first();
                if ($tenantDatabase === null || $tenantDatabase->cluster === null) {
                    return $this->fallbackScenarioCredentialRows($tenant, $configuration, $limitationByRole, $mfaByRole);
                }

                $connectionName = null;

                try {
                    $connectionName = $this->tenantDatabaseManager->connectToDatabase(
                        databaseName: (string) $tenantDatabase->database_name,
                        secretReference: (string) $tenantDatabase->secret_reference,
                        cluster: $tenantDatabase->cluster,
                        createIfMissing: false
                    );

                    $rows = DB::connection($connectionName)
                        ->table('tenant_users')
                        ->select(['name', 'email', 'role_key', 'scope', 'mfa_enabled'])
                        ->orderBy('email')
                        ->get();

                    return $rows
                        ->map(function (object $row) use ($tenant, $configuration, $limitationByRole, $mfaByRole): array {
                            $role = (string) $row->role_key;

                            return [
                                'tenant' => $tenant->key,
                                'domain' => $tenant->domain,
                                'login_url' => sprintf('https://%s/login', $tenant->domain),
                                'name' => (string) $row->name,
                                'email' => (string) $row->email,
                                'role' => $role,
                                'scope' => (string) $row->scope,
                                'password' => $configuration->password,
                                'mfa_status' => ((bool) $row->mfa_enabled)
                                    ? ($mfaByRole[$role] ?? 'required')
                                    : 'disabled',
                                'access_limitations' => $limitationByRole[$role]
                                    ?? 'Demo data only; no production-grade integration access.',
                            ];
                        })
                        ->all();
                } catch (Throwable) {
                    return $this->fallbackScenarioCredentialRows($tenant, $configuration, $limitationByRole, $mfaByRole);
                } finally {
                    if (is_string($connectionName)) {
                        $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                    }
                }
            })
            ->filter(static function (array $entry) use ($normalizedRole): bool {
                if ($normalizedRole === null) {
                    return true;
                }

                return strtolower((string) ($entry['role'] ?? '')) === $normalizedRole;
            })
            ->values();
    }

    /**
     * @param  array<string, string>  $limitationByRole
     * @param  array<string, string>  $mfaByRole
     * @return list<array<string, string>>
     */
    private function fallbackScenarioCredentialRows(
        DemoTenantScenarioData $tenant,
        DemoConfigurationData $configuration,
        array $limitationByRole,
        array $mfaByRole,
    ): array {
        return array_map(static function (array $entry) use ($tenant, $configuration, $limitationByRole, $mfaByRole): array {
            $role = (string) ($entry['role'] ?? 'user');

            return [
                'tenant' => $tenant->key,
                'domain' => $tenant->domain,
                'login_url' => sprintf('https://%s/login', $tenant->domain),
                'name' => (string) ($entry['name'] ?? ''),
                'email' => (string) ($entry['email'] ?? ''),
                'role' => $role,
                'scope' => (string) ($entry['scope'] ?? 'tenant'),
                'password' => $configuration->password,
                'mfa_status' => $mfaByRole[$role] ?? (string) ($entry['mfa_status'] ?? 'disabled'),
                'access_limitations' => $limitationByRole[$role]
                    ?? 'Demo data only; no production-grade integration access.',
            ];
        }, $tenant->users);
    }

    /**
     * @return array<string, string>
     */
    private function buildRoleLimitationMap(DemoTenantScenarioData $tenant): array
    {
        $map = [];
        foreach ($tenant->users as $entry) {
            $role = trim((string) ($entry['role'] ?? ''));
            $limitations = trim((string) ($entry['access_limitations'] ?? ''));

            if ($role !== '' && $limitations !== '') {
                $map[$role] = $limitations;
            }
        }

        return $map;
    }

    /**
     * @return array<string, string>
     */
    private function buildRoleMfaMap(DemoTenantScenarioData $tenant): array
    {
        $map = [];
        foreach ($tenant->users as $entry) {
            $role = trim((string) ($entry['role'] ?? ''));
            $mfa = trim((string) ($entry['mfa_status'] ?? ''));

            if ($role !== '' && $mfa !== '') {
                $map[$role] = $mfa;
            }
        }

        return $map;
    }
}
