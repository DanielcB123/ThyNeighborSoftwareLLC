# Middleware Runtime Audit — TENANCY-LOCAL-REGISTRY-001

## Middleware Under Audit

- `App\Http\Middleware\ResolveTenantFromDomain`
- `App\Http\Middleware\HandleInertiaRequests`

## Remediation Changes

1. Unknown host pass-through removed as default.
2. Runtime surface marker introduced on request attributes:
   - `tenantRuntimeSurface`
3. Frontend runtime payload extended with:
   - `frontendRuntime.runtimeSurface`
4. Tenant frontend context surface now derives from runtime classification when resolver succeeds.

## Runtime Surface Classification Rules

- `api/*` -> `tenant_api`
- `admin*` or `dashboard*` -> `tenant_admin`
- `login`, `register`, `password/*`, `auth/*` -> `tenant_auth`
- otherwise (tenant host with valid mapping) -> `tenant_public`
- platform hosts -> `platform`
- unresolved non-platform hosts -> `unknown_host`

## Test Coverage Added/Updated

- `tests/Feature/Tenancy/ResolveTenantFromDomainMiddlewareTest.php`
  - verifies resolved host runtime surface and context
  - verifies default unknown-host rejection
  - verifies optional passthrough override behavior
