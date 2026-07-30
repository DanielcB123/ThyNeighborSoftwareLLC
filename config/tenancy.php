<?php

declare(strict_types=1);

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST);

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
    'platform_domains' => array_values(array_unique(array_filter([
        is_string($appHost) ? strtolower($appHost) : null,
        'localhost',
        '127.0.0.1',
        ...array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            array_filter(array_map('trim', explode(',', (string) env('PLATFORM_DOMAINS', ''))))
        ),
    ]))),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution Cache
    |--------------------------------------------------------------------------
    */
    'resolution_cache_store' => env('TENANCY_RESOLUTION_CACHE_STORE', 'redis'),
    'resolution_cache_prefix' => env('TENANCY_RESOLUTION_CACHE_PREFIX', 'tenant-domain-resolution:'),
    'resolution_cache_ttl_seconds' => (int) env('TENANCY_RESOLUTION_CACHE_TTL_SECONDS', 300),

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
        'references' => [],
    ],
];
