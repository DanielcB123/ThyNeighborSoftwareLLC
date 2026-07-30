<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\CentralTenantDatabaseResolver;
use App\Tenancy\TenantDatabaseConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ResolveTenantFromDomain
{
    public function __construct(
        private readonly CentralTenantDatabaseResolver $resolver,
        private readonly TenantDatabaseConnectionManager $connectionManager,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $this->connectionManager->reset();

        try {
            $host = $this->normalizeDomain($request->getHost());

            if ($host === '' || $this->isPlatformDomain($host)) {
                $request->attributes->set('tenantRuntimeSurface', 'platform');
                return $next($request);
            }

            $resolved = $this->resolver->resolveByDomain($host);

            if ($resolved === null) {
                $request->attributes->set('tenantRuntimeSurface', 'unknown_host');

                return $this->handleUnknownHost($request, $next);
            }

            if ($resolved->tenantStatus !== 'active' || $resolved->databaseStatus !== 'active') {
                throw new HttpException(423, 'This tenant is currently unavailable.');
            }

            try {
                $this->connectionManager->activate($resolved);
            } catch (Throwable) {
                throw new HttpException(503, 'Tenant database connection is unavailable.');
            }

            $request->attributes->set('resolvedTenantDatabase', [
                'publicId' => $resolved->tenantPublicId,
                'slug' => $resolved->tenantSlug,
                'displayName' => $resolved->tenantDisplayName,
                'domain' => $resolved->domain,
                'databaseName' => $resolved->databaseName,
                'clusterName' => $resolved->clusterName,
            ]);

            $runtimeSurface = $this->resolveTenantRuntimeSurface($request);
            $request->attributes->set('tenantRuntimeSurface', $runtimeSurface);

            if (! is_array($request->attributes->get('frontendTenantContext'))) {
                $request->attributes->set('frontendTenantContext', [
                    'surface' => $this->frontendSurfaceForRuntime($runtimeSurface),
                    'publicId' => $resolved->tenantPublicId,
                    'slug' => $resolved->tenantSlug,
                    'displayName' => $resolved->tenantDisplayName,
                    'locale' => $resolved->tenantLocale,
                    'timezone' => $resolved->tenantTimezone,
                    'enabledModules' => $resolved->tenantEnabledModules,
                    'enabledCapabilities' => $resolved->tenantEnabledCapabilities,
                    'urls' => [
                        'primaryBaseUrl' => sprintf('https://%s', $resolved->domain),
                        'authBaseUrl' => sprintf('https://%s', $resolved->domain),
                        'adminBaseUrl' => sprintf('https://%s/admin', $resolved->domain),
                        'previewBaseUrl' => sprintf('https://%s/preview', $resolved->domain),
                    ],
                ]);
            }

            return $next($request);
        } finally {
            $this->connectionManager->reset();
        }
    }

    private function handleUnknownHost(Request $request, Closure $next): mixed
    {
        $behavior = strtolower((string) config('tenancy.unknown_host_behavior', 'reject'));

        if ($behavior === 'passthrough') {
            return $next($request);
        }

        throw new NotFoundHttpException('No active tenant exists for this domain.');
    }

    private function isPlatformDomain(string $domain): bool
    {
        $platformDomains = config('tenancy.platform_domains', []);

        if (! is_array($platformDomains)) {
            return false;
        }

        return in_array($domain, array_map('strtolower', $platformDomains), true);
    }

    private function resolveTenantRuntimeSurface(Request $request): string
    {
        if ($request->is('api/*')) {
            return 'tenant_api';
        }

        if ($request->is('admin*') || $request->is('dashboard*')) {
            return 'tenant_admin';
        }

        if (
            $request->is('login') ||
            $request->is('register') ||
            $request->is('password/*') ||
            $request->is('auth/*')
        ) {
            return 'tenant_auth';
        }

        return 'tenant_public';
    }

    private function frontendSurfaceForRuntime(string $runtimeSurface): string
    {
        return match ($runtimeSurface) {
            'tenant_admin' => 'tenant-admin',
            'tenant_auth' => 'tenant-auth',
            default => 'tenant-public',
        };
    }

    private function normalizeDomain(string $domain): string
    {
        $normalized = strtolower(trim($domain));
        $normalized = rtrim($normalized, '.');

        if ($normalized === '') {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($normalized);

            if (is_string($ascii) && $ascii !== '') {
                return strtolower($ascii);
            }
        }

        return $normalized;
    }
}
