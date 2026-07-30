# Verification Results — TENANCY-LOCAL-REGISTRY-001

## Executed Verification

- Baseline command capture files were generated under `command-output/`.
- Static code-level verification performed for:
  - unknown-host behavior defaults
  - runtime-surface attribution paths
  - central resolver cache behavior changes
  - env/config path ownership changes

## Environment Constraint

PHP runtime is unavailable in this cloud execution environment (`php: command not found`), so the following could not be executed here:

- `php artisan about`
- `php artisan env`
- `php artisan config:show ...`
- `php artisan demo:verify`
- `php artisan app:audit-environment`
- PHPUnit test suite

## Planned Runtime Gate Commands (to run in PHP-enabled environment)

1. `php artisan app:audit-environment`
2. `php artisan optimize:clear`
3. `php artisan config:clear`
4. `php artisan cache:clear`
5. `php artisan demo:verify`
6. `php artisan test --filter=ResolveTenantFromDomainMiddlewareTest`
7. `php artisan test --filter=CentralTenantDatabaseResolverTest`
8. `php artisan test --filter=AuditEnvironmentCommandTest`

## Expected Post-Remediation Outcomes

- Unknown non-platform tenant host defaults to `404` instead of platform fallback.
- Resolver writes and honors negative cache entries for unresolved hosts.
- `frontendRuntime.runtimeSurface` and `tenantRuntimeSurface` expose deterministic runtime classification.
- `app:audit-environment` surfaces conflicting/missing/deprecated tenancy keys without leaking secrets.
