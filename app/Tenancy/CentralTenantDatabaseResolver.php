<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Tenancy\Data\ResolvedTenantDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CentralTenantDatabaseResolver
{
    private const CACHE_KIND_POSITIVE = 'resolved';

    private const CACHE_KIND_NEGATIVE = 'not_found';

    public function resolveByDomain(string $domain): ?ResolvedTenantDatabase
    {
        $normalizedDomain = $this->normalizeDomain($domain);

        if ($normalizedDomain === '') {
            return null;
        }

        $cacheKey = $this->cacheKey($normalizedDomain);
        $cached = $this->resolveFromCache($cacheKey);

        if ($cached['hit']) {
            return $cached['resolved'];
        }

        $resolved = $this->resolveFromCentralRegistry($normalizedDomain);

        if ($resolved !== null) {
            $this->cachePositiveResolution($cacheKey, $resolved);
        } else {
            $this->cacheNegativeResolution($cacheKey);
        }

        return $resolved;
    }

    private function cacheKey(string $domain): string
    {
        return (string) config('tenancy.resolution_cache_prefix', 'tenant-domain-resolution:').$domain;
    }

    /**
     * @return array{hit:bool,resolved:ResolvedTenantDatabase|null}
     */
    private function resolveFromCache(string $cacheKey): array
    {
        try {
            $payload = Cache::store((string) config('tenancy.resolution_cache_store', 'redis'))
                ->get($cacheKey);
        } catch (Throwable) {
            return ['hit' => false, 'resolved' => null];
        }

        if (! is_array($payload)) {
            return ['hit' => false, 'resolved' => null];
        }

        $kind = (string) ($payload['kind'] ?? '');
        if ($kind === self::CACHE_KIND_NEGATIVE) {
            return ['hit' => true, 'resolved' => null];
        }

        if ($kind === self::CACHE_KIND_POSITIVE) {
            $resolved = ResolvedTenantDatabase::fromCachePayload(
                is_array($payload['data'] ?? null) ? $payload['data'] : []
            );

            return $resolved === null
                ? ['hit' => false, 'resolved' => null]
                : ['hit' => true, 'resolved' => $resolved];
        }

        // Backward compatibility for legacy cache payloads.
        $resolved = ResolvedTenantDatabase::fromCachePayload($payload);

        return $resolved === null
            ? ['hit' => false, 'resolved' => null]
            : ['hit' => true, 'resolved' => $resolved];
    }

    private function cachePositiveResolution(string $cacheKey, ResolvedTenantDatabase $resolved): void
    {
        try {
            Cache::store((string) config('tenancy.resolution_cache_store', 'redis'))
                ->put(
                    $cacheKey,
                    [
                        'kind' => self::CACHE_KIND_POSITIVE,
                        'data' => $resolved->toCachePayload(),
                    ],
                    now()->addSeconds($this->positiveCacheTtl())
                );
        } catch (Throwable) {
            // Redis cache is optional. Central registry remains authoritative.
        }
    }

    private function cacheNegativeResolution(string $cacheKey): void
    {
        $negativeTtl = (int) config('tenancy.negative_cache_ttl', 30);

        if ($negativeTtl <= 0) {
            return;
        }

        try {
            Cache::store((string) config('tenancy.resolution_cache_store', 'redis'))
                ->put(
                    $cacheKey,
                    ['kind' => self::CACHE_KIND_NEGATIVE],
                    now()->addSeconds($negativeTtl)
                );
        } catch (Throwable) {
            // Redis cache is optional. Central registry remains authoritative.
        }
    }

    private function resolveFromCentralRegistry(string $domain): ?ResolvedTenantDatabase
    {
        $record = DB::connection('central')
            ->table('tenant_domains as td')
            ->join('tenants as t', 't.id', '=', 'td.tenant_id')
            ->join('tenant_databases as tdb', function ($join): void {
                $join->on('tdb.tenant_id', '=', 't.id')
                    ->where('tdb.is_current', '=', 1);
            })
            ->join('database_clusters as dc', 'dc.id', '=', 'tdb.database_cluster_id')
            ->where('td.domain', '=', $domain)
            ->where('td.is_active', '=', 1)
            ->where('t.status', '=', 'active')
            ->where('tdb.status', '=', 'active')
            ->where('dc.is_active', '=', 1)
            ->whereNull('t.deleted_at')
            ->select([
                't.public_id as tenant_public_id',
                't.slug as tenant_slug',
                't.display_name as tenant_display_name',
                't.status as tenant_status',
                't.locale as tenant_locale',
                't.timezone as tenant_timezone',
                't.enabled_modules as tenant_enabled_modules',
                't.enabled_capabilities as tenant_enabled_capabilities',
                'td.domain as domain',
                'tdb.database_name as database_name',
                'tdb.status as database_status',
                'tdb.secret_reference as secret_reference',
                'dc.name as cluster_name',
                'dc.host as cluster_host',
                'dc.port as cluster_port',
                'dc.ssl_mode as cluster_ssl_mode',
            ])
            ->first();

        if ($record === null) {
            return null;
        }

        return new ResolvedTenantDatabase(
            (string) $record->tenant_public_id,
            (string) $record->tenant_slug,
            (string) $record->tenant_display_name,
            (string) $record->tenant_status,
            (string) $record->tenant_locale,
            (string) $record->tenant_timezone,
            $this->decodeJsonList($record->tenant_enabled_modules ?? null),
            $this->decodeJsonList($record->tenant_enabled_capabilities ?? null),
            (string) $record->domain,
            (string) $record->database_name,
            (string) $record->database_status,
            (string) $record->secret_reference,
            (string) $record->cluster_name,
            (string) $record->cluster_host,
            (int) $record->cluster_port,
            (string) $record->cluster_ssl_mode,
        );
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

    private function positiveCacheTtl(): int
    {
        $ttl = (int) config(
            'tenancy.resolution_cache_ttl',
            (int) config('tenancy.resolution_cache_ttl_seconds', 300)
        );

        return max(1, $ttl);
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function decodeJsonList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map('strval', $value));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('strval', $decoded));
    }
}
