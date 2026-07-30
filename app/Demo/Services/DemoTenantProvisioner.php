<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Data\DemoTenantScenarioData;
use App\Demo\Data\DemoTenantSeedContextData;
use App\Demo\Enums\DemoDataProfile;
use App\Demo\Exceptions\DemoSeedingNotAllowedException;
use App\Demo\Support\DemoRandomizer;
use App\Demo\Support\TenantDatabaseName;
use App\Models\Central\CentralSetting;
use App\Models\Central\DatabaseCluster;
use App\Models\Central\Tenant;
use App\Models\Central\TenantDatabase;
use App\Models\Central\TenantDomain;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DemoTenantProvisioner
{
    public function __construct(
        private readonly DemoCentralCatalogSeeder $centralCatalogSeeder,
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
        private readonly DemoTenantSchemaManager $tenantSchemaManager,
        private readonly DemoTenantSeederRunner $tenantSeederRunner,
    ) {
    }

    /**
     * @param  Collection<int, DemoTenantScenarioData>  $tenants
     * @param  array<string, bool>  $skipFlags
     * @return array{
     *   seeded_tenants:int,
     *   seeded_users:int,
     *   tenant_database_names:list<string>
     * }
     */
    public function provision(
        Collection $tenants,
        DemoDataProfile $profile,
        CarbonImmutable $referenceDate,
        bool $seedPlatformUsers,
        array $skipFlags = [],
        bool $preserveIdentity = false,
    ): array {
        $configuration = DemoConfigurationData::fromConfig();
        $effectiveSeedPlatformUsers = $seedPlatformUsers && ! ($skipFlags['skip-platform'] ?? false);

        $this->centralCatalogSeeder->seed(seedPlatformUsers: $effectiveSeedPlatformUsers);
        $targetAllocations = $this->allocateTargets($profile, $tenants);
        $tenantDatabaseNames = [];
        $seededUsers = 0;

        foreach ($tenants as $tenantScenario) {
            $result = $this->provisionTenant(
                tenantScenario: $tenantScenario,
                profile: $profile,
                configuration: $configuration,
                referenceDate: $referenceDate,
                targets: $targetAllocations[$tenantScenario->key] ?? $this->fallbackTargets($profile),
                skipFlags: $skipFlags,
                preserveIdentity: $preserveIdentity,
            );

            $tenantDatabaseNames[] = $result['database_name'];
            $seededUsers += $result['seeded_users'];
        }

        $this->recordDatasetSettings($profile, $referenceDate, $configuration);

        return [
            'seeded_tenants' => $tenants->count(),
            'seeded_users' => $seededUsers,
            'tenant_database_names' => $tenantDatabaseNames,
        ];
    }

    /**
     * @param  array{
     *   locations:int,
     *   customers:int,
     *   leads:int,
     *   invoices:int,
     *   payments:int,
     *   expenses:int,
     *   kpi_days:int,
     *   users:int
     * }  $targets
     * @param  array<string, bool>  $skipFlags
     * @return array{database_name:string,seeded_users:int}
     */
    private function provisionTenant(
        DemoTenantScenarioData $tenantScenario,
        DemoDataProfile $profile,
        DemoConfigurationData $configuration,
        CarbonImmutable $referenceDate,
        array $targets,
        array $skipFlags,
        bool $preserveIdentity,
    ): array {
        $tenant = Tenant::query()->where('slug', $tenantScenario->slug)->first();

        if ($tenant !== null && ! $tenant->is_demo) {
            throw new DemoSeedingNotAllowedException(sprintf(
                'Tenant "%s" is a real tenant and cannot be modified by demo commands.',
                $tenantScenario->slug
            ));
        }

        if ($tenant === null) {
            $tenant = new Tenant();
            $tenant->slug = $tenantScenario->slug;
        }

        $this->assertDomainNotOwnedByRealTenant($tenantScenario->domain, $tenant);

        $tenant->display_name = $tenantScenario->displayName;
        $tenant->status = 'active';
        $tenant->locale = $tenantScenario->locale;
        $tenant->timezone = $tenantScenario->timezone;
        $tenant->enabled_modules = ['crm', 'finance', 'content', 'analytics'];
        $tenant->enabled_capabilities = ['support-access', 'reporting', 'role-management', 'multi-location'];
        $tenant->provisioning_state = 'ready';
        $tenant->migration_state = 'current';
        $tenant->health_state = 'healthy';
        $tenant->is_demo = true;
        $tenant->demo_dataset_version = $configuration->datasetVersion;
        $tenant->demo_seeded_at = now()->utc();
        $tenant->save();

        if ($tenant->public_id !== $tenantScenario->publicId) {
            $tenant->public_id = $tenantScenario->publicId;
            $tenant->save();
        }

        $validatedDatabaseName = TenantDatabaseName::from($tenantScenario->databaseName)->value;

        TenantDomain::query()->updateOrCreate(
            ['domain' => $tenantScenario->domain],
            [
                'tenant_id' => $tenant->id,
                'is_primary' => true,
                'is_active' => true,
                'verified_at' => now()->utc(),
            ]
        );

        $cluster = DatabaseCluster::query()->updateOrCreate(
            ['name' => 'demo-local-cluster'],
            [
                'host' => (string) config(
                    'tenancy.secret_provider.local_cluster_defaults.host',
                    config('database.connections.central.host', 'mysql')
                ),
                'port' => (int) config('tenancy.secret_provider.local_cluster_defaults.port', 3306),
                'ssl_mode' => (string) config('tenancy.secret_provider.local_cluster_defaults.ssl_mode', 'preferred'),
                'is_active' => true,
            ]
        );

        TenantDatabase::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'is_current' => true],
            [
                'database_cluster_id' => $cluster->id,
                'database_name' => $validatedDatabaseName,
                'status' => 'active',
                'secret_reference' => sprintf('demo://tenant/%s/db', $tenantScenario->slug),
                'schema_version' => 'demo-'.$configuration->datasetVersion,
                'provisioning_state' => 'ready',
                'migration_state' => 'current',
                'health_state' => 'healthy',
            ]
        );

        $tenantDatabase = TenantDatabase::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_current', true)
            ->with('cluster')
            ->first();

        if ($tenantDatabase === null || $tenantDatabase->cluster === null) {
            throw new RuntimeException(sprintf(
                'Current tenant database is unavailable for tenant "%s".',
                $tenantScenario->slug
            ));
        }

        $connectionName = $this->tenantDatabaseManager->ensureDatabase(
            databaseName: $validatedDatabaseName,
            secretReference: (string) $tenantDatabase->secret_reference,
            cluster: $tenantDatabase->cluster,
        );

        $this->tenantSchemaManager->ensureSchema($connectionName);
        $this->tenantSchemaManager->resetData(
            connectionName: $connectionName,
            preserveIdentity: $preserveIdentity
        );

        $context = new DemoTenantSeedContextData(
            connectionName: $connectionName,
            tenantPublicId: $tenantScenario->publicId,
            tenantSlug: $tenantScenario->slug,
            tenantDisplayName: $tenantScenario->displayName,
            tenantDomain: $tenantScenario->domain,
            datasetVersion: $configuration->datasetVersion,
            profile: $profile,
            referenceDate: $referenceDate,
            scenario: $tenantScenario,
            targets: $targets,
            skipFlags: array_merge($skipFlags, ['preserve-identity' => $preserveIdentity]),
            randomizer: new DemoRandomizer(
                $configuration->datasetVersion,
                $tenantScenario->publicId,
                $profile
            ),
            password: $configuration->password,
        );

        $this->tenantSeederRunner->run($context);

        $seededUsers = (int) DB::connection($connectionName)->table('tenant_users')->count();
        $this->tenantDatabaseManager->purgeTenantConnection($validatedDatabaseName);

        return [
            'database_name' => $validatedDatabaseName,
            'seeded_users' => $seededUsers,
        ];
    }

    private function recordDatasetSettings(
        DemoDataProfile $profile,
        CarbonImmutable $referenceDate,
        DemoConfigurationData $configuration,
    ): void {
        $seededAt = now()->utc()->toIso8601String();

        $records = [
            'demo.dataset.version' => $configuration->datasetVersion,
            'demo.dataset.reference_date' => $referenceDate->toDateString(),
            'demo.dataset.seeded_at' => $seededAt,
            'demo.dataset.profile' => $profile->value,
        ];

        foreach ($records as $key => $value) {
            CentralSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    private function assertDomainNotOwnedByRealTenant(string $domain, Tenant $candidateTenant): void
    {
        $existing = TenantDomain::query()
            ->where('domain', $domain)
            ->with('tenant')
            ->first();

        if ($existing === null || $existing->tenant === null) {
            return;
        }

        if ($existing->tenant_id === $candidateTenant->id) {
            return;
        }

        $tenantType = $existing->tenant->is_demo ? 'another demo tenant' : 'a real tenant';

        throw new DemoSeedingNotAllowedException(sprintf(
            'Domain "%s" is already assigned to %s (%s).',
            $domain,
            $tenantType,
            $existing->tenant->slug
        ));
    }

    /**
     * @param  Collection<int, DemoTenantScenarioData>  $tenants
     * @return array<string, array{
     *   locations:int,
     *   customers:int,
     *   leads:int,
     *   invoices:int,
     *   payments:int,
     *   expenses:int,
     *   kpi_days:int,
     *   users:int
     * }>
     */
    private function allocateTargets(DemoDataProfile $profile, Collection $tenants): array
    {
        $profileTargets = $profile->targets();
        $weights = $tenants
            ->mapWithKeys(static fn (DemoTenantScenarioData $scenario): array => [$scenario->key => $scenario->weight])
            ->all();

        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            $totalWeight = count($weights);
        }

        $metrics = ['users', 'locations', 'customers', 'leads', 'invoices', 'payments', 'expenses'];
        $allocations = [];
        foreach ($weights as $key => $weight) {
            $allocations[$key] = [
                'users' => 0,
                'locations' => 0,
                'customers' => 0,
                'leads' => 0,
                'invoices' => 0,
                'payments' => 0,
                'expenses' => 0,
                'kpi_days' => $profileTargets['kpi_days'],
            ];
        }

        foreach ($metrics as $metric) {
            $totalTarget = (int) ($profileTargets[$metric] ?? 0);
            $assigned = 0;
            $remainders = [];

            foreach ($weights as $key => $weight) {
                $raw = ($totalTarget * $weight) / $totalWeight;
                $base = (int) floor($raw);
                $allocations[$key][$metric] = $base;
                $assigned += $base;
                $remainders[$key] = $raw - $base;
            }

            $remaining = $totalTarget - $assigned;
            while ($remaining > 0) {
                arsort($remainders);
                foreach (array_keys($remainders) as $key) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $allocations[$key][$metric]++;
                    $remaining--;
                }
            }
        }

        foreach ($tenants as $scenario) {
            $allocations[$scenario->key]['users'] = max(
                count($scenario->users),
                $allocations[$scenario->key]['users']
            );
        }

        return $allocations;
    }

    /**
     * @return array{
     *   locations:int,
     *   customers:int,
     *   leads:int,
     *   invoices:int,
     *   payments:int,
     *   expenses:int,
     *   kpi_days:int,
     *   users:int
     * }
     */
    private function fallbackTargets(DemoDataProfile $profile): array
    {
        $targets = $profile->targets();

        return [
            'locations' => max(1, (int) ceil($targets['locations'] / 3)),
            'customers' => max(1, (int) ceil($targets['customers'] / 3)),
            'leads' => max(1, (int) ceil($targets['leads'] / 3)),
            'invoices' => max(1, (int) ceil($targets['invoices'] / 3)),
            'payments' => max(0, (int) ceil($targets['payments'] / 3)),
            'expenses' => max(1, (int) ceil($targets['expenses'] / 3)),
            'kpi_days' => max(1, (int) $targets['kpi_days']),
            'users' => max(1, (int) ceil($targets['users'] / 3)),
        ];
    }
}
