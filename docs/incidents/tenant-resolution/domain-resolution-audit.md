# Domain Resolution Audit — TENANCY-LOCAL-REGISTRY-001

## Pre-Remediation Risk

`ResolveTenantFromDomain` previously allowed unresolved hosts through in local/testing environments. This enabled false-positive tenant validation outcomes.

## Host Normalization

Resolver and middleware both normalize incoming hosts by:

- lowercasing
- trimming
- removing trailing dot
- applying IDN ASCII conversion when available

## Platform Domain Classification

Remediation tightened platform domain handling:

- platform domains are explicit hostnames
- wildcard-like entries (`*`, `.suffix`) are ignored
- local host shortcuts are controlled by `TENANCY_LOCAL_DOMAINS_ENABLED`

## Unknown Host Behavior

New explicit behavior key:

- `TENANCY_UNKNOWN_HOST_BEHAVIOR=reject` (default)
- Optional override: `passthrough`

Default now fails unresolved tenant hosts with `404`, preventing platform fallback masquerading as tenant success.

## Required Runtime Surface Proof Signal

Middleware now sets `tenantRuntimeSurface` for each request:

- `platform`
- `tenant_public`
- `tenant_admin`
- `tenant_auth`
- `tenant_api`
- `unknown_host`
