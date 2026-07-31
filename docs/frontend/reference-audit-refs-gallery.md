# Refs.Gallery Visual Reference Audit (WEB-15)

## Scope

Primary reference reviewed:

- https://www.refs.gallery/tags/3d
- https://www.refs.gallery

Viewport review targets:

- desktop wide
- desktop standard
- tablet
- mobile

This audit captures **interaction and visual principles** to inform an original WeBuildYouThrive implementation. It does **not** replicate Refs.Gallery identity, content, or code.

---

## 3.1 Layout

### Header behavior

- Slim, persistent top navigation with minimal chrome.
- Navigation sits above content rather than competing with it.
- Header rhythm is consistent; no oversized hero nav bars.

### Content margins

- Wide outer gutters on large viewports.
- Tightens progressively on tablet/mobile while preserving breathing room.
- Content width feels editorial: large media surfaces inside controlled margins.

### Grid behavior

- Image-first card grid; text metadata is subordinate.
- Stable rhythm with intentional asymmetry created by content size/sequence, not chaotic offsets.
- Grid density adapts by viewport to maintain visual cadence.

### Number of columns by viewport

- Desktop wide: high-density multi-column gallery behavior.
- Desktop standard: fewer columns, still image-first.
- Tablet: reduced columns with retained card prominence.
- Mobile: single-column stack with strong vertical pacing.

### Thumbnail proportions

- Dominantly rectangular surfaces with occasional variety.
- Ratios prioritize screenshot legibility and visual impact.

### Project-title placement

- Titles are compact metadata beneath or adjacent to media.
- Typography never overpowers imagery.

### Relationship between text and imagery

- Imagery leads, text supports.
- Labels/metadata are concise and restrained.

### Section spacing

- Consistent vertical rhythm.
- Larger spacing used to separate narrative bands, not every card.

### Use of borders

- Fine-line separators and light card boundaries.
- Borders provide structure without looking heavy or boxed-in.

### Use of whitespace

- Significant negative space around major sections.
- Dense card zones balanced by calm breathing areas.

### Page density

- High enough to feel curated and rich, not sparse.
- Dense content is still readable because hierarchy is strict.

### Footer behavior

- Low-noise footer treatment.
- Utility and credibility information are present but visually subordinate.

---

## 3.2 Typography

- Headline scale is large and architectural at key moments.
- Body copy remains practical and readable (no compressed novelty text).
- Labels/microcopy are compact, often uppercase or semibold for wayfinding.
- Clear weight hierarchy: display > heading > body > metadata.
- Tight letter spacing on large display lines; neutral tracking on body.
- Comfortable line-height in body text; denser in labels.
- Uppercase is used strategically, not excessively.
- Editorial type for storytelling, utilitarian type for interface/navigation.
- Titles remain subordinate to imagery in gallery contexts.

---

## 3.3 Interactions

- Card hover behavior is subtle: light lift/scale/translation and reveal cues.
- Pointer response is immediate but restrained.
- Image movement favors controlled transforms over dramatic parallax noise.
- Transition timing is short-to-medium with smooth easing.
- Scroll behavior feels continuous and stable; no jittery reveal spam.
- Navigation transitions are clean and predictable.
- Loading experience favors progressive content reveal over spinner-heavy gating.
- Page transitions do not distract from content hierarchy.
- Filter/shuffle interactions are concise utility controls.
- Cursor-dependent behaviors enhance, but never block, usability.

---

## 3.4 Visual language

- Backgrounds are restrained and mostly neutral to amplify media.
- Images are clearly framed, often edge-to-edge inside cards/tiles.
- Corner radii are controlled and consistent (not overly rounded everywhere).
- Shadows are minimal and tactical.
- Contrast is disciplined with readability preserved.
- Accent color usage is sparse and intentional.
- Texture is subtle (grain/material hints) rather than noisy effects.
- Motion restraint is a defining quality: confidence over spectacle overload.
- Repetition in spacing and typography creates trust and polish.
- Intentional asymmetry appears in pacing/composition, not random placement.

---

## 3.5 Adopt, adapt, and avoid

### Adopt

- Image-first editorial layout.
- Restrained navigation chrome.
- Strong visual hierarchy with large media surfaces.
- Consistent spacing rhythm and thin-border geometry.
- Motion quality that is subtle, responsive, and polished.

### Adapt

- Convert inspiration-card feeling into **business capability showcase** blocks.
- Pair editorial presentation with conversion-focused CTAs and trust content.
- Use category framing relevant to buyers (websites, apps, tenant platforms, operations).
- Introduce a focused 3D narrative layer (hero + one or two interaction moments), not site-wide effect overload.
- Keep interaction sophistication while preserving accessibility, keyboard paths, and performance budgets.

### Avoid

- Copying exact grid composition, spacing constants, or navigation structure.
- Reusing any Refs.Gallery branding, content, or proprietary imagery.
- Turning WeBuildYouThrive into a gallery-only experience.
- Hiding service credibility and conversion paths behind visual experimentation.
- Overusing animation, blur, glow, or trend-driven gradient aesthetics.

---

## Implementation implication summary

For WEB-15, the target is:

- **80% editorial restraint / 20% technical spectacle**
- A constrained shell with selective high-impact 3D and motion moments
- Strong conversion architecture (clear pathways, trust signals, service framing)
- Fast, responsive, and accessible presentation across all target viewports
