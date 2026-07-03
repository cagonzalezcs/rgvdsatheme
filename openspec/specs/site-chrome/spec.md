# site-chrome Specification

## Purpose
TBD - created by archiving change chapter-theme-foundation. Update Purpose after archive.
## Requirements
### Requirement: Site header
All templates SHALL render a site header (via `base.twig`) containing the chapter logo linked home, the `primary` nav menu, and a prominent "Join DSA" CTA button linking to the DSA membership URL. The starter Timber header markup SHALL be replaced.

#### Scenario: Header on every template
- **WHEN** a visitor loads the front page, a single event, or an archive
- **THEN** the same header renders with logo, menu, and Join button

### Requirement: Mobile navigation toggle
Below the mobile breakpoint the header SHALL collapse the nav behind a toggle `<button>` that manages `aria-expanded` and `aria-controls`, implemented in `src/ts/components/SiteHeader.ts` and imported from `app.ts`.

#### Scenario: Toggle opens nav
- **WHEN** a mobile-width visitor taps the toggle
- **THEN** the nav becomes visible and `aria-expanded` flips to `true`; tapping again closes it

### Requirement: Site footer
All templates SHALL render a footer with the chapter mission one-liner, meeting info ("General meetings: 2nd Friday of each month, 6pm, on Zoom"), social links, newsletter link, and the `footer` menu when assigned.

#### Scenario: Footer content
- **WHEN** any page renders
- **THEN** the footer shows mission, meeting info, and social/newsletter links

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

