# Central Registry Audit — TENANCY-LOCAL-REGISTRY-001

## Objective

Confirm that tenant domain resolution uses central registry records and never bypasses registry state with static tenant database assumptions.

## Static Audit Findings

1. Central resolver query path is authoritative:
   - `tenant_domains -> tenants -> tenant_databases -> database_clusters`
   - Implemented in `App\Tenancy\CentralTenantDatabaseResolver`
2. Demo tenant provisioning updates central registry entities via Eloquent:
   - `Tenant`
   - `TenantDomain`
   - `TenantDatabase`
   - `DatabaseCluster`
3. Registry seeding helper `DemoTenantRegistrySeeder` only writes summary metadata to `central_settings`; full tenant/domain/database records are created by demo provisioning flow.

## Runtime Count Verification Status

Blocked in this cloud runtime because `php` is unavailable; direct artisan/database verification could not be executed here.

## Remediation Relevance

- Unknown-host fallback was removed for default behavior, preventing registry-empty states from silently rendering platform pages.
- Resolver now supports negative-cache entries to prevent repeated registry misses from behaving unpredictably under repeated requests.
