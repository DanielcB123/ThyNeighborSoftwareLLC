# Current Frontend Audit (Pre-Implementation) — WEB-15

## Scope reviewed

- `resources/js`
- `resources/css`
- `resources/views`
- `routes`
- Inertia middleware + shared props
- frontend navigation + shells + blocks
- existing tests
- `vite.config.js`
- `tailwind.config.js`
- `package.json`

---

## Architecture snapshot

The current frontend has a good **runtime/surface foundation** but a weak **public-facing visual layer**:

- Strong: typed block registry, surface shell resolver, runtime/context sharing, URL builder, navigation contracts.
- Weak: public marketing presentation remains generic and component-library-like, with hardcoded route content and low editorial impact.

---

## Classification

## Keep

- `resources/js/core/runtime/*`  
  Solid shared runtime contract across platform and tenant surfaces.
- `resources/js/core/url/*`  
  Typed URL builder is reliable and prevents cross-surface linking mistakes.
- `resources/js/core/access/*`  
  Capability/permission filtering model should remain.
- `resources/js/core/blocks/blockRegistry.ts`  
  Schema-validated composition is a strong extension point.
- `resources/js/layouts/shells/SurfaceShell.vue` + `shellResolver.ts`  
  Surface selection approach is scalable.
- `app/Http/Middleware/HandleInertiaRequests.php`  
  Shared prop structure is useful and coherent.
- `app/Support/Frontend/Navigation/ServerDrivenNavigationBuilder.php`  
  Server-driven navigation with access checks is correct direction.
- test harness (`tests/Feature/*`, `resources/js/**/__tests__/*`)  
  Existing test setup should be preserved and expanded.

## Improve

- `resources/css/theme/tokens.css`  
  Needs semantic token model for cinematic/editorial direction (current palette is generic SaaS blue).
- `resources/js/layouts/shells/PlatformPublicShell.vue`  
  Header/footer and hero framing are too plain.
- `resources/js/layouts/shells/TenantPublicShell.vue`  
  Better than base, but still utilitarian and low-impact.
- `resources/js/core/navigation/components/NavigationMenu.ts`  
  Functional but too primitive (no mobile strategy, no stronger interaction language).
- `resources/js/Pages/Platform/Home.vue` and `resources/js/Pages/Tenant/PublicHome.vue`  
  Page metadata and framing are static and repetitive.
- block components in `resources/js/core/blocks/components/*.vue`  
  Current card treatment is repetitive and visually shallow.
- `resources/views/app.blade.php`  
  Lacks skip link and stronger baseline document semantics for accessibility/perf polish.

## Rebuild

- Public marketing visual system end-to-end:
    - color/typography/motion tokens
    - shell chrome
    - block presentation
    - conversion band composition
    - selective 3D experience layer

## Remove

- `resources/js/Pages/Welcome.vue`  
  Unused starter artifact.
- `resources/js/Pages/Dashboard.vue`  
  Unused starter dashboard artifact.
- Generic `wb-card` everywhere styling pattern  
  Conflicts with desired editorial direction and creates repetitive panel-heavy layout.

## Relocate

- Marketing content currently hardcoded in `routes/web.php` closures should eventually move to:
    - dedicated marketing content provider (controller/service)
    - or CMS-backed/content config files
  For this implementation pass, route-level props can remain temporarily but structure should be made more explicit and reusable.

---

## Problems found

## Generic AI-looking sections / repetitive cards

- Hero, rich-text, feature-grid, and CTA are all rendered with nearly identical white cards and similar spacing.
- Excessive repetition of soft rounded-card motif causes sameness and weak differentiation.

## Weak copy / placeholder tone

- Public copy reads technically correct but lacks high-confidence creative/strategic articulation.
- Several sections feel like implementation notes instead of buyer-facing value framing.

## Color and visual hierarchy issues

- Token palette is generic blue/sky with low distinction.
- Accents are over-reused; no controlled visual pacing across sections.

## Spacing/radius inconsistency risk

- Radius and surface treatment are globally simplistic; hierarchy depends mostly on heading size.
- Intra-section spacing does not create strong editorial cadence.

## Component quality / duplication

- Two UI eras coexist:
    - Breeze-oriented `resources/js/Layouts` + `resources/js/Components`
    - new shell/block architecture under `resources/js/layouts` and `resources/js/core`
- Navigation definitions are partly duplicated between server defaults and client fallbacks.

## Animation and interaction debt

- Minimal interactive richness on public pages.
- No high-quality kinetic/3D showcase moment despite issue goals.

## Hardcoded project data / demo values

- `routes/web.php` contains large inline content arrays and demo snapshot metrics.
- Public pages are rigid and not content-managed.

## Responsiveness / accessibility concerns

- Navigation lacks robust small-screen behavior.
- Missing skip link in root Blade shell.
- Anchor-only navigation/CTA behavior limits richer Inertia transitions and accessibility patterns.

## Performance concerns

- External font dependency in `resources/views/app.blade.php`.
- No controlled 3D loading strategy (currently no 3D, but redesign must be budget-aware).
- No explicit reduced-motion accommodation in visual interaction layer.

## Conversion concerns

- Existing CTA strategy is too flat and repetitive.
- Trust and capability framing are present but not emotionally compelling.

---

## Implementation decision from this audit

1. Keep runtime and schema foundations.
2. Rebuild visual shell and block presentation.
3. Introduce a constrained but high-quality 3D interaction in hero contexts.
4. Implement semantic tokens and fluid typography.
5. Improve responsive navigation and accessibility baseline.
6. Remove dead starter pages that conflict with the new direction.
