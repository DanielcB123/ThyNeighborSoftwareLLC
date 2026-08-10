# Phase 0 Existing System Audit (Central Platform)

## Scope

This audit covers the entities listed in Linear issue `WEB-20` and compares them to the current repository implementation.

Classification values used:

- `REUSE_AS_IS`
- `EXTEND`
- `RENAME`
- `REPLACE`
- `DEFER`
- `REMOVE_IF_UNUSED`

Audit date: 2026-08-02

---

## Evidence reviewed

- Migrations:
  - `database/migrations/0001_01_01_000000_create_users_table.php`
  - `database/migrations/2026_07_30_132500_create_central_tenant_registry_tables.php`
  - `database/migrations/2026_07_30_141500_add_demo_columns_to_tenants_and_create_central_settings_table.php`
  - `database/migrations/2026_07_30_160500_create_demo_catalog_and_access_tables.php`
- Models:
  - `app/Models/User.php`
  - `app/Models/Central/Tenant.php`
  - `app/Models/Central/TenantDomain.php`
  - `app/Models/Central/TenantDatabase.php`
- Services and seeders:
  - `app/Tenancy/CentralTenantDatabaseResolver.php`
  - `app/Demo/Services/DemoTenantProvisioner.php`
  - `database/seeders/Central/PlatformUserSeeder.php`
- Tests:
  - `tests/Unit/Tenancy/CentralTenantDatabaseResolverTest.php`
  - `tests/Feature/Tenancy/ResolveTenantFromDomainMiddlewareTest.php`
  - `tests/Feature/Auth/AuthenticationTest.php`
  - `tests/Feature/Auth/RegistrationTest.php`
  - `tests/Feature/Shared/HasPublicIdTest.php`

---

## Entity audit matrix

| Requested entity | Current table | Current model | Current purpose | Current relationships | Current statuses | Code exists | Tests exist | Production-like data present | Overlap with proposed onboarding model | Classification | Notes / action |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `onboarding_sessions` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | High (future `DiscoverySession`) | `DEFER` | Create as new central prospect-phase entity in Phase 2/3 design. |
| `onboarding_answers` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | High (future `DiscoveryResponse`) | `DEFER` | Create as versioned answer store with audit metadata. |
| `client_projects` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Medium (post-discovery execution domain) | `DEFER` | Keep out of Phase 0-3; define only after conversion gate. |
| `project_requirements` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Medium (future requirement candidates) | `DEFER` | Introduce after discovery model is approved. |
| `project_milestones` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low (delivery phase concern) | `DEFER` | Out of scope for pre-tenant onboarding phases. |
| `project_deliverables` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low (delivery phase concern) | `DEFER` | Out of scope for pre-tenant onboarding phases. |
| `proposals` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Medium (commercial phase) | `DEFER` | Keep human approval/commercial commitments in later phases. |
| `proposal_items` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Medium | `DEFER` | Introduce only with proposal workflow. |
| `proposal_acceptances` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Medium | `DEFER` | Introduce only with legal/commercial acceptance workflow. |
| `platform_customers` | Not found | Not found | Not implemented as central platform table | N/A | N/A | No | No | No | Low for Phase 0-3 | `DEFER` | Demo tenant data includes customer-like records, but not as central `platform_customers`. |
| `platform_subscriptions` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low for Phase 0-3 | `DEFER` | Add only when commercial lifecycle begins. |
| `platform_invoices` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low for Phase 0-3 | `DEFER` | Add only when billing system is designed. |
| `platform_payments` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low for Phase 0-3 | `DEFER` | Add only when billing system is designed. |
| `provisioning_runs` | Not found (related state fields exist) | Not found | Not implemented as dedicated run log table | Related fields: `tenants.provisioning_state`, `tenant_databases.provisioning_state`; provisioning logic in `DemoTenantProvisioner` | String states used: `ready` (default/current demo path) | Partial | No dedicated tests | Demo-only provisioning artifacts exist | Low for Phase 0-3 | `EXTEND` | When provisioning lifecycle is formalized, introduce explicit run/step entities instead of implicit fields only. |
| `provisioning_steps` | Not found (related state fields exist) | Not found | Not implemented as dedicated step table | Same related fields as above | String states used: `ready` | Partial | No dedicated tests | Demo-only provisioning artifacts exist | Low for Phase 0-3 | `EXTEND` | Add step-level records only when production provisioning orchestration is defined. |
| `tenants` | `tenants` | `App\Models\Central\Tenant` | Central tenant registry for active provisioned tenants | `hasMany` to `tenant_domains`, `tenant_databases`; soft deletes | `status` string (queried as `active`), `provisioning_state`, `migration_state`, `health_state`; demo flag columns | Yes | Yes (indirect via tenancy resolver/middleware tests) | Yes (synthetic but production-like seeded tenant records) | Medium (must remain separate from prospect entities) | `REUSE_AS_IS` | Keep tenant boundary intact; do not create tenant records during Phase 0-3 onboarding. |
| `tenant_profiles` | Not found | Not found | Not implemented | N/A | N/A | No | No | No | Low | `DEFER` | Define only if tenant profile split is required after conversion. |
| `tenant_domains` | `tenant_domains` | `App\Models\Central\TenantDomain` | Domain-to-tenant mapping for runtime resolution | `belongsTo` tenant | `is_primary` bool, `is_active` bool, `verified_at` timestamp | Yes | Yes (resolver and middleware exercise this table path) | Yes (synthetic but production-like seeded domain records) | Low for Phase 0-3 | `REUSE_AS_IS` | Keep for tenant runtime; not used for prospect workspace URLs. |
| `tenant_databases` | `tenant_databases` | `App\Models\Central\TenantDatabase` | Tenant database connection registry | `belongsTo` tenant, `belongsTo` `database_clusters` | `status` string (queried as `active`), `is_current` bool, `provisioning_state`, `migration_state`, `health_state` | Yes | Yes (resolver/middleware via tenancy path) | Yes (synthetic but production-like seeded DB metadata) | Low for Phase 0-3 | `REUSE_AS_IS` | Keep for post-conversion/provisioned tenants only. |
| `platform_users` | No dedicated `platform_users` table; represented by `users` + `platform_user_roles` | `App\Models\User` | Central authenticated user identity used for platform access; role assignment via pivot | `platform_user_roles.user_id -> users.id` | No explicit status enum; active state implied by auth + soft delete + email verification | Yes | Yes (auth and public ID tests) | Yes (synthetic platform demo users seeded) | Medium (internal actor identity for intake/admin roles) | `RENAME` | Treat logical `platform_users` as existing `users` model; document terminology mapping to avoid duplicate identity tables. |

