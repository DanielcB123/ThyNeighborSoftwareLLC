<?php

declare(strict_types=1);

namespace App\Demo\Verification;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Enums\DemoDataProfile;
use App\Demo\Jobs\DemoQueueProbeJob;
use App\Demo\Services\DemoTenantDatabaseManager;
use App\Demo\Services\DemoTenantRegistry;
use App\Models\Central\Tenant;
use App\Tenancy\CentralTenantDatabaseResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class DemoDatasetVerifier
{
    public function __construct(
        private readonly DemoTenantRegistry $tenantRegistry,
        private readonly DemoTenantDatabaseManager $tenantDatabaseManager,
        private readonly CentralTenantDatabaseResolver $centralTenantDatabaseResolver,
        private readonly DemoFinancialVerifier $financialVerifier,
        private readonly DemoTenantIsolationVerifier $isolationVerifier,
    ) {
    }

    /**
     * @return array{
     *   ok:bool,
     *   checks:list<array{name:string,ok:bool,issues:list<string>}>
     * }
     */
    public function verify(): array
    {
        $checks = [
            $this->verifyCentralTenantRecordsAndDomains(),
            $this->verifyPhysicalDatabasesAndIdentityMetadata(),
            $this->verifySeededUsersAndAuthentication(),
            $this->verifyRolesAndPermissions(),
            $this->verifyOrganizationAndLocationCoverage(),
            $this->verifyCrmCoverage(),
            $this->isolationCheck(),
            $this->financeCheck(),
            $this->verifyKpiCoverage(),
            $this->verifyPublishedPagesAndPublicForms(),
            $this->verifyResolutionCacheBehavior(),
            $this->verifyQueueDispatchAbility(),
        ];

        $overallOk = ! collect($checks)->contains(static fn (array $check): bool => $check['ok'] === false);

        return [
            'ok' => $overallOk,
            'checks' => $checks,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyCentralTenantRecordsAndDomains(): array
    {
        $issues = [];

        foreach ($this->tenantRegistry->all() as $scenario) {
            $tenant = Tenant::query()->where('slug', $scenario->slug)->first();

            if ($tenant === null) {
                $issues[] = sprintf('Tenant "%s" is missing.', $scenario->slug);
                continue;
            }

            if (! $tenant->is_demo) {
                $issues[] = sprintf('Tenant "%s" is not marked as demo.', $scenario->slug);
            }

            $expectedDomain = $scenario->domain;
            $hasDomain = $tenant->domains()->where('domain', $expectedDomain)->exists();
            if (! $hasDomain) {
                $issues[] = sprintf('Tenant "%s" is missing domain mapping "%s".', $scenario->slug, $expectedDomain);
            }
        }

        return [
            'name' => 'central_demo_tenant_records_and_domains',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyPhysicalDatabasesAndIdentityMetadata(): array
    {
        $issues = [];

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
                $issues[] = sprintf('Tenant "%s" is missing current tenant database metadata.', $scenario->slug);
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

                $metadata = DB::connection($connectionName)
                    ->table('tenant_metadata')
                    ->first();

                if ($metadata === null) {
                    $issues[] = sprintf('Tenant "%s" metadata row is missing.', $scenario->slug);
                    continue;
                }

                if ((string) $metadata->tenant_public_id !== $scenario->publicId) {
                    $issues[] = sprintf(
                        'Tenant "%s" metadata public ID mismatch: expected %s got %s.',
                        $scenario->slug,
                        $scenario->publicId,
                        (string) $metadata->tenant_public_id
                    );
                }

                if ((string) $metadata->tenant_domain !== $scenario->domain) {
                    $issues[] = sprintf(
                        'Tenant "%s" metadata domain mismatch: expected %s got %s.',
                        $scenario->slug,
                        $scenario->domain,
                        (string) $metadata->tenant_domain
                    );
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" physical database verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null && isset($tenantDatabase->database_name)) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'physical_databases_and_tenant_identity',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifySeededUsersAndAuthentication(): array
    {
        $issues = [];
        $demoPassword = (string) config('demo.password', 'DemoPassword!2026');

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

                $userCount = (int) DB::connection($connectionName)->table('tenant_users')->count();
                if ($userCount < count($scenario->users)) {
                    $issues[] = sprintf(
                        'Tenant "%s" has too few users seeded (%d).',
                        $scenario->slug,
                        $userCount
                    );
                }

                foreach ($scenario->users as $expectedUser) {
                    $email = strtolower((string) ($expectedUser['email'] ?? ''));
                    if ($email === '') {
                        continue;
                    }

                    $row = DB::connection($connectionName)
                        ->table('tenant_users')
                        ->where('email', '=', $email)
                        ->first();

                    if ($row === null) {
                        $issues[] = sprintf('Tenant "%s" missing expected user %s.', $scenario->slug, $email);
                        continue;
                    }

                    if (! Hash::check($demoPassword, (string) $row->password)) {
                        $issues[] = sprintf(
                            'Tenant "%s" user %s password does not match configured demo password.',
                            $scenario->slug,
                            $email
                        );
                    }
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" user authentication verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'seeded_users_and_password_authentication',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyRolesAndPermissions(): array
    {
        $issues = [];

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

                $roleCount = (int) DB::connection($connectionName)->table('tenant_roles')->count();
                $permissionCount = (int) DB::connection($connectionName)->table('tenant_permissions')->count();
                $rolePermissionCount = (int) DB::connection($connectionName)->table('tenant_role_permissions')->count();

                if ($roleCount === 0 || $permissionCount === 0 || $rolePermissionCount === 0) {
                    $issues[] = sprintf(
                        'Tenant "%s" missing role/permission assignments (roles=%d permissions=%d role_permissions=%d).',
                        $scenario->slug,
                        $roleCount,
                        $permissionCount,
                        $rolePermissionCount
                    );
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" role/permission verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'roles_and_permission_assignments',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyOrganizationAndLocationCoverage(): array
    {
        $issues = [];

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

                $organizationCount = (int) DB::connection($connectionName)->table('tenant_organizations')->count();
                $locationCount = (int) DB::connection($connectionName)->table('tenant_locations')->count();

                if ($organizationCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no organizations.', $scenario->slug);
                }

                if ($locationCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no locations.', $scenario->slug);
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" organization/location verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'organization_hierarchy_and_locations',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyCrmCoverage(): array
    {
        $issues = [];

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

                $customerCount = (int) DB::connection($connectionName)->table('tenant_customers')->count();
                $leadCount = (int) DB::connection($connectionName)->table('tenant_leads')->count();

                if ($customerCount === 0 || $leadCount === 0) {
                    $issues[] = sprintf(
                        'Tenant "%s" CRM dataset is incomplete (customers=%d leads=%d).',
                        $scenario->slug,
                        $customerCount,
                        $leadCount
                    );
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" CRM verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'crm_dataset_counts',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyKpiCoverage(): array
    {
        $issues = [];
        $defaultExpectedDays = max(1, (int) DemoConfigurationData::fromConfig()->profile->targets()['kpi_days']);

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

                $metadata = DB::connection($connectionName)->table('tenant_metadata')->first();
                $profileValue = (string) ($metadata->dataset_profile ?? '');
                $profile = DemoDataProfile::tryFrom($profileValue);
                $expectedDays = $profile !== null
                    ? (int) $profile->targets()['kpi_days']
                    : $defaultExpectedDays;

                $distinctDays = (int) DB::connection($connectionName)
                    ->table('tenant_kpi_daily')
                    ->distinct('kpi_date')
                    ->count('kpi_date');

                if ($distinctDays < $expectedDays) {
                    $issues[] = sprintf(
                        'Tenant "%s" KPI day coverage too low (%d/%d).',
                        $scenario->slug,
                        $distinctDays,
                        $expectedDays
                    );
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" KPI verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'kpi_coverage',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyPublishedPagesAndPublicForms(): array
    {
        $issues = [];

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

                $publishedPageCount = (int) DB::connection($connectionName)
                    ->table('tenant_pages')
                    ->where('status', '=', 'published')
                    ->count();
                $publicFormCount = (int) DB::connection($connectionName)
                    ->table('tenant_forms')
                    ->where('is_public', '=', true)
                    ->count();

                if ($publishedPageCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no published pages.', $scenario->slug);
                }
                if ($publicFormCount === 0) {
                    $issues[] = sprintf('Tenant "%s" has no public forms.', $scenario->slug);
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Tenant "%s" content/form verification failed: %s',
                    $scenario->slug,
                    $exception->getMessage()
                );
            } finally {
                if ($connectionName !== null) {
                    $this->tenantDatabaseManager->purgeTenantConnection((string) $tenantDatabase->database_name);
                }
            }
        }

        return [
            'name' => 'published_pages_and_public_forms',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyResolutionCacheBehavior(): array
    {
        $issues = [];
        $cachePrefix = (string) config('tenancy.resolution_cache_prefix', 'tenant-domain-resolution:');
        $cacheStore = (string) config('tenancy.resolution_cache_store', 'redis');

        foreach ($this->tenantRegistry->all() as $scenario) {
            $resolved = $this->centralTenantDatabaseResolver->resolveByDomain($scenario->domain);
            if ($resolved === null) {
                $issues[] = sprintf('Domain resolution failed for "%s".', $scenario->domain);
                continue;
            }

            try {
                $cacheHit = Cache::store($cacheStore)->has($cachePrefix.$scenario->domain);
                if (! $cacheHit) {
                    $issues[] = sprintf(
                        'Resolution cache entry missing for "%s" in store "%s".',
                        $scenario->domain,
                        $cacheStore
                    );
                }
            } catch (\Throwable $exception) {
                $issues[] = sprintf(
                    'Resolution cache verification failed for "%s": %s',
                    $scenario->domain,
                    $exception->getMessage()
                );
            }
        }

        return [
            'name' => 'resolution_cache',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function verifyQueueDispatchAbility(): array
    {
        $issues = [];

        try {
            DemoQueueProbeJob::dispatchSync();
        } catch (\Throwable $exception) {
            $issues[] = sprintf('Queue dispatch failed: %s', $exception->getMessage());
        }

        return [
            'name' => 'queue_dispatch_ability',
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function isolationCheck(): array
    {
        $result = $this->isolationVerifier->verify();

        return [
            'name' => 'tenant_isolation',
            'ok' => $result['ok'],
            'issues' => $result['issues'],
        ];
    }

    /**
     * @return array{name:string,ok:bool,issues:list<string>}
     */
    private function financeCheck(): array
    {
        $result = $this->financialVerifier->verify();

        return [
            'name' => 'financial_consistency',
            'ok' => $result['ok'],
            'issues' => $result['issues'],
        ];
    }
}
