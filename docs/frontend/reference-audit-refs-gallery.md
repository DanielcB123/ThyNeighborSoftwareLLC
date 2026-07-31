# Reference Audit: Refs.Gallery 3D Collection and Comparable Studio Sites

## Scope and evidence sources

This audit was compiled from:

1. Direct live URL inspection of:
   - `https://www.refs.gallery/tags/3d` (Cloudflare gate content returned)
   - `https://lusion.co/`
   - `https://wonderlandams.com/`
   - `https://activetheory.net/` (JS gate content returned)
   - `https://igloo.inc/` (request timed out)
   - `https://solk.com/`
   - `https://ultra.studio/`
2. Supplemental public case studies and editorial breakdowns used to verify interaction and implementation details where direct crawl output was limited:
   - Awwwards case studies (Wonderland, Igloo)
   - Communication Arts web picks (Active Theory, SOLK)
   - One Page Love overview (Studio Ultra)

Because several reference sites are heavily JS/WebGL driven or bot-protected, behavior notes below focus on signals consistently documented across first-party copy and third-party technical case studies.

---

## 4.1 Page structure

### Desktop (>=1200px)

- **Header height and behavior:** Generally compact, persistent, and utility-light. Brand mark + minimal nav + occasional utility CTA. Sticky behavior is common.
- **Navigation density:** Low. Most references avoid dense IA in top chrome; they prioritize one-level labels and direct project access.
- **Main content margins:** Wide outer gutters, high whitespace confidence, and full-bleed media moments for hero/case transitions.
- **Grid columns:** Editorial grids are mostly 12-column aligned but used flexibly. Cards can break strict symmetry while preserving rhythm.
- **Card proportions:** Tall and cinematic (often near 4:5, 9:16, or viewport-responsive hybrids), mixed with occasional wide feature spans.
- **Media proportions:** Dominant and immersive. Media is the primary canvas, labels are secondary overlays.
- **Project-title placement:** Typically lower-left or directly adjacent to media with concise taxonomy labels.
- **Label hierarchy:** Small uppercase or micro-label metadata above/beside larger title copy.
- **Vertical rhythm:** Alternates dense visual clusters with generous resting whitespace.
- **Footer composition:** Minimal but intentional; often contact + social + legal in simple columns.
- **Loading pattern:** Intro transitions, staged asset loading, and deferred heavy media are common.
- **Pagination/continuation:** Infinite or long-scroll flow preferred over explicit pagination.
- **Chrome vs media relationship:** Interface chrome is restrained; project media carries visual identity.

### Tablet (768px-1199px)

- **Header/nav:** Preserves minimal chrome; nav often compresses or shifts to compact trigger while maintaining direct project access.
- **Grid behavior:** Multi-column grids collapse to fewer columns with selective featured items still spanning wider areas.
- **Media treatment:** Retains large visual priority while reducing simultaneous motion complexity.
- **Pacing:** Scroll storytelling remains intact, but transitions are simplified for stability/performance.

### Mobile (<768px)

- **Header/nav:** Ultra-lean top chrome, often with simple menu affordance and prominent back-to-work pathways.
- **Layout:** Single-column editorial flow with strong spacing and oversized media blocks.
- **Media strategy:** Poster-first and delayed/optional rich motion on weaker devices.
- **Project flow:** Sequential and clear; no reliance on tiny hit targets.
- **Content density:** Fewer words, stronger hierarchy, and quick wayfinding.

---

## 4.2 Typography

- **Relative display scale:** Large and bold display headings (often viewport-aware), especially in hero/section transitions.
- **Project-title scale:** Medium-large; clear priority over descriptive copy.
- **Body scale:** Controlled, readable, generally conservative compared with display scale.
- **Label scale:** Small micro-typography for categories/taxonomy.
- **Weight usage:** Limited but deliberate range (regular/medium/semibold/bold).
- **Letter spacing:** Slight tracking increase for utility labels and uppercase metadata.
- **Line height:** Tight for display headlines; more open for editorial/body text.
- **Uppercase usage:** Common for section labels and metadata; avoided for long body copy.
- **Typographic contrast:** Strong contrast between editorial display text and utility/meta text.
- **Editorial vs utility text:** Editorial text sets mood and narrative, utility text anchors scanning and navigation.

---

## 4.3 Motion and interaction

- **Card hover behavior:** Subtle scale/tilt/overlay shifts; hover should preview depth, not distract.
- **Image scaling:** Gentle parallax and zoom transitions on hover/scroll.
- **Pointer response:** Interactive scenes frequently react to pointer position or velocity.
- **Media transition:** Scroll-driven transitions blend video, 3D, and typography with controlled easing.
- **Scroll behavior:** Narrative scroll is central; section transitions are choreographed and often linked to visual state.
- **Navigation movement:** Sticky nav with lightweight transition states is common.
- **Page transition behavior:** Many references use transition overlays, intro ramps, or motion continuity between sections.
- **Cursor treatment:** Custom cursor patterns appear in some references, but not required for clarity.
- **Loading treatment:** Loading state is treated as part of brand expression.
- **Timing:** Generally medium duration with confident pacing; avoids jittery micro-bounces.
- **Easing:** Custom cubic-bezier curves or spring-like easing; physically plausible, not gimmicky.
- **Interaction feedback:** Clear, immediate, and tactile, with consistent response language.

---

## 4.4 Visual restraint

These references avoid visual cheapness through strict restraint rules:

1. **Limited decoration:** Effects are concentrated where they add narrative value; most UI remains clean.
2. **Minimal color noise:** Palettes are tightly controlled (often monochrome + one accent family).
3. **Controlled borders/shadows:** Border usage is sparse and intentional; depth is primarily from media and motion.
4. **Reduced card chrome:** Project cards avoid heavy UI wrappers and let media lead.
5. **Hierarchy clarity:** One dominant focal point per viewport region.
6. **Whitespace discipline:** Negative space is used as pacing and hierarchy, not as filler.
7. **Consistent grammar:** Repeated interaction and spacing patterns build trust.
8. **Stillness vs movement balance:** Calm baseline with selective high-impact motion.

---

## 4.5 ADOPT / ADAPT / AVOID

## ADOPT

- Image-first project presentation
- Large visual canvases with restrained text overlays
- Tight, minimal navigation
- Strong editorial typography hierarchy
- Consistent grid rhythm with selective asymmetry
- Motion as identity, not decoration
- Performance-aware progressive enhancement
- Reduced-motion accommodations as first-class behavior

## ADAPT

- Convert gallery-like project showcases into capability proof for real client outcomes
- Connect aesthetics to business credibility (security, architecture, operational depth)
- Build clear project-to-contact conversion pathways
- Keep immersive sections but ground them with concrete service language
- Use 3D selectively to elevate key moments without overwhelming readability
- Maintain one coherent interaction grammar across home/services/industries/contact

## AVOID

- Copying protected source code, assets, or composition one-for-one
- Turning the site into an inspiration scrapbook with no business narrative
- Overloading every section with motion effects
- Hiding core information behind obscure interactions
- Creating inaccessible canvas-only content
- Shipping interactions without reduced-motion fallback
- Using decorative gradients/particles with no semantic or compositional function
