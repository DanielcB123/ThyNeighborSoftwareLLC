<?php

declare(strict_types=1);

namespace App\Demo\Services;

use App\Demo\Data\DemoTenantSeedContextData;
use Database\Seeders\Tenant\TenantAnalyticsSeeder;
use Database\Seeders\Tenant\TenantContentSeeder;
use Database\Seeders\Tenant\TenantCoreSeeder;
use Database\Seeders\Tenant\TenantCrmSeeder;
use Database\Seeders\Tenant\TenantFinanceSeeder;
use Database\Seeders\Tenant\TenantFormSeeder;
use Database\Seeders\Tenant\TenantOrganizationSeeder;
use Database\Seeders\Tenant\TenantPermissionSeeder;
use Database\Seeders\Tenant\TenantRoleSeeder;
use Database\Seeders\Tenant\TenantSettingsSeeder;
use Database\Seeders\Tenant\TenantUserSeeder;
use Database\Seeders\Tenant\TenantVerticalSeeder;

final class DemoTenantSeederRunner
{
    public function run(DemoTenantSeedContextData $context): void
    {
        app()->instance(DemoTenantSeedContextData::class, $context);

        try {
            $preserveIdentity = (bool) ($context->skipFlags['preserve-identity'] ?? false);

            app(TenantCoreSeeder::class)->run();
            if (! $preserveIdentity) {
                app(TenantPermissionSeeder::class)->run();
                app(TenantRoleSeeder::class)->run();
            }
            app(TenantOrganizationSeeder::class)->run();
            if (! $preserveIdentity) {
                app(TenantUserSeeder::class)->run();
            }
            app(TenantSettingsSeeder::class)->run();
            app(TenantContentSeeder::class)->run();
            app(TenantCrmSeeder::class)->run();
            app(TenantFormSeeder::class)->run();
            app(TenantFinanceSeeder::class)->run();
            app(TenantAnalyticsSeeder::class)->run();
            app(TenantVerticalSeeder::class)->run();
        } finally {
            app()->forgetInstance(DemoTenantSeedContextData::class);
        }
    }
}
