<?php

namespace Database\Seeders;

use Database\Seeders\Central\DemoTenantRegistrySeeder;
use Database\Seeders\Central\FeatureSeeder;
use Database\Seeders\Central\ModuleSeeder;
use Database\Seeders\Central\PlanSeeder;
use Database\Seeders\Central\PlatformPermissionSeeder;
use Database\Seeders\Central\PlatformRoleSeeder;
use Database\Seeders\Central\PlatformUserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlatformPermissionSeeder::class,
            PlatformRoleSeeder::class,
            FeatureSeeder::class,
            ModuleSeeder::class,
            PlanSeeder::class,
            PlatformUserSeeder::class,
            DemoTenantRegistrySeeder::class,
        ]);
    }
}
