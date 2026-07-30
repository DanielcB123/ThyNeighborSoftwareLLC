# MySQL Conversion Audit (WEB-13)

## Scope

This audit replaces SQLite assumptions with a MySQL-first architecture and documents the tenant database resolution strategy:

- Central platform database: `webuildyouthrive_central`
- Isolated tenant databases resolved by exact custom domain
- No runtime database discovery (`SHOW DATABASES`, `INFORMATION_SCHEMA` scans, or guessed names)
- Central registry is authoritative; Redis is cache-only and optional

## SQLite Reference Inventory and Disposition

| File | Reference | Disposition |
| --- | --- | --- |
| `.env.example` | `DB_CONNECTION=sqlite` and SQLite-oriented defaults | Replaced with explicit `central` and `tenant` MySQL variables |
| `config/database.php` | `sqlite` connection, SQLite fallback defaults, `database.sqlite`, SQLite-only options | Removed SQLite defaults from runtime config; central + tenant MySQL configuration now authoritative |
| `config/queue.php` | Queue batching/failed-job defaults fallback to `sqlite` | Updated to use central connection defaults |
| `phpunit.xml` | `DB_CONNECTION=sqlite` and `:memory:` | Replaced with central MySQL testing environment variables |
| `composer.json` | `post-create-project-cmd` created `database/database.sqlite` | Removed SQLite file creation command |
| `database/.gitignore` | `*.sqlite*` | Replaced with generic local DB artifact ignores (`*.db`, `*.sql`) |
| `tests/*` | `RefreshDatabase` trait usage | Retained. Trait now runs against configured MySQL testing connection (no SQLite memory DB fallback) |
| `routes/console.php` and `app/Console/Commands/ImportSqliteToMysqlCommand.php` | New explicit SQLite migration utility | Intentionally added as one-time migration path from legacy SQLite to central MySQL |

## New Tenant Database Resolution Architecture

Implemented classes:

- `App\Tenancy\CentralTenantDatabaseResolver`
- `App\Tenancy\TenantDatabaseConnectionManager`
- `App\Http\Middleware\ResolveTenantFromDomain`
- `App\Tenancy\Contracts\TenantDatabaseSecretProvider`
- `App\Tenancy\ConfigTenantDatabaseSecretProvider`

Implemented central registry schema:

- `database_clusters`
- `tenants`
- `tenant_domains`
- `tenant_databases`

Resolution flow in code:

1. Request host/domain normalization
2. Exact-match lookup in central registry (`tenant_domains.domain`)
3. Active tenant + active current tenant database + active cluster filtering
4. Secret reference retrieval (via secret provider abstraction)
5. Runtime `tenant` connection reconfiguration and purge/reconnect
6. Guaranteed connection reset after every request and queue job

### Prohibited Discovery Behaviors

The codebase now avoids and does not implement:

- `SHOW DATABASES`
- `INFORMATION_SCHEMA` scanning
- Guessed database names from domain/company strings
- Looping through databases per request

## Redis Role and Failure Behavior

- Redis configured as cache/session/queue backend defaults
- Tenant domain resolution cache uses Redis, but cache failures are caught and bypassed
- On Redis outage, resolver falls back directly to central database lookup

## Queue/Worker Tenant Connection Hygiene

`AppServiceProvider` now resets tenant DB state on queue lifecycle events:

- `Queue::before`
- `Queue::after`
- `Queue::failing`

This prevents long-lived workers from reusing previous tenant DB connection state.

## SQLite to MySQL Import Command

Added one-time command:

```bash
php artisan app:import-sqlite-to-mysql --source=/path/to/source.sqlite --target-env=local [--dry-run]
```

Safety controls implemented:

- Requires explicit source file (`--source`)
- Requires explicit target environment (`--target-env`)
- Blocks production unless `--allow-production` is provided
- Imports in dependency order
- Preserves existing identifiers by row copy
- Validates row counts and records failed rows
- Supports dry-run mode
- Writes JSON migration report under `storage/app/reports/`
- Aborts live imports when target MySQL tables are non-empty

## MySQL Behavior Notes

Expected MySQL behavior now codified:

- InnoDB engine
- `utf8mb4` / `utf8mb4_0900_ai_ci`
- Strict SQL modes:
  - `STRICT_TRANS_TABLES`
  - `ERROR_FOR_DIVISION_BY_ZERO`
  - `NO_ENGINE_SUBSTITUTION`
- UTC connection timezone default (`+00:00`)

## Testing Changes

- Testing no longer uses SQLite in-memory DB
- PHPUnit now targets central MySQL variables
- Added `.env.testing` with MySQL-centric defaults
- Added tenancy tests:
  - `tests/Unit/Tenancy/CentralTenantDatabaseResolverTest.php`
  - `tests/Feature/Tenancy/ResolveTenantFromDomainMiddlewareTest.php`

## CI / Docker / Workflow Audit Notes

Searched paths listed in the issue:

- `.github/workflows/` (not present in repository)
- `docker-compose.yml` / `compose.yml` / `Dockerfile` (not present in repository)

Result: no SQLite assumptions found in absent CI/Docker files for this repository snapshot.

## Local Development Setup Implications

- Developers must run a MySQL server for local development and tests
- Central DB connection must be provisioned (`webuildyouthrive_central`)
- Tenant DB credentials should come from secret provider integration, not per-tenant `.env` keys

## Production Infrastructure Requirements

- MySQL 8.4 LTS target (or supported MySQL 8 variant where 8.4 unavailable)
- Central cluster + scalable tenant clusters
- Central registry durability and indexing for domain lookups
- Managed secret provider integration replacing config-based fallback secrets
- Redis deployed for cache/session/queue, but tenant resolution remains operable if Redis is unavailable
