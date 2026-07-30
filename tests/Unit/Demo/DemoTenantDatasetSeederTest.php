<?php

declare(strict_types=1);

namespace Tests\Unit\Demo;

use App\Demo\Services\DemoDatasetManager;
use Database\Seeders\Central\DemoTenantDatasetSeeder;
use Mockery;
use Tests\TestCase;

class DemoTenantDatasetSeederTest extends TestCase
{
    public function test_it_skips_auto_dataset_seed_when_demo_seeding_is_disabled(): void
    {
        config()->set('demo.enabled', false);
        config()->set('demo.auto_seed_on_database_seeder', true);

        $datasetManager = Mockery::mock(DemoDatasetManager::class);
        $datasetManager->shouldNotReceive('seed');
        $this->app->instance(DemoDatasetManager::class, $datasetManager);

        app(DemoTenantDatasetSeeder::class)->run();
    }

    public function test_it_skips_auto_dataset_seed_when_auto_seed_flag_is_disabled(): void
    {
        config()->set('demo.enabled', true);
        config()->set('demo.auto_seed_on_database_seeder', false);

        $datasetManager = Mockery::mock(DemoDatasetManager::class);
        $datasetManager->shouldNotReceive('seed');
        $this->app->instance(DemoDatasetManager::class, $datasetManager);

        app(DemoTenantDatasetSeeder::class)->run();
    }

    public function test_it_runs_demo_dataset_seed_during_database_seeder_when_enabled(): void
    {
        config()->set('demo.enabled', true);
        config()->set('demo.auto_seed_on_database_seeder', true);

        $datasetManager = Mockery::mock(DemoDatasetManager::class);
        $datasetManager->shouldReceive('seed')
            ->once()
            ->with('all', null, null, true, [], true)
            ->andReturn([
                'dataset_version' => 'v1',
                'profile' => 'standard',
                'reference_date' => '2026-01-01',
                'tenant_option' => 'all',
                'seeded_tenants' => 3,
                'seeded_users' => 9,
                'tenant_databases' => [],
                'skip_flags' => [],
            ]);
        $this->app->instance(DemoDatasetManager::class, $datasetManager);

        app(DemoTenantDatasetSeeder::class)->run();
    }
}
