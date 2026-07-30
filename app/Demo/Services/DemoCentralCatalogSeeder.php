<?php

declare(strict_types=1);

namespace App\Demo\Services;

use Database\Seeders\Central\DemoTenantRegistrySeeder;
use Database\Seeders\Central\FeatureSeeder;
use Database\Seeders\Central\ModuleSeeder;
use Database\Seeders\Central\PlanSeeder;
use Database\Seeders\Central\PlatformPermissionSeeder;
use Database\Seeders\Central\PlatformRoleSeeder;
use Database\Seeders\Central\PlatformUserSeeder;

final class DemoCentralCatalogSeeder
{
    public function seed(bool $seedPlatformUsers): void
    {
        app(PlatformPermissionSeeder::class)->run();
        app(PlatformRoleSeeder::class)->run();
        app(FeatureSeeder::class)->run();
        app(ModuleSeeder::class)->run();
        app(PlanSeeder::class)->run();

        if ($seedPlatformUsers) {
            app(PlatformUserSeeder::class)->run();
        }

        app(DemoTenantRegistrySeeder::class)->run();
    }
}
