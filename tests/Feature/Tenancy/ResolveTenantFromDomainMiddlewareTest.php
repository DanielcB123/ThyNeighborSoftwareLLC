<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Tenancy\CentralTenantDatabaseResolver;
use App\Tenancy\Data\ResolvedTenantDatabase;
use App\Tenancy\TenantDatabaseConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class ResolveTenantFromDomainMiddlewareTest extends TestCase
{
    public function test_it_resolves_tenant_from_exact_domain_and_sets_request_context(): void
    {
        $resolver = Mockery::mock(CentralTenantDatabaseResolver::class);
        $connectionManager = Mockery::mock(TenantDatabaseConnectionManager::class);

        $this->app->instance(CentralTenantDatabaseResolver::class, $resolver);
        $this->app->instance(TenantDatabaseConnectionManager::class, $connectionManager);

        $connectionManager->shouldReceive('reset')->twice();
        $connectionManager->shouldReceive('activate')->once();

        $resolver->shouldReceive('resolveByDomain')
            ->once()
            ->with('tenant-a.example.com')
            ->andReturn(new ResolvedTenantDatabase(
                tenantPublicId: '01JZZZB87RDKVG7FN6M7F9Y3QX',
                tenantSlug: 'tenant-a',
                tenantDisplayName: 'Tenant A',
                tenantStatus: 'active',
                tenantLocale: 'en',
                tenantTimezone: 'UTC',
                tenantEnabledModules: ['dispatch', 'crm'],
                tenantEnabledCapabilities: ['billing'],
                domain: 'tenant-a.example.com',
                databaseName: 'wbyt_t_01k111',
                databaseStatus: 'active',
                secretReference: 'secret://tenant/a',
                clusterName: 'cluster-a',
                clusterHost: 'mysql-tenant-a',
                clusterPort: 3306,
                clusterSslMode: 'preferred',
            ));

        $routePath = '/_test/tenant-resolution';

        Route::middleware('web')->get($routePath, function (Request $request) {
            return response()->json([
                'resolvedTenantDatabase' => $request->attributes->get('resolvedTenantDatabase'),
                'tenantRuntimeSurface' => $request->attributes->get('tenantRuntimeSurface'),
                'frontendTenantContext' => $request->attributes->get('frontendTenantContext'),
            ]);
        });

        $this->withServerVariables(['HTTP_HOST' => 'tenant-a.example.com'])
            ->get($routePath)
            ->assertOk()
            ->assertJsonPath('resolvedTenantDatabase.publicId', '01JZZZB87RDKVG7FN6M7F9Y3QX')
            ->assertJsonPath('resolvedTenantDatabase.slug', 'tenant-a')
            ->assertJsonPath('resolvedTenantDatabase.domain', 'tenant-a.example.com')
            ->assertJsonPath('resolvedTenantDatabase.databaseName', 'wbyt_t_01k111')
            ->assertJsonPath('tenantRuntimeSurface', 'tenant_public')
            ->assertJsonPath('frontendTenantContext.surface', 'tenant-public');
    }

    public function test_it_rejects_unknown_domains_by_default(): void
    {
        $resolver = Mockery::mock(CentralTenantDatabaseResolver::class);
        $connectionManager = Mockery::mock(TenantDatabaseConnectionManager::class);

        $this->app->instance(CentralTenantDatabaseResolver::class, $resolver);
        $this->app->instance(TenantDatabaseConnectionManager::class, $connectionManager);

        $connectionManager->shouldReceive('reset')->twice();
        $resolver->shouldReceive('resolveByDomain')
            ->once()
            ->with('unknown-tenant.example.com')
            ->andReturnNull();

        $routePath = '/_test/tenant-resolution-bypass';

        Route::middleware('web')->get($routePath, function () {
            return response()->json(['ok' => true]);
        });

        $this->withServerVariables(['HTTP_HOST' => 'unknown-tenant.example.com'])
            ->get($routePath)
            ->assertNotFound();
    }

    public function test_it_can_allow_unknown_domains_when_passthrough_behavior_is_enabled(): void
    {
        config()->set('tenancy.unknown_host_behavior', 'passthrough');

        $resolver = Mockery::mock(CentralTenantDatabaseResolver::class);
        $connectionManager = Mockery::mock(TenantDatabaseConnectionManager::class);

        $this->app->instance(CentralTenantDatabaseResolver::class, $resolver);
        $this->app->instance(TenantDatabaseConnectionManager::class, $connectionManager);

        $connectionManager->shouldReceive('reset')->twice();
        $resolver->shouldReceive('resolveByDomain')
            ->once()
            ->with('unknown-tenant.example.com')
            ->andReturnNull();

        $routePath = '/_test/tenant-resolution-passthrough';

        Route::middleware('web')->get($routePath, function (Request $request) {
            return response()->json([
                'ok' => true,
                'tenantRuntimeSurface' => $request->attributes->get('tenantRuntimeSurface'),
            ]);
        });

        $this->withServerVariables(['HTTP_HOST' => 'unknown-tenant.example.com'])
            ->get($routePath)
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('tenantRuntimeSurface', 'unknown_host');
    }
}
