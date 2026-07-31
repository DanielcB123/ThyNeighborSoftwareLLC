# Current Frontend Audit (WEB-17)

## Scope audited

- `resources/js`
- `resources/css`
- `resources/views`
- `routes`
- Inertia middleware (`app/Http/Middleware/HandleInertiaRequests.php`)
- Shared runtime/navigation/theming primitives
- Existing frontend tests
- `vite.config.js`
- `tailwind.config.js`
- `package.json`

---

## Executive summary

The frontend had a strong architectural base (typed runtime contracts, shell resolver, block registry, navigation adapter, and QA harness), but visual output was still conservative and repetitive for a marketing-first brand direction.

Primary gaps before this issue:

1. **Flat visual hierarchy** across shells and content blocks.
2. **Repetitive content pattern** (hero + generic copy + card grid).
3. **Weak emotional impact** (no high-impact motion moment).
4. **Boilerplate starter residue** (`resources/js/Pages/Welcome.vue`).
5. **Route-level placeholder/filler tone** in several CTAs and supporting copy.
6. **Placeholder dashboard values** (`monthlyRevenueCents: null`).

---

## Classification

## KEEP

- `resources/js/core/runtime/*`  
  Strong typed runtime contract and composable consumption.
- `resources/js/core/navigation/*`  
  Good surface-aware + access-aware navigation architecture.
- `resources/js/core/access/*`  
  Explicit access requirement model is production-oriented.
- `resources/js/layouts/shells/SurfaceShell.vue` and resolver tests  
  Correct central shell selection pattern.
- `app/Http/Middleware/HandleInertiaRequests.php`  
  Clean prop-sharing design for runtime and navigation contracts.
- `vite.config.js` alias and Inertia entry setup  
  Healthy baseline.

## IMPROVE

- `resources/css/theme/tokens.css`  
  Needed stronger token system for mature/editorial visual direction.
- `resources/js/layouts/shells/*.vue`  
  Needed tighter typography, spacing rhythm, and stronger chrome identity.
- Existing block components:  
  - `HeroBlock.vue`
  - `FeatureGridBlock.vue`
  - `RichTextBlock.vue`
  - `CTASectionBlock.vue`  
  Needed better visual hierarchy and less generic card treatment.
- `resources/views/app.blade.php` + `tailwind.config.js`  
  Needed typography update to support design direction.
- Route copy in `routes/web.php`  
  Needed more specific, less generic language and better CTA framing.

## REBUILD

- Marketing content model as rendered from route closures (`routes/web.php`)  
  Existing block payloads were repetitive and not page-distinct enough.
- Home/services experiential centerpiece  
  Needed an intentionally designed interactive stage rather than static repetition.

## REMOVE

- `resources/js/Pages/Welcome.vue`  
  Laravel starter page with stock visuals and unrelated brand language; not referenced by routes.

## RELOCATE

- No mandatory relocations required in this pass.  
  Existing folder topology (`core/*`, `layouts/shells/*`, `Pages/*`) is directionally correct and scalable.

---

## Findings by requested criteria

### Generic AI-looking sections

- Multiple route payloads in `routes/web.php` repeated near-identical “hero + generic claim + CTA” structure.

### Repetitive cards

- Existing block styles produced similar visual weight and spacing across all block types (`resources/js/core/blocks/components/*`).

### Weak copy

- Several phrases were broad/unspecific (“Need a system that can scale…”, “What clients rely on”) and lacked directional specificity.

### Clashing colors / unnecessary gradients

- Not severe, but baseline token palette leaned toward generic SaaS defaults and did not support the requested mature editorial tone.

### Poor spacing / inconsistent radius values

- Mix of ad-hoc rounded utility classes and limited shared radius semantics before token update.

### Unused/duplicate components

- `resources/js/Pages/Welcome.vue` was unused.
- Legacy Breeze auth components remain in use for auth flows; not removed.

### Animation code embedded in page components

- Minimal issue before this pass. However, no dedicated reusable high-impact interactive block existed.

### Hardcoded project data

- Public marketing content remains route-seeded demo content in `routes/web.php` (acceptable for seeded/demo environment, but should move to CMS/config-backed content layer later).

### Unresponsive layouts

- Baseline responsive behavior existed; typography hierarchy and block rhythm needed stronger responsive differentiation.

### Accessibility problems

- Existing a11y test harness present and healthy. No immediate severe violations found in baseline test suite.

### Performance concerns

- No severe frontend bundle anti-patterns found in audited scope.
- Missing reduced-motion-aware “hero spectacle” layer (now addressed in reusable block with reduced motion handling).

### Empty/fake calls to action and placeholder metrics

- Some CTAs read as generic placeholders.
- Dashboard route seeded `monthlyRevenueCents: null` and `activeServiceRequests: 0` created “loading/empty” demo feel rather than intentional snapshot.

### Unsupported claims

- Prior marketing copy included broad claims without clear context; revised toward explicit capability statements.

### Stock illustrations / low-quality icons

- Starter Laravel welcome page contained unrelated stock visuals and was removed.

---

## Implemented remediation in this issue

1. New design-token and shell visual language (editorial + architectural tone).
2. Reworked core block visual system.
3. Added immersive reusable block: `experience-stage` (`ExperienceStageBlock.vue`).
4. Expanded block schema/registry for new block type.
5. Rewrote route-level marketing payloads for stronger page differentiation and clearer CTA logic.
6. Added `pageMeta` support to platform and tenant public pages for distinct page titles/summaries.
7. Removed unused starter `Welcome.vue`.
8. Replaced placeholder tenant dashboard snapshot values with concrete seeded values.

---

## Follow-up recommendations

1. Move route-seeded marketing blocks from `routes/web.php` into a persisted content source (database/CMS + typed backend DTO).
2. Add browser-level visual regression tests for new immersive block under reduced-motion and standard-motion modes.
3. Add analytics instrumentation for CTA clickthrough and section engagement to validate conversion impact.
