# Frontend Implementation Manifest

## Scope

This manifest tracks phased frontend delivery for WEB-12 in a repository that started as a Laravel + Inertia starter baseline.

## Phase status

| Phase                                                         | Status      | Delivered in repo                                                                                                                                                                                                   |
| ------------------------------------------------------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1. Frontend foundation baseline                               | Completed   | TypeScript tooling, typed runtime context contract, typed URL builder, access-aware navigation registry, component + accessibility test harness                                                                     |
| 2. Tenant public website composition system                   | In progress | Surface shell resolver, platform and tenant public shells, tenant public block-composed page, typed navigation registries, and route-level tenant runtime simulation for frontend integration                     |
| 3. Tenant admin application shell and module surfaces         | In progress | Tenant admin shell, tenant admin page surface, typed access composable wiring, admin navigation integration, and tenant admin URL prefix hardening                                                                   |
| 4. Page builder block registry and schema versioning          | In progress | Typed block contracts, registry-based block resolution, safe unknown-block fallback renderer, and initial production-ready block components (`hero`, `rich-text`, `cta-section`, `feature-grid`)               |
| 5. Theming system and token orchestration                     | In progress | CSS token baseline (`resources/css/theme/tokens.css`), tenant token sanitization/runtime style application, and shell-level theme injection                                                                         |
| 6. Enterprise operational dashboards and cross-module widgets | In progress | Capability-aware tenant admin widget registry with loading/empty/data states and frontend filtering by permissions/modules/capabilities                                                                             |
| 7. Browser-level QA matrix and release hardening              | In progress | Vitest browser-smoke tests expanded for tenant admin domain/path behavior; full multi-domain browser automation remains pending                                                                                    |

## Implementation rules established in phase 1

1. URL generation is centralized through typed surface methods instead of ad-hoc string concatenation.
2. Tenant-domain URLs require backend-provided tenant context and fail fast if that context is absent.
3. Navigation visibility is evaluated from explicit access requirements using permissions, modules, and capabilities.
4. Inertia shared props include typed `frontendRuntime` and typed auth/access contracts.
5. Shell selection is surface-driven through a typed resolver (`resolveShellBySurface`), not page-level string checks.
6. Content pages use a typed block registry with schema-version validation and safe unknown block fallback behavior.

## Remaining risks

1. Backend tenant resolution middleware is not yet implemented in this repository; tenant route context is currently simulated in route closures for frontend integration.
2. Platform/tenant route groups by custom domain and middleware are still missing; the current route wiring demonstrates frontend integration but does not enforce production tenant boundaries.
3. Browser end-to-end coverage (real browser automation across custom-domain boundaries) is still pending.
4. Backend access payload generation is currently seeded from request attributes for demo routes and must be replaced with policy-backed permission resolution.
