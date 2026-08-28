# design-tokens Specification

## Purpose
TBD - created by archiving change chapter-theme-foundation. Update Purpose after archive.
## Requirements
### Requirement: Brand token custom properties
`global/_global.scss` SHALL define `:root` CSS custom properties for the DSA palette — red `#dd1111`, dark red `#7c0909`, cream `#fff5e5`, black, white — plus spacing/max-width tokens, and map brand colors onto webawesome `--wa-color-brand-*` variables so `wa-*` components inherit the brand.

#### Scenario: Tokens applied globally
- **WHEN** the compiled stylesheet loads
- **THEN** the page background is cream and components reference `var(--color-*)` tokens

### Requirement: Global baseline styles
The theme SHALL ship a light reset (border-box sizing, zero body margin, fluid images) and a `.wrapper` layout utility (max-width, centered, inline padding) in `_global.scss`.

#### Scenario: Wrapper constrains content
- **WHEN** a section uses `.wrapper`
- **THEN** its content is centered with a max width and side padding

### Requirement: Button system
`ui/_button.scss` SHALL provide a `.btn` class usable on anchors (red fill, uppercase Montserrat, hover state) and brand overrides for `wa-button`. All SCSS partials in use (`button`, `hero`, header partials, `home`) SHALL be registered in the `app.scss` `@use` manifest.

#### Scenario: Anchor CTA styled
- **WHEN** an `<a class="btn">` renders
- **THEN** it appears as a red uppercase button with hover feedback

#### Scenario: Manifest complete
- **WHEN** `npm run build` compiles SCSS
- **THEN** all populated partials are included and stylelint passes


### Requirement: Brand palette tokens (v3 values)
`src/css/tailwind.css` SHALL carry the v3 palette on the theme's semantic token names — no per-generation namespace — consumable as Tailwind utilities: `--color-brand-red` / `--color-red` `#DC1520` (primary; accessibility-adjusted from the AI file's `#EB2028` and SHALL NOT be reverted), `--color-brand-red-deep` / `--color-red-hover` `#B5121B`, `--color-orange` `#FF4100` (+ `-hover` `#E63A00`), `--color-yellow` `#FFC800`, `--color-green` `#719655`, `--color-green-dark` `#5F813A`, `--color-green-panel` `#668043`, `--color-flame` `#F75414`, `--color-cream` `#F7F5F1`, `--color-off-white` `#F5F2EC`, `--color-ink` `#231F20`, `--color-ink-footer` `#211E1E`, `--color-border-muted` `#B9B3A9`, `--color-stripe-a/b` `#F5F1EA`/`#ECE6DA`. The shadcn semantic variables (`--primary`, `--foreground`, `--background`, `--ring`, …) and the inline critical background in `views/html-header.twig` SHALL mirror these values. Interior pages SHALL inherit the palette through these tokens. Orange SHALL NOT be used for small body text (contrast ~3.5:1 — large/bold uppercase only).

#### Scenario: Utilities available
- **WHEN** the stylesheet compiles
- **THEN** `bg-brand-red`, `text-red`, `text-orange`, `bg-cream`, `text-ink`, … resolve to the exact hex values above and no `v3-` prefixed token or utility exists

#### Scenario: Red stays adjusted
- **WHEN** the header/hero render
- **THEN** the red is `#DC1520` (5.0:1 on white), not `#EB2028`

### Requirement: Typography faces
The theme SHALL self-host the brand faces under `static/fonts/{bowlby-one,manifold-dsa,special-season}/` with `@font-face` (`font-display: swap`) and expose them as `--font-display` (Bowlby One 400 — section headings, all nav links and pill buttons), `--font-sans` (Manifold DSA: Heavy 800 eyebrows/arrow links, Bold 700 body emphasis/event titles, DemiBold 600 hero subhead/dropdown items, Medium 500 default body/footer links) and `--font-brush` (Special Season Brush 400 — brush CTA line, caps only). `body` SHALL set `font-synthesis: none` so heavier weights on Bowlby One render the real face rather than a faux-bold. Montserrat, Open Sans, Jost and Myriad Pro SHALL NOT ship. Pill buttons SHALL be `border-radius: 999px` in Bowlby One. The first-paint faces (Bowlby One, Manifold DSA Medium and Bold) SHALL be preloaded.

#### Scenario: Fonts load self-hosted
- **WHEN** any page loads
- **THEN** all brand faces load from `static/fonts/` with no external font requests and no Montserrat/Open Sans requests

### Requirement: High-contrast token swaps
High-contrast mode (Aa widget) SHALL swap red (`--color-red`, `--color-brand-red`, `--primary`) to `#B5121B`, red hover/deep to `#8E0E15`, and `--color-green-dark` to `#3F5A23` (`--color-green-panel` and `--color-flame` are decorative and match baked-in artwork, so they do not swap) via CSS custom-property overrides keyed off the existing `html.a11y-contrast` root class, alongside the per-band `data-tone` rules (`cream` and `orange` bands go white/black; `red`, `ink`, `green` bands darken); no other tokens change.

#### Scenario: High contrast toggled
- **WHEN** a visitor enables high contrast
- **THEN** red bands/chips render `#B5121B` and green-dark surfaces `#3F5A23`, persisting via `rgv-dsa-a11y`

### Requirement: Artwork assets are SVG-first
Production artwork (logo lockups, hero headline, stars/sparkles, county map, flames, luchador panel) SHALL ship as SVGs in `static/images/brand/` with semantic filenames mapped from the designer's `design-assets/SVG/` exports. Exceptions: the hero photo ships as the designer's duotone raster, and the luchador panel MAY fall back to the designer's 2x PNG if its spray texture rasterized poorly in SVG export. The header logo SHALL be transparent with the `#DC1520` background set in CSS. The prototype's `social-icons.png` SHALL NOT ship.

#### Scenario: Crisp assets
- **WHEN** the page renders on a high-DPI display
- **THEN** logos, stars, map, flames, and headline are vector-crisp; only the photo (and possibly the luchador) are raster
