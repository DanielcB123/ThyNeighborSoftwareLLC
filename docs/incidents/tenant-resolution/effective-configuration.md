# Effective Configuration Audit — TENANCY-LOCAL-REGISTRY-001

## Sources Reviewed

- `.env` (not present)
- `.env.local` (not present at baseline)
- `.env.testing`
- `.env.example`
- `phpunit.xml`
- Docker Compose files (not present)
- Docker env files (not present)
- CI workflow env definitions (`.github/workflows/*`) (not present)
- Shell-exported environment variables (captured by new command design)
- Local process-manager configuration (not present in repository)

## Duplicate Keys (within source files)

No duplicate tracked keys were found in:

- `.env.testing`
- `.env.example`
- `phpunit.xml`

## Conflicting Keys (across active local sources)

Observed by static inspection:

- `APP_ENV`: `.env.testing=testing` vs `.env.example=local`
- `CENTRAL_DB_DATABASE`: testing-scoped value differs from example/default value
- `CACHE_STORE`: `.env.testing=array` vs `.env.example=redis`
- `QUEUE_CONNECTION`: `.env.testing=sync` vs `.env.example=redis`

These conflicts are partially expected between testing and example templates, but they must be explicitly surfaced.

## Deprecated / Ignored Keys

Deprecated tenancy keys identified:

- `TENANT_DB_DATABASE`
- `TENANT_DB_USERNAME`
- `TENANT_DB_PASSWORD`

Ignored-by-architecture key identified:

- `TENANT_DB_DRIVER` (tenant driver is fixed by `config/database.php`)

## Effective Value Path Normalization

Remediation introduced explicit config-path ownership for critical settings:

- Central DB authority: `config('database.connections.central.*')`
- Resolution cache controls:
  - `config('tenancy.resolution_cache_store')`
  - `config('tenancy.resolution_cache_ttl')`
  - `config('tenancy.negative_cache_ttl')`
- Unknown host handling:
  - `config('tenancy.unknown_host_behavior')`
- Platform domain classification:
  - `config('tenancy.platform_domains')`

## Corrective Action

- Added `php artisan app:audit-environment` to consistently report:
  - duplicate keys
  - cross-source conflicts
  - deprecated and ignored keys
  - missing required keys
  - SQLite misconfiguration risk
- Removed raw `env()` usage from runtime tenancy/demo services so effective values are owned by config files.
