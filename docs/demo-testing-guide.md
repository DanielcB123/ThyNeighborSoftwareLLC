# WEB-14 Demo Dataset Testing Guide

This guide defines both manual and programmatic test workflows for the WEB-14 demo-data implementation.

The goal is to validate:

- demo-data safety guardrails
- central tenant registry correctness
- physical tenant database isolation
- seeded auth + role/permission coverage
- CRM, content, finance, analytics coverage
- verification and refresh lifecycle behavior

---

## 1) Prerequisites

## 1.1 Environment variables

Set these in `.env` for local verification:

```dotenv
APP_ENV=local
DEMO_SEEDING_ENABLED=true
DEMO_AUTO_SEED_ON_DATABASE_SEEDER=true
DEMO_USER_PASSWORD=DemoPassword!2026
DEMO_DATA_PROFILE=standard
CENTRAL_DB_DATABASE=wbyt_local_dev
```

`CENTRAL_DB_DATABASE` must match one of:

- `webuildyouthrive_central`
- `wbyt_local_*`
- `wbyt_test_*`

## 1.2 Database/user assumptions

- Central database user must be able to read/write central tables.
- Tenant DB credentials must be resolvable either from:
  - `tenancy.secret_provider.references`, or
  - fallback central credentials.
- Tenant DB user must be able to create/drop tenant databases for `demo:seed`/`demo:reset` workflows.

## 1.3 Migration baseline

Run central migrations before testing:

```bash
php artisan migrate
```

---

## 2) Manual test plan

## 2.1 Safety guardrails

Run each case and confirm the expected failure:

1. `DEMO_SEEDING_ENABLED=false` then:
   - `php artisan demo:seed --force`
   - Expected: blocked with `Demo seeding is disabled...`

2. `APP_ENV=production` then:
   - `php artisan demo:seed --force`
   - Expected: blocked by environment guard.

3. Unsafe central DB name (example `prod_central`) then:
   - `php artisan demo:seed --force`
   - Expected: blocked by unsafe DB-name guard.

4. Repeat each failure case using `--force`:
   - Expected: still blocked.
   - `--force` must never bypass safety checks.

## 2.2 Seed full standard dataset

```bash
php artisan demo:seed --profile=standard --tenant=all --force
```

Validate command output:

- warning banner:
  - `DEMO DATA ONLY`
  - `DO NOT USE THESE CREDENTIALS IN PRODUCTION`
- seeded summary (profile/reference date/tenant DB names/user counts)

Validate central DB records:

- `tenants.is_demo = 1` for seeded tenants
- `demo_dataset_version` and `demo_seeded_at` populated
- `tenant_domains` entries for all three demo domains
- `tenant_databases` current records for all tenants
- `central_settings` keys:
  - `demo.dataset.version`
  - `demo.dataset.reference_date`
  - `demo.dataset.seeded_at`
  - `demo.dataset.profile`

Validate generated documentation:

- `docs/implementation-status.md` updated with profile, reference date, tenant DB names.

## 2.3 Credential output and role filtering

```bash
php artisan demo:credentials --tenant=all
php artisan demo:credentials --tenant=enterprise --role=finance-lead
php artisan demo:credentials --tenant=all --json
```

Confirm each output contains:

- domain
- login URL
- name/email
- role/scope
- effective demo password
- MFA status
- access limitations

## 2.4 Verify command

```bash
php artisan demo:verify
```

Expected checks include:

- central tenant records and domain mappings
- physical tenant DB + metadata identity
- seeded users and password checks
- role/permission assignments
- organization/location counts
- CRM dataset counts
- financial consistency
- KPI day coverage
- published pages and public forms
- resolution cache behavior
- queue dispatch ability

Any failing check is actionable and should be fixed before relying on the dataset.

## 2.5 Refresh operational data

```bash
php artisan demo:refresh-operations --reference-date=2026-08-01 --tenant=all --profile=standard --force
```

Validate:

- tenant users/roles remain present
- operational datasets (CRM/finance/KPI/content/form submissions) are regenerated against the new reference date
- `docs/implementation-status.md` updates reference date/profile

## 2.6 Reset flow

```bash
php artisan demo:reset --tenant=all --force
```

Validate:

- only demo tenants are affected
- cache entries are cleared for demo domains
- central audit event persisted:
  - `demo_audit_events` row with `event_type=demo.reset`
  - `central_settings.demo.audit.last_reset` updated

Optional DB-preserving reset:

```bash
php artisan demo:reset --tenant=all --keep-databases --force
```

---

## 3) Programmatic testing strategy

## 3.1 Fast feedback suite

Run guard/config + command smoke tests first:

```bash
php artisan test tests/Unit/Demo tests/Feature/Demo
```

## 3.2 Integration test matrix (recommended)

Add/maintain feature tests for:

1. **Guard constraints**
   - disallow non-local/testing
   - disallow unsafe central DB name
   - disallow when `DEMO_SEEDING_ENABLED=false`
   - ensure `--force` does not bypass guard

2. **Seeding behavior**
   - central tables populated
   - demo flags set on tenants
   - tenant DBs created and seeded
   - expected profile-scale counts (allow bounded ranges if needed)

3. **Credentials behavior**
   - credentials command returns rows for selected tenant
   - `--role` filtering
   - `--json` schema validation

4. **Verification behavior**
   - `demo:verify` passes after seed
   - fails intentionally when key entities are removed

5. **Refresh behavior**
   - preserves user/role identity
   - rewrites operational data with new reference date

6. **Reset behavior**
   - refuses non-demo tenants
   - removes demo tenants safely
   - respects `--keep-databases`
   - writes reset audit event

## 3.3 CI recommendations

- Run minimal profile for CI smoke tests:
  - `php artisan demo:seed --profile=minimal --force`
  - `php artisan demo:verify`
- Keep `large` profile blocked in ordinary CI and reserve it for explicit performance environments.
- Include at least one negative-guard test job (unsafe env/DB name).

---

## 4) Operational troubleshooting checklist

If seeding fails:

1. Confirm environment guard inputs:
   - `APP_ENV`
   - `DEMO_SEEDING_ENABLED`
   - central DB name pattern
2. Confirm tenant DB credentials resolve.
3. Confirm DB user has create/drop privileges for tenant DB operations.
4. Confirm cache store availability if resolution-cache verification fails.
5. Confirm demo tenant dataset has been provisioned:
   - `php artisan demo:seed --profile=standard --tenant=all --force`
   - or run `php artisan db:seed` when `DEMO_AUTO_SEED_ON_DATABASE_SEEDER=true`.
6. Re-run:
   - `demo:reset --force`
   - `demo:seed --force`
   - `demo:verify`

---

## 5) Expected command lifecycle order

For full reproducible validation:

1. `demo:reset --tenant=all --force`
2. `demo:seed --profile=standard --tenant=all --force`
3. `demo:credentials --tenant=all`
4. `demo:verify`
5. `demo:refresh-operations --tenant=all --force`
6. `demo:verify`

