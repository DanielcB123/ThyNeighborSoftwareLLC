<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Enums\DemoDataProfile;
use App\Demo\Exceptions\DemoSeedingNotAllowedException;
use App\Demo\Support\DemoReferenceClock;
use App\Demo\Support\TenantDatabaseName;
use App\Models\Central\DemoAuditEvent;
use App\Models\Central\CentralSetting;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class DemoDatasetManager
{
    public function __construct(
        private readonly DemoEnvironmentGuard $environmentGuard,
        private readonly DemoTenantRegistry $tenantRegistry,
        private readonly DemoTenantProvisioner $tenantProvisioner,
        private readonly DemoReferenceClock $referenceClock,
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
    ) {
    }

    /**
     * @param  array<string, bool>  $skipFlags
     * @return array<string, mixed>
     */
    public function seed(
        string $tenantOption,
        ?string $referenceDate,
        ?DemoDataProfile $profileOverride,
        bool $seedPlatform,
        array $skipFlags = [],
        bool $explicitCommand = false,
    ): array {
        $this->environmentGuard->assertMutationAllowed('demo:seed', $explicitCommand);

        $profile = $profileOverride ?? \App\Demo\Data\DemoConfigurationData::fromConfig()->profile;

        if ($profile === DemoDataProfile::Large && (bool) env('CI', false)) {
            throw new DemoSeedingNotAllowedException(
                'The large demo profile is blocked in CI environments.'
            );
        }

        $tenants = $this->tenantRegistry->resolveSelection($tenantOption);
        $effectiveReferenceDate = $this->referenceClock->resolveReferenceDate($referenceDate);

        $summary = $this->tenantProvisioner->provision(
            tenants: $tenants,
            profile: $profile,
            referenceDate: $effectiveReferenceDate,
            seedPlatformUsers: $seedPlatform,
            skipFlags: $skipFlags,
            preserveIdentity: false,
        );

        $this->writeImplementationStatus(
            profile: $profile,
            referenceDate: $effectiveReferenceDate->toDateString(),
            tenantOption: $tenantOption,
            skipFlags: $skipFlags,
            tenantDatabases: $summary['tenant_database_names']
        );

        $this->recordAuditEvent('demo.seed', $tenantOption, [
            'profile' => $profile->value,
            'reference_date' => $effectiveReferenceDate->toDateString(),
            'tenant_databases' => $summary['tenant_database_names'],
            'skip_flags' => array_keys(array_filter($skipFlags, static fn (bool $value): bool => $value)),
        ]);

        return [
            'dataset_version' => DemoConfigurationData::fromConfig()->datasetVersion,
            'profile' => $profile->value,
            'reference_date' => $effectiveReferenceDate->toDateString(),
            'tenant_option' => $tenantOption,
            'seeded_tenants' => $summary['seeded_tenants'],
            'seeded_users' => $summary['seeded_users'],
            'tenant_databases' => $summary['tenant_database_names'],
            'skip_flags' => array_keys(array_filter($skipFlags, static fn (bool $value): bool => $value)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reset(string $tenantOption, bool $keepDatabases, bool $explicitCommand = false): array
    {
        $this->environmentGuard->assertMutationAllowed('demo:reset', $explicitCommand);

        $selected = $this->tenantRegistry->resolveSelection($tenantOption);
        $deletedTenants = 0;
        $droppedDatabases = 0;

        foreach ($selected as $tenantScenario) {
            $tenant = Tenant::query()->where('slug', $tenantScenario->slug)->first();

            if ($tenant === null) {
                continue;
            }

            if (! $tenant->is_demo) {
                throw new DemoSeedingNotAllowedException(sprintf(
                    'Refusing to reset real tenant "%s".',
                    $tenantScenario->slug
                ));
            }

            $tenantDatabases = $tenant->tenantDatabases()->get();
            foreach ($tenantDatabases as $tenantDatabase) {
                $databaseName = TenantDatabaseName::from((string) $tenantDatabase->database_name)->value;

                if (! $keepDatabases) {
                    $tenantDatabase->loadMissing('cluster');
                    if ($tenantDatabase->cluster !== null) {
                        $this->tenantDatabaseManager->dropDatabase(
                            databaseName: $databaseName,
                            secretReference: (string) $tenantDatabase->secret_reference,
                            cluster: $tenantDatabase->cluster
                        );
                    }
                    $droppedDatabases++;
                }

                $this->tenantDatabaseManager->purgeTenantConnection($databaseName);
            }

            foreach ($tenant->domains as $domain) {
                $this->forgetResolutionCacheForDomain((string) $domain->domain);
            }

            $tenant->forceDelete();
            $deletedTenants++;
        }

        $this->recordResetAuditEvent($tenantOption, $keepDatabases);

        DB::purge((string) config('tenancy.tenant_connection_name', 'tenant'));

        return [
            'tenant_option' => $tenantOption,
            'deleted_tenants' => $deletedTenants,
            'dropped_databases' => $droppedDatabases,
            'kept_databases' => $keepDatabases,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshOperations(
        string $tenantOption,
        ?DemoDataProfile $profileOverride,
        ?string $referenceDate,
        bool $explicitCommand = false,
    ): array {
        $this->environmentGuard->assertMutationAllowed('demo:refresh-operations', $explicitCommand);

        $profile = $profileOverride ?? \App\Demo\Data\DemoConfigurationData::fromConfig()->profile;
        $resolvedDate = $this->referenceClock->resolveReferenceDate($referenceDate);
        $selected = $this->tenantRegistry->resolveSelection($tenantOption);

        foreach ($selected as $tenantScenario) {
            $tenant = Tenant::query()->where('slug', $tenantScenario->slug)->first();

            if ($tenant === null || ! $tenant->is_demo) {
                throw new DemoSeedingNotAllowedException(sprintf(
                    'Refresh operations can run only for existing demo tenants. Failed at "%s".',
                    $tenantScenario->slug
                ));
            }
        }

        $summary = $this->tenantProvisioner->provision(
            tenants: $selected,
            profile: $profile,
            referenceDate: $resolvedDate,
            seedPlatformUsers: false,
            skipFlags: [
                'skip-platform' => true,
            ],
            preserveIdentity: true
        );

        $this->writeImplementationStatus(
            profile: $profile,
            referenceDate: $resolvedDate->toDateString(),
            tenantOption: $tenantOption,
            skipFlags: ['skip-platform' => true],
            tenantDatabases: $summary['tenant_database_names']
        );

        $this->recordAuditEvent('demo.refresh-operations', $tenantOption, [
            'profile' => $profile->value,
            'reference_date' => $resolvedDate->toDateString(),
            'tenant_databases' => $summary['tenant_database_names'],
        ]);

        return [
            'dataset_version' => DemoConfigurationData::fromConfig()->datasetVersion,
            'profile' => $profile->value,
            'reference_date' => $resolvedDate->toDateString(),
            'tenant_option' => $tenantOption,
            'refreshed' => true,
            'seeded_tenants' => $summary['seeded_tenants'],
            'seeded_users' => $summary['seeded_users'],
            'tenant_databases' => $summary['tenant_database_names'],
        ];
    }

    /**
     * @param  array<string, bool>  $skipFlags
     * @param  list<string>  $tenantDatabases
     */
    private function writeImplementationStatus(
        DemoDataProfile $profile,
        string $referenceDate,
        string $tenantOption,
        array $skipFlags,
        array $tenantDatabases,
    ): void {
        $targets = $profile->targets();
        $enabledSkipFlags = array_keys(array_filter($skipFlags, static fn (bool $value): bool => $value));
        $datasetVersion = DemoConfigurationData::fromConfig()->datasetVersion;

        $content = implode(PHP_EOL, [
            '# Implementation Status',
            '',
            '## Demo Dataset',
            '',
            sprintf('- Dataset version: `%s`', $datasetVersion),
            sprintf('- Dataset profile: `%s`', $profile->value),
            sprintf('- Reference date: `%s`', $referenceDate),
            sprintf('- Tenant scope: `%s`', $tenantOption),
            sprintf('- Seeded at (UTC): `%s`', now()->utc()->toIso8601String()),
            sprintf('- Tenant databases: %s', $tenantDatabases === [] ? '`none`' : '`'.implode('`, `', $tenantDatabases).'`'),
            sprintf('- Active skip flags: %s', $enabledSkipFlags === [] ? '`none`' : '`'.implode('`, `', $enabledSkipFlags).'`'),
            '',
            '## Profile Target Scale',
            '',
            sprintf('- Tenants: %d', $targets['tenants']),
            sprintf('- Users: %d', $targets['users']),
            sprintf('- Locations: %d', $targets['locations']),
            sprintf('- Customers: %d', $targets['customers']),
            sprintf('- Leads: %d', $targets['leads']),
            sprintf('- Invoices: %d', $targets['invoices']),
            sprintf('- Payments: %d', $targets['payments']),
            sprintf('- Expenses: %d', $targets['expenses']),
            sprintf('- KPI coverage (days): %d', $targets['kpi_days']),
            '',
        ]);

        $docsPath = base_path('docs/implementation-status.md');
        $directoryPath = dirname($docsPath);

        if (! is_dir($directoryPath)) {
            throw new InvalidArgumentException(sprintf('Directory does not exist: %s', $directoryPath));
        }

        file_put_contents($docsPath, $content);
    }

    private function forgetResolutionCacheForDomain(string $domain): void
    {
        $cacheStore = (string) config('tenancy.resolution_cache_store', 'redis');
        $cachePrefix = (string) config('tenancy.resolution_cache_prefix', 'tenant-domain-resolution:');

        try {
            Cache::store($cacheStore)->forget($cachePrefix.strtolower($domain));
        } catch (\Throwable) {
            // Cache invalidation failures should not block safe central cleanup.
        }
    }

    private function recordResetAuditEvent(string $tenantOption, bool $keepDatabases): void
    {
        $payload = [
            'tenant_option' => $tenantOption,
            'keep_databases' => $keepDatabases,
            'reset_at_utc' => now()->utc()->toIso8601String(),
        ];

        $this->recordAuditEvent('demo.reset', $tenantOption, $payload);

        CentralSetting::query()->updateOrCreate(
            ['key' => 'demo.audit.last_reset'],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES)]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function recordAuditEvent(string $eventType, string $tenantScope, array $payload): void
    {
        DemoAuditEvent::query()->create([
            'event_type' => $eventType,
            'tenant_scope' => $tenantScope,
            'payload' => $payload,
        ]);
    }
}
