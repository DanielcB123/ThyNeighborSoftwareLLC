# Frontend Implementation Manifest

## Scope

This manifest tracks phased frontend delivery for WEB-12 in a repository that started as a Laravel + Inertia starter baseline.

## Phase status

| Phase                                                         | Status      | Delivered in repo                                                                                                                               |
| ------------------------------------------------------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| 1. Frontend foundation baseline                               | Completed   | TypeScript tooling, typed runtime context contract, typed URL builder, access-aware navigation registry, component + accessibility test harness |
| 2. Tenant public website composition system                   | Not started | Pending                                                                                                                                         |
| 3. Tenant admin application shell and module surfaces         | Not started | Pending                                                                                                                                         |
| 4. Page builder block registry and schema versioning          | Not started | Pending                                                                                                                                         |
| 5. Theming system and token orchestration                     | Not started | Pending                                                                                                                                         |
| 6. Enterprise operational dashboards and cross-module widgets | Not started | Pending                                                                                                                                         |
| 7. Browser-level QA matrix and release hardening              | Not started | Pending                                                                                                                                         |

## Implementation rules established in phase 1

1. URL generation is centralized through typed surface methods instead of ad-hoc string concatenation.
2. Tenant-domain URLs require backend-provided tenant context and fail fast if that context is absent.
3. Navigation visibility is evaluated from explicit access requirements using permissions, modules, and capabilities.
4. Inertia shared props now include a typed `frontendRuntime` contract to support platform/tenant surface separation.

## Remaining risks

1. Backend tenant resolution middleware is not yet implemented in this repository, so tenant runtime data is not populated in non-platform requests.
2. The page builder, theme token runtime, and module-level route/page registries are not yet present.
3. Browser end-to-end coverage (real browser automation across domain boundaries) is still pending.
