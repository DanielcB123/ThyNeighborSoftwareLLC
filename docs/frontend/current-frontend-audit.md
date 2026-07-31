# Current Frontend Audit (Pre-Expansion Baseline)

## Scope reviewed

- `resources/js`
- `resources/css`
- `resources/views`
- `routes/web.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Support/Frontend/Navigation/ServerDrivenNavigationBuilder.php`
- Frontend tests under `tests/Feature` and `resources/js/**/__tests__`
- Build config (`vite.config.js`, `tailwind.config.js`, `package.json`)

---

## Executive assessment

The current public frontend is a **solid engineering scaffold** (typed runtime contracts, navigation/access filtering, shell resolver, block registry), but the visible marketing experience is still **generic and utility-oriented**. It currently demonstrates architecture discipline more than premium creative execution.

In short:

- **System quality:** good
- **Design ambition:** low
- **Visual identity:** weak
- **Narrative conversion quality:** weak
- **3D/immersive expression:** absent

---

## Classification matrix

## Keep

1. `app/Http/Middleware/HandleInertiaRequests.php`
   - Strong shared-props contract for runtime, auth/access, and navigation.
2. `app/Support/Frontend/Navigation/ServerDrivenNavigationBuilder.php`
   - Explicit access constraints and gate support are correct and maintainable.
3. `resources/js/core/url/urlBuilder.ts`
   - Well-typed URL generation with surface boundaries and validation.
4. `resources/js/core/access/*`
   - Access model is explicit and reusable.
5. `resources/js/layouts/shells/SurfaceShell.vue` + resolver
   - Correct abstraction for surface-based shell composition.
6. Existing frontend test harness (Vitest + a11y and unit coverage)
   - Good baseline safety net for iterative redesign.

## Improve

1. `resources/js/layouts/shells/PlatformPublicShell.vue`
   - Structurally clean but visually generic; needs stronger identity and editorial rhythm.
2. `resources/css/theme/tokens.css`
   - Useful token baseline but too narrow for cinematic visual language.
3. `resources/js/core/navigation/components/NavigationMenu.ts`
   - Semantically fine; visual styling should support premium restrained navigation.
4. `resources/views/app.blade.php`
   - Default font stack (`Figtree`) does not support intended high-end editorial tone.

## Rebuild

1. `resources/js/Pages/Platform/Home.vue`
   - Current page is a thin wrapper around generic blocks; lacks distinctive art direction.
2. Route-seeded block content in `routes/web.php` for `/`, `/services`, `/industries`, `/contact`
   - Copy and structure are repetitive, low-impact, and not project-first.
3. Block visual components:
   - `HeroBlock.vue`
   - `FeatureGridBlock.vue`
   - `RichTextBlock.vue`
   - `CTASectionBlock.vue`
   These are functionally fine but visually template-like and non-cinematic.

## Remove

1. Generic marketing language that reads as placeholder architecture copy rather than confident studio positioning.
2. Dependence on card chrome (`wb-card`, `wb-pill`) as the dominant visual pattern for public pages.
3. Default Laravel `resources/js/Pages/Welcome.vue` from public-facing pathways (retain file only if needed for framework baseline).

## Relocate

1. Platform marketing data currently embedded as large arrays in route closures.
   - Move to explicit scenario/page-model structures to improve maintainability and reduce route noise.
2. Animation logic currently absent from dedicated composables/components.
   - Introduce motion components with lifecycle cleanup and reduced-motion support.

---

## Problem inventory against issue requirements

### Generic AI-looking sections / repetitive cards

- Public pages are built from a small set of generic card blocks, producing repetitive visual output across routes.

### Weak copy / minimum viable messaging

- Much copy is architecture-descriptive rather than outcome-driven.
- Emotional and business persuasion layers are underdeveloped.

### Inconsistent design language and hierarchy

- Visual hierarchy is shallow (similar card treatment everywhere).
- Typography lacks strong editorial scaling contrast.

### Missing immersive layer

- No WebGL, no spatial interactions, and no premium motion identity.

### Motion architecture and reduced-motion behavior

- Minimal motion in marketing surfaces; no explicit immersive interaction fallback strategy.

### Content and conversion quality

- CTA pathways exist but are not narratively staged through proof-driven project sections.
- Contact intent is present but weakly integrated into journey pacing.

### Accessibility and semantics

- Baseline semantics are mostly good.
- Risk area: future immersive components must preserve keyboard/assistive readability and avoid canvas-only meaning.

### Performance and lifecycle risk

- Current pages are light and low-risk.
- Introducing immersive 3D requires strict cleanup, resize handling, and conditional animation.

---

## Decisions for expansion implementation

1. Preserve typed runtime/navigation/access foundations.
2. Rebuild platform public pages into a cohesive, project-first editorial experience.
3. Add a controlled 3D interaction layer with progressive enhancement and reduced-motion fallback.
4. Keep tenant/admin surfaces stable while improving only public marketing experience in this pass.
5. Move from generic blocks to purpose-built marketing sections with clearer naming and ownership.

---

## Verification targets after implementation

1. Public pages visibly shift from utility cards to cinematic editorial layout.
2. Navigation remains accessible and stable across surfaces.
3. Reduced-motion users receive a complete, non-animated experience.
4. Existing runtime/navigation tests stay green (or are updated only where intentional behavior changed).
5. Build and lint remain clean.
