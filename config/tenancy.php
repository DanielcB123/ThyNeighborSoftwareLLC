<?php

declare(strict_types=1);

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST);
$localDomainsEnabled = (bool) env('TENANCY_LOCAL_DOMAINS_ENABLED', false);
$resolutionCacheTtl = (int) env(
    'TENANCY_RESOLUTION_CACHE_TTL',
    env('TENANCY_RESOLUTION_CACHE_TTL_SECONDS', 300)
);
$platformDomains = array_values(array_unique(array_filter([
    is_string($appHost) ? strtolower(trim($appHost)) : null,
    ...($localDomainsEnabled ? ['localhost', '127.0.0.1'] : []),
    ...array_map(
        static fn (string $domain): string => strtolower(trim($domain)),
        array_filter(array_map('trim', explode(',', (string) env('PLATFORM_DOMAINS', ''))))
    ),
], static fn (?string $domain): bool => is_string($domain)
    && $domain !== ''
    && ! str_contains($domain, '*')
    && ! str_starts_with($domain, '.'))));

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Domains
    |--------------------------------------------------------------------------
    |
    | Domains listed here are treated as platform/control-plane domains and
    | will not trigger tenant database resolution middleware.
    |
    */
    'platform_domains' => $platformDomains,

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution Cache
    |--------------------------------------------------------------------------
    */
    'resolution_cache_store' => env('TENANCY_RESOLUTION_CACHE_STORE', 'redis'),
    'resolution_cache_prefix' => env('TENANCY_RESOLUTION_CACHE_PREFIX', 'tenant-domain-resolution:'),
    'resolution_cache_ttl' => $resolutionCacheTtl,
    'resolution_cache_ttl_seconds' => $resolutionCacheTtl,
    'negative_cache_ttl' => (int) env('TENANCY_NEGATIVE_CACHE_TTL', 30),
    'unknown_host_behavior' => env('TENANCY_UNKNOWN_HOST_BEHAVIOR', 'reject'),
    'local_domains_enabled' => $localDomainsEnabled,

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection
    |--------------------------------------------------------------------------
    */
    'tenant_connection_name' => env('TENANT_CONNECTION_NAME', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Secret Provider
    |--------------------------------------------------------------------------
    |
    | This config-backed provider is intended for local/testing bootstraps.
    | Production environments should replace it with a managed secret backend.
    |
    */
    'secret_provider' => [
        'fallback_username' => env('TENANT_DB_FALLBACK_USERNAME'),
        'fallback_password' => env('TENANT_DB_FALLBACK_PASSWORD'),
        'local_cluster_defaults' => [
            'host' => env('TENANT_DB_HOST', env('CENTRAL_DB_HOST', 'mysql')),
            'port' => (int) env('TENANT_DB_PORT', 3306),
            'ssl_mode' => env('TENANT_DB_SSL_MODE', 'preferred'),
        ],
        'references' => [],
    ],
];
