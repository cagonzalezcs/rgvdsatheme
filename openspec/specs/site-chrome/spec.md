# site-chrome Specification

## Purpose
TBD - created by archiving change chapter-theme-foundation. Update Purpose after archive.
## Requirements

### Requirement: Site header
All templates SHALL render a site header (via `base.twig`) containing the chapter logo linked home, the `primary` nav menu, and a prominent "Join DSA" CTA button linking to the DSA membership URL. The header SHALL wear the v3 skin site-wide: background `#DC1520`, nav links in Bowlby One (~1.06rem) white, pill-shaped buttons (JOIN DSA, Aa, EN/ES), and the v3 logo lockup (transparent SVG, ~78px tall, background set in CSS). Sticky behavior, About dropdown, EN/ES switcher, and Aa widget behavior SHALL be unchanged from v2.

#### Scenario: Header on every template
- **WHEN** a visitor loads the front page, a single event, or an archive
- **THEN** the same v3 header renders with logo, menu, and Join button

#### Scenario: v3 skin without behavior regressions
- **WHEN** a visitor uses the About dropdown, EN/ES toggle, and Aa widget on the v3 header
- **THEN** each behaves exactly as before the re-skin

### Requirement: Mobile navigation toggle
Below the mobile breakpoint the header SHALL collapse the nav behind a toggle `<button>` that manages `aria-expanded` and `aria-controls`, implemented in `src/ts/components/SiteHeader.ts` and imported from `app.ts`.

#### Scenario: Toggle opens nav
- **WHEN** a mobile-width visitor taps the toggle
- **THEN** the nav becomes visible and `aria-expanded` flips to `true`; tapping again closes it

### Requirement: Site footer
All templates SHALL render the v3 footer: background `#211E1E`, grid of v3 footer logo (~230px) plus About / Get involved / Resources link columns, social icon links top-right, column heads in Manifold Bold (~1.15rem), links in Manifold Medium (~1.06rem) white with `#FFC800` underline hover. Social icons SHALL be real icon components linking to the chapter's actual profiles (no placeholder image strip). The bottom bar SHALL be green `#5F813A` with the chapter name left and the accessibility invitation ("Built to be accessible — tell us how we can do better.") right. The `footer` menu/columns and contact email SHALL remain data-driven as today.

#### Scenario: Footer content
- **WHEN** any page renders
- **THEN** the v3 footer shows logo, link columns, working social icon links, and the green bottom bar

#### Scenario: No placeholder social strip
- **WHEN** the footer renders
- **THEN** each social icon is an individual link to a real chapter profile URL

### Requirement: Shared chapter context
`StarterSite::add_to_context()` SHALL expose a `chapter` array (join URL, Facebook/Instagram/Twitter URLs, newsletter URL, meeting blurb) consumed by header, footer, and front-page templates; nav locations `primary` and `footer` SHALL be registered. Demo starter context (`foo`, `stuff`, `notes`, `myfoo` filter) SHALL be removed.

#### Scenario: Single source for chapter URLs
- **WHEN** the join URL changes in `add_to_context()`
- **THEN** header, hero, and footer CTAs all reflect it without template edits

### Requirement: Language toggle is a Polylang language switcher
The header EN/ES toggle SHALL be a Polylang language switcher: each segment is an `<a>` linking to the current page's translation URL (or the target language's home when no translation exists), rendered from server-provided language data. The active language segment SHALL be marked `aria-current="true"`. All responsive header instances SHALL receive the same language props and stay consistent. The toggle SHALL NOT record a client language cookie or trigger any machine-translation bridge; navigation to the translated URL is the entire behavior. The EN/ES codes themselves remain untranslated.

#### Scenario: Flip to Spanish
- **WHEN** a visitor clicks the ES segment on the English front page
- **THEN** the browser navigates to `/es/` and the Spanish page loads

#### Scenario: Active state on Spanish page
- **WHEN** the Spanish page renders
- **THEN** the ES segment is styled active with `aria-current` and the EN segment links back to the English page

### Requirement: Header and footer chrome translate via Polylang
Header nav labels, the About dropdown, the mobile menu, the Join CTA label, and footer columns/tagline SHALL render in the active language — nav from per-language WP menus assigned in Polylang, and static labels from `pll__()`-registered strings passed as island props. Language-neutral tokens (brand name, county names, social handles) SHALL stay untranslated.

#### Scenario: Spanish chrome
- **WHEN** the Spanish front page renders
- **THEN** header nav labels, the Join CTA, and footer text render in Spanish while the EN/ES toggle segments and brand tokens stay literal
