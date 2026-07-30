# Resolution Cache Audit — TENANCY-LOCAL-REGISTRY-001

## Pre-Remediation Behavior

- Resolver cached only positive tenant-domain hits.
- Repeated misses always queried central registry.
- No explicit negative-cache signal existed.

## Remediated Behavior

`CentralTenantDatabaseResolver` now supports explicit cache payload kinds:

- positive: `kind=resolved`, includes serialized tenant database payload
- negative: `kind=not_found`

Configuration controls:

- `tenancy.resolution_cache_store`
- `tenancy.resolution_cache_ttl`
- `tenancy.negative_cache_ttl`

Behavior guarantees:

- cache read/write exceptions never bypass central authority
- negative cache is optional (ttl <= 0 disables)
- legacy payload compatibility retained

## Tests Added/Updated

- `tests/Unit/Tenancy/CentralTenantDatabaseResolverTest.php`
  - verifies negative-cache hit returns `null` without DB query
  - verifies unresolved registry lookups write negative-cache entries