---

## Canonical mapping recommendation

Based on current repository state and the Phase 0-3 blueprint:

### Direct mappings

- Logical `platform_users` -> existing `users` (+ `platform_user_roles`) **(RENAME at terminology layer, not DB rename)**
- Existing `tenants` -> `tenants` (provisioned/runtime tenants only; never prospects)
- Existing `tenant_domains` -> `tenant_domains` (runtime domain routing only)
- Existing `tenant_databases` -> `tenant_databases` (runtime tenant DB registry only)

### New entities to introduce (currently absent)

- `Inquiry`
- `Lead`
- `LeadContact`
- `Prospect`
- `ProspectWorkspace`
- `DiscoveryTemplate`
- `DiscoveryTemplateVersion` (immutable published versions)
- `DiscoverySession` (or `ProspectOnboardingSession`)
- `DiscoverySection`
- `DiscoveryResponse` (versioned answer history)
- `ClarificationRequest`
- `RequirementCandidate`
- `ProspectStakeholder`
- `ProjectTrack`
- Central onboarding timeline/audit event entity

### Boundary rules to enforce in implementation

1. Prospect lifecycle must stay on central platform routes and storage through `DISCOVERY_COMPLETE`.
2. `tenants`, `tenant_domains`, and `tenant_databases` are post-acceptance provisioning artifacts and must not be created during inquiry/discovery phases.
3. Commercial entities (`proposals`, `proposal_items`, `proposal_acceptances`, `platform_subscriptions`, `platform_invoices`, `platform_payments`) remain deferred until post-discovery phases.
4. Provisioning run/step observability should move from implicit status strings to explicit run-step tables when provisioning automation is formalized.

---

## Immediate implementation planning implications

- Phase 1-3 implementation should be designed as a **new central onboarding bounded context**, not an extension of tenant runtime tables.
- Existing tenant and platform identity structures are adequate to support internal actors and later conversion handoff.
- The largest gap is the complete absence of onboarding/discovery entities; this is expected and aligns with the blueprint's “design before implementation” directive.
