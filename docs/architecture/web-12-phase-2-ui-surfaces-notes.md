# WEB-12 Phase 2 UI Surface Implementation Notes

This document records the phase-2 delivery slice focused on straight frontend files and runtime integration over mock-only scaffolding.

## Delivered in this phase

### 1) Critical hardening before UI expansion

- Fixed Blade Vite entry to load `resources/js/app.ts`.
- Hardened URL join semantics to preserve configured base path prefixes (`/admin`, `/preview`, etc.).
- Added runtime composables to consume live Inertia props:
    - `useFrontendRuntime`
    - `useAccessContext`
- Removed stale boot-time URL builder registration; `useUrlBuilder()` now derives URLs from current runtime context.

### 2) App shell architecture

- Added surface shells:
    - `PlatformPublicShell.vue`
    - `TenantPublicShell.vue`
    - `TenantAdminShell.vue`
- Added typed surface resolver:
    - `shellResolver.ts`
- Added `SurfaceShell.vue` to select shell implementation by runtime surface.

### 3) Route surfaces + navigation integration

- Added typed navigation registries:
    - `platformPublicNavigation.ts`
    - `tenantPublicNavigation.ts`
    - `tenantAdminNavigation.ts`
- Shells now consume:
    - `resolveNavigation(...)`
    - `useUrlBuilder()`
    - `useAccessContext()`
- Added route-driven Inertia pages for platform, tenant public, and tenant admin surfaces.

### 4) Theme/token runtime

- Added token baseline stylesheet:
    - `resources/css/theme/tokens.css`
- Added runtime token sanitization + style map generation:
    - `resources/js/core/theme/themeRuntime.ts`
- Tenant shells apply tenant theme tokens as runtime CSS variables.

### 5) Page composition system

- Added typed block contracts and registry:
    - `resources/js/core/blocks/types.ts`
    - `resources/js/core/blocks/blockRegistry.ts`
    - `resources/js/core/blocks/BlockRenderer.vue`
- Added initial production blocks:
    - `HeroBlock.vue`
    - `RichTextBlock.vue`
    - `CTASectionBlock.vue`
    - `FeatureGridBlock.vue`
- Added `UnknownBlock.vue` fallback to fail safely when block type/schema/data is invalid.

### 6) Module-aware tenant dashboard

- Added widget registry:
    - `resources/js/core/dashboard/widgetRegistry.ts`
- Added tenant admin dashboard page:
    - `resources/js/pages/tenant-admin/DashboardPage.vue`
    - `resources/js/Pages/Tenant/AdminDashboard.vue`
- Widgets are filtered by capability/module/permission requirements with explicit loading/empty/data states.

## Verification scope in this phase

- Added tests for:
    - URL path-prefix behavior
    - shell surface resolution
    - block registry safety behavior
    - dashboard widget access filtering and state behavior
    - shell landmarks (component tests)
    - shell + block accessibility checks
    - tenant admin browser-smoke URL behavior

## Remaining risks and follow-up

1. Tenant/domain resolution is still route-simulated for frontend integration; production-grade tenant middleware and domain routing remain required.
2. Backend-generated access contexts and tenant feature contracts must replace route-attribute seeding.
3. Real custom-domain browser E2E and cross-domain auth workflows are still pending.
