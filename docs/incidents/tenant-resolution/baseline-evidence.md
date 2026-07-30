# Baseline Evidence — TENANCY-LOCAL-REGISTRY-001

## Phase Zero Command Capture

Required baseline commands were executed and redirected to:

- `command-output/about.txt`
- `command-output/environment.txt`
- `command-output/config-app.txt`
- `command-output/config-database.txt`
- `command-output/config-tenancy.txt`
- `command-output/config-cache.txt`
- `command-output/config-session.txt`
- `command-output/config-queue.txt`
- `command-output/demo-verify-before.txt`
- `command-output/git-branch.txt`
- `command-output/git-status.txt`
- `command-output/git-diff-stat.txt`

### Command Availability

`php` was unavailable in the execution environment at baseline capture time:

- `php artisan about` -> `php: command not found`
- `php artisan env` -> `php: command not found`
- `php artisan config:show ...` -> `php: command not found`
- `php artisan demo:verify` -> `php: command not found`

No substitutions were made for unavailable commands.

## Baseline Summary (Pre-Remediation)

- Active branch: `main` (captured before feature branch creation)
- Dirty files at baseline: `docs/incidents/` (new incident workspace)
- Laravel environment (effective runtime): unavailable (`php` runtime missing)
- Effective default database connection: unresolved at runtime (command unavailable)
- Effective central connection: unresolved at runtime (command unavailable)
- Effective tenant connection template: unresolved at runtime (command unavailable)
- Effective cache store: unresolved at runtime (command unavailable)
- Effective tenancy resolution cache store: unresolved at runtime (command unavailable)
- Effective platform domains: unresolved at runtime (command unavailable)
- Existing demo verification failures: unresolved at runtime (command unavailable)
- Existing domain-resolution behavior: determined by static code inspection only at this stage
- Existing central registry counts: unresolved at runtime (database audit blocked)
- Config cache status: unresolved at runtime
- Route cache status: unresolved at runtime
- Event cache status: unresolved at runtime

## Baseline Behavioral Risk Identified (Static)

Pre-remediation middleware allowed unknown tenant domains to continue in local/testing environments. This created a false-positive path where unknown hosts could render platform pages instead of failing tenant resolution.
