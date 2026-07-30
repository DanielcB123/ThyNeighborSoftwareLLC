# Runtime Audit — 2026-07-30 (Local)

This audit was executed locally against the active workspace to validate tenancy resolution behavior with runtime evidence only (no assumptions).

## Step 1 — Baseline Evidence Capture

### Commands executed

- `php artisan about`
- `php artisan env`
- `php artisan config:show database`
- `php artisan config:show tenancy`
- `php artisan demo:verify`
- `git status --short --branch`

### Baseline findings

- App environment: `local`
- Config cache: `NOT CACHED`
- DB default driver: `central`
- Effective central connection target from config:
  - host: `127.0.0.1`
  - port: `3307`
  - database: `wbyt_local_dev`
  - username: `root`
- Tenancy resolution cache store (effective): `redis` (initial baseline)
- Baseline verify status: `FAIL` for:
  - `central_demo_tenant_records_and_domains`
  - `resolution_cache`

## Step 2 — Active Central DB Target Verification

### Evidence

- `.env` contained duplicate keys:
  - `CENTRAL_DB_DATABASE=webuildyouthrive_central`
  - `CENTRAL_DB_DATABASE=wbyt_local_dev`
- Runtime effective target before remediation (via config): `wbyt_local_dev`

### Interpretation

Duplicate central DB keys created ambiguity risk. Runtime selected the later key (`wbyt_local_dev`), but the source-of-truth file was not deterministic.

## Step 3 — Central Registry Population State

### Baseline SQL evidence (before reseed)

In `wbyt_local_dev`:

- `tenants`: `0`
- `tenant_domains`: `0`
- `tenant_databases`: `0`
- `database_clusters`: `0`

### Post-remediation SQL evidence

In `wbyt_local_dev`:

- `tenants`: `3`
- `tenant_domains`: `3`
- `tenant_databases`: `3`
- `database_clusters`: `1`

Active tenant rows:

- `atlas-field-services`, `active`, `is_demo=1`
- `carolina-beauty-collective`, `active`, `is_demo=1`
- `coastal-comfort-plumbing`, `active`, `is_demo=1`

Domain mappings:

- `atlasfieldservices.test` -> `atlas-field-services`
- `carolinabeautycollective.test` -> `carolina-beauty-collective`
- `coastalcomfortplumbing.test` -> `coastal-comfort-plumbing`

Tenant DB mappings:

- all three tenants marked `status=active`, `is_current=1`
- all mapped to `demo-local-cluster` at `127.0.0.1:3307`

## Step 4 — Direct Domain Resolver Proof

### Baseline resolver evidence

- `resolveByDomain('coastalcomfortplumbing.test')` -> `null`

### Post-remediation resolver evidence

- `resolveByDomain('coastalcomfortplumbing.test')` ->
  - tenant slug: `coastal-comfort-plumbing`
  - tenant public id: `01K1A4SMALLBIZTENANT000001`
  - resolved domain: `coastalcomfortplumbing.test`
  - database name: `wbyt_local_coastal_comfort_plumbing`
  - cluster host/port: `127.0.0.1:3307`

## Step 5 — Middleware Runtime Behavior with Host Header / Domain Requests

### After remediation + server restart

- `localhost:8000` ->
  - component: `Platform/Home`
  - surface: `platform`
  - tenant slug: empty
- `coastalcomfortplumbing.test:8000` ->
  - component: `Platform/Home`
  - surface: `tenant-public`
  - tenant slug: `coastal-comfort-plumbing`
- `carolinabeautycollective.test:8000` ->
  - component: `Platform/Home`
  - surface: `tenant-public`
  - tenant slug: `carolina-beauty-collective`
- `atlasfieldservices.test:8000` ->
  - component: `Platform/Home`
  - surface: `tenant-public`
  - tenant slug: `atlas-field-services`

### Interpretation

Tenant domain resolution and runtime tenancy attribution are now working.
The shared `Platform/Home` component on tenant domains remains a routing/content placeholder concern, not a resolution failure.

## Step 6 — Resolution Cache Behavior

### Baseline cache evidence

- tenancy cache store: `redis`
- runtime probe error: `Class "Redis" not found`

### Post-remediation cache evidence

- tenancy cache store: `file`
- write/read probe:
  - wrote key `tenant-domain-resolution:probe`
  - read result: `['ok' => true]`

## Remediation Actions Performed

1. Updated `.env`:
   - removed duplicate `CENTRAL_DB_DATABASE=webuildyouthrive_central`
   - retained `CENTRAL_DB_DATABASE=wbyt_local_dev`
   - set `TENANCY_RESOLUTION_CACHE_STORE=file`
   - set `TENANT_DB_FALLBACK_USERNAME=root`
   - set `TENANT_DB_FALLBACK_PASSWORD=`
2. Cleared cached bootstrap artifacts:
   - `php artisan optimize:clear`
3. Reseeded demo data:
   - `php artisan demo:seed --profile=standard --tenant=all --force`
4. Restarted local Laravel dev server to load updated env.
5. Re-ran verification:
   - `php artisan demo:verify` -> all checks `PASS`

## Current Status

- Tenant registry state: healthy
- Resolver behavior: healthy
- Middleware runtime tenancy attribution: healthy
- Resolution cache behavior: healthy for local setup
- Remaining non-blocker: tenant domains currently render a shared page component (`Platform/Home`) due route/content placeholder design.
