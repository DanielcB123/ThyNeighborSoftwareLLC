<?php

declare(strict_types=1);

namespace Tests\Unit\Tenancy;

use App\Tenancy\CentralTenantDatabaseResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CentralTenantDatabaseResolverTest extends TestCase
{
    public function test_it_returns_cached_resolution_when_available(): void
    {
        $cacheStore = Mockery::mock();

        Cache::shouldReceive('store')
            ->once()
            ->with('redis')
            ->andReturn($cacheStore);

        $cacheStore->shouldReceive('get')
            ->once()
            ->andReturn([
                'tenantPublicId' => '01JZZZB87RDKVG7FN6M7F9Y3QX',
                'tenantSlug' => 'cached-tenant',
                'tenantDisplayName' => 'Cached Tenant',
                'tenantStatus' => 'active',
                'tenantLocale' => 'en',
                'tenantTimezone' => 'UTC',
                'tenantEnabledModules' => ['crm'],
                'tenantEnabledCapabilities' => ['billing'],
                'domain' => 'cached-tenant.example.com',
                'databaseName' => 'wbyt_t_01jzzz',
                'databaseStatus' => 'active',
                'secretReference' => 'secret://tenant/cached',
                'clusterName' => 'cluster-a',
                'clusterHost' => 'mysql-tenant-a',
                'clusterPort' => 3306,
                'clusterSslMode' => 'preferred',
            ]);

        DB::shouldReceive('connection')->never();

        $resolver = new CentralTenantDatabaseResolver();
        $resolved = $resolver->resolveByDomain('CACHED-TENANT.EXAMPLE.COM');

        $this->assertNotNull($resolved);
        $this->assertSame('cached-tenant.example.com', $resolved->domain);
        $this->assertSame('wbyt_t_01jzzz', $resolved->databaseName);
    }

    public function test_it_falls_back_to_central_registry_when_cache_unavailable(): void
    {
        Cache::shouldReceive('store')
            ->andThrow(new RuntimeException('redis unavailable'));

        $queryBuilder = Mockery::mock();
        $connection = Mockery::mock();

        DB::shouldReceive('connection')
            ->once()
            ->with('central')
            ->andReturn($connection);

        $connection->shouldReceive('table')
            ->once()
            ->with('tenant_domains as td')
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('join')->andReturnSelf();
        $queryBuilder->shouldReceive('where')->andReturnSelf();
        $queryBuilder->shouldReceive('whereNull')->once()->andReturnSelf();
        $queryBuilder->shouldReceive('select')->once()->andReturnSelf();
        $queryBuilder->shouldReceive('first')
            ->once()
            ->andReturn((object) [
                'tenant_public_id' => '01JZZZB87RDKVG7FN6M7F9Y3QX',
                'tenant_slug' => 'registry-tenant',
                'tenant_display_name' => 'Registry Tenant',
                'tenant_status' => 'active',
                'tenant_locale' => 'en',
                'tenant_timezone' => 'UTC',
                'tenant_enabled_modules' => json_encode(['dispatch', 'crm']),
                'tenant_enabled_capabilities' => json_encode(['billing']),
                'domain' => 'registry-tenant.example.com',
                'database_name' => 'wbyt_t_01k000',
                'database_status' => 'active',
                'secret_reference' => 'secret://tenant/registry',
                'cluster_name' => 'cluster-b',
                'cluster_host' => 'mysql-tenant-b',
                'cluster_port' => 3306,
                'cluster_ssl_mode' => 'preferred',
            ]);

        $resolver = new CentralTenantDatabaseResolver();
        $resolved = $resolver->resolveByDomain('registry-tenant.example.com');

        $this->assertNotNull($resolved);
        $this->assertSame('Registry Tenant', $resolved->tenantDisplayName);
        $this->assertSame('mysql-tenant-b', $resolved->clusterHost);
    }
}
