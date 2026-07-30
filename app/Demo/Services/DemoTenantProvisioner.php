<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoConfigurationData;
use App\Demo\Enums\DemoDataProfile;
use App\Demo\Exceptions\DemoSeedingNotAllowedException;
use App\Demo\Support\DemoRandomizer;
use App\Models\Central\CentralSetting;
use App\Models\Central\DatabaseCluster;
use App\Models\Central\Tenant;
use App\Models\Central\TenantDatabase;
use App\Models\Central\TenantDomain;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class DemoTenantProvisioner
{
    /**
     * @param  Collection<int, array<string, mixed>>  $tenants
     * @return array{seeded_tenants:int,seeded_users:int}
     */
    public function provision(
        Collection $tenants,
        DemoDataProfile $profile,
        CarbonImmutable $referenceDate,
        bool $seedPlatformUsers,
    ): array {
        $configuration = DemoConfigurationData::fromConfig();

        DB::connection('central')->transaction(function () use ($tenants, $profile, $referenceDate, $configuration, $seedPlatformUsers): void {
            foreach ($tenants as $tenantScenario) {
                $this->provisionTenant($tenantScenario, $profile, $configuration);
            }

            if ($seedPlatformUsers) {
                $this->seedPlatformUsers($configuration);
            }

            $this->recordDatasetSettings($profile, $referenceDate, $configuration);
        });

        return [
            'seeded_tenants' => $tenants->count(),
            'seeded_users' => User::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $tenantScenario
     */
    private function provisionTenant(
        array $tenantScenario,
        DemoDataProfile $profile,
        DemoConfigurationData $configuration,
    ): void {
        $slug = (string) ($tenantScenario['slug'] ?? '');
        $domain = strtolower((string) ($tenantScenario['domain'] ?? ''));
        $databaseName = strtolower((string) ($tenantScenario['database_name'] ?? sprintf('wbyt_local_%s', $slug)));
        $publicId = (string) ($tenantScenario['public_id'] ?? '');

        if ($slug === '' || $domain === '') {
            throw new DemoSeedingNotAllowedException('Tenant scenario is missing a slug or domain.');
        }

        $tenant = Tenant::query()->where('slug', $slug)->first();

        if ($tenant !== null && ! $tenant->is_demo) {
            throw new DemoSeedingNotAllowedException(sprintf(
                'Tenant "%s" is a real tenant and cannot be converted to demo.',
                $slug
            ));
        }

        if ($tenant === null) {
            $tenant = new Tenant();
            $tenant->slug = $slug;
        }

        $tenant->display_name = (string) ($tenantScenario['display_name'] ?? $slug);
        $tenant->status = 'active';
        $tenant->locale = (string) ($tenantScenario['locale'] ?? 'en');
        $tenant->timezone = (string) ($tenantScenario['timezone'] ?? 'UTC');
        $tenant->enabled_modules = ['crm', 'finance', 'content', 'analytics'];
        $tenant->enabled_capabilities = ['support-access', 'reporting'];
        $tenant->provisioning_state = 'ready';
        $tenant->migration_state = 'current';
        $tenant->health_state = 'healthy';
        $tenant->is_demo = true;
        $tenant->demo_dataset_version = $configuration->datasetVersion;
        $tenant->demo_seeded_at = now()->utc();
        $tenant->save();

        if ($publicId !== '' && $tenant->public_id !== $publicId) {
            $tenant->public_id = $publicId;
            $tenant->save();
        }

        TenantDomain::query()->updateOrCreate(
            ['domain' => $domain],
            [
                'tenant_id' => $tenant->id,
                'is_primary' => true,
                'is_active' => true,
                'verified_at' => now()->utc(),
            ]
        );

        $cluster = DatabaseCluster::query()->firstOrCreate(
            ['name' => 'demo-local-cluster'],
            [
                'host' => (string) env('TENANT_DB_HOST', env('CENTRAL_DB_HOST', 'mysql')),
                'port' => (int) env('TENANT_DB_PORT', 3306),
                'ssl_mode' => (string) env('TENANT_DB_SSL_MODE', 'preferred'),
                'is_active' => true,
            ]
        );

        TenantDatabase::query()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'is_current' => true],
            [
                'database_cluster_id' => $cluster->id,
                'database_name' => $databaseName,
                'status' => 'active',
                'secret_reference' => sprintf('demo://tenant/%s/db', $slug),
                'schema_version' => 'demo-'.$configuration->datasetVersion,
                'provisioning_state' => 'ready',
                'migration_state' => 'current',
                'health_state' => 'healthy',
            ]
        );

        $users = is_array($tenantScenario['users'] ?? null) ? $tenantScenario['users'] : [];

        foreach ($users as $userScenario) {
            $email = strtolower((string) ($userScenario['email'] ?? ''));

            if ($email === '') {
                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => (string) ($userScenario['name'] ?? $email),
                    'password' => Hash::make($configuration->password),
                    'email_verified_at' => now()->utc(),
                ]
            );
        }

        $this->seedSyntheticUsersForTenant($tenant, $profile, $configuration);
    }

    private function seedPlatformUsers(DemoConfigurationData $configuration): void
    {
        foreach ([
            ['name' => 'Platform Owner', 'email' => 'platform.owner@webuildyouthrive.test'],
            ['name' => 'Platform Support', 'email' => 'platform.support@webuildyouthrive.test'],
            ['name' => 'Platform Billing', 'email' => 'platform.billing@webuildyouthrive.test'],
        ] as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($configuration->password),
                    'email_verified_at' => now()->utc(),
                ]
            );
        }
    }

    private function seedSyntheticUsersForTenant(
        Tenant $tenant,
        DemoDataProfile $profile,
        DemoConfigurationData $configuration,
    ): void {
        $targetUsersPerTenant = (int) ceil($profile->targets()['users'] / 3);
        $randomizer = new DemoRandomizer($configuration->datasetVersion, $tenant->public_id, $profile);

        for ($index = 1; $index <= $targetUsersPerTenant; $index++) {
            $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $localPart = sprintf('%s.user%s', $tenant->slug, $suffix);
            $domain = 'demo.webuildyouthrive.test';
            $email = strtolower(sprintf('%s@%s', $localPart, $domain));

            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => sprintf('%s Demo User %d', $tenant->display_name, $index),
                    'password' => Hash::make($configuration->password),
                    'email_verified_at' => now()->utc(),
                ]
            );

            // Deterministic touch point; keeps user generation reproducible by seed.
            $randomizer->bool(50);
        }
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
}
