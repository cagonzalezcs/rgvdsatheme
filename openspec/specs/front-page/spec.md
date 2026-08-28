# front-page Specification

## Purpose
TBD - created by archiving change chapter-theme-foundation. Update Purpose after archive.
## Requirements

### Requirement: Front page template
The theme SHALL provide `front-page.php` rendering `views/front-page.twig` via Timber, containing in order: hero (v3 split), who-we-are, upcoming events, from-the-blog, Ponte Trucha CTA. The v2 counties strip and get-involved steps sections SHALL NOT render. Each band SHALL carry a `data-tone` attribute (`red`/`cream`/`ink`/`orange`/`green`) for high-contrast mode.

#### Scenario: Front page renders all sections
- **WHEN** a visitor loads the site root with a static front page configured
- **THEN** the five v3 sections render in order with no PHP/Twig errors, and no counties strip or get-involved steps section appears

### Requirement: Hero section
The hero SHALL be a 50/50 split (stacking under ~1000px). Left: `#DC1520` panel with a centered column — the supplied headline artwork rendered as an `<img>` inside the page's `<h1>` with alt text "A better Rio Grande Valley is possible!", a subhead, a JOIN DSA pill linking to the chapter join URL, and a dashed CTA box (2px dashed `#FFC800`, radius 16) linking to Get Involved — plus scattered star artwork (decorative, hidden from AT). Right: the designer's green-duotone hero photo, `object-fit: cover`, min-height 480px. The headline SHALL remain an image (no CSS recreation).

#### Scenario: Headline is accessible art
- **WHEN** the front page renders
- **THEN** the `<h1>` contains the supplied headline image with meaningful alt text, and star art is `aria-hidden`

#### Scenario: Join CTA
- **WHEN** a visitor clicks the hero JOIN DSA pill
- **THEN** they are taken to the DSA membership URL from chapter context

#### Scenario: Narrow viewport
- **WHEN** the viewport is under the stack breakpoint
- **THEN** the panel and photo stack vertically with the headline still legible

### Requirement: Who we are section (v3)
The who-we-are section SHALL be a two-column grid (county map ~1.15fr / text 1fr): left the supplied `county-map` artwork (stars baked in) representing the chapter's counties; right a right-aligned column with orange eyebrow, Bowlby One heading, body paragraphs, and a "MORE ABOUT OUR CHAPTER" arrow link. Copy SHALL come from the page's ACF fields per language (v3 prototype copy as defaults).

#### Scenario: Map replaces counties strip
- **WHEN** the front page renders
- **THEN** the county story appears via the map artwork in who-we-are; no standalone counties strip band exists

#### Scenario: Right-aligned text column
- **WHEN** the section renders at desktop width
- **THEN** eyebrow, heading, paragraphs, and arrow link are right-aligned beside the map

### Requirement: Upcoming events section
The front page SHALL list up to 3 published `chapter_event` posts **in the active language** with `event_date` >= now, ordered soonest first, each card showing formatted date, title, location, event type term (if any), and an RSVP/link when `event_link` is set. When no upcoming events exist in the active language, an empty-state message with a link to `/events/` SHALL render instead.

#### Scenario: Past events excluded
- **WHEN** the only published events have `event_date` in the past
- **THEN** the empty-state message renders and no past events appear

#### Scenario: Soonest-first ordering
- **WHEN** multiple future events exist
- **THEN** cards render in ascending `event_date` order, max 3

#### Scenario: Spanish events on the Spanish home
- **WHEN** the Spanish front page renders and Spanish translations of upcoming events exist
- **THEN** the cards show the Spanish event translations; when none exist the empty-state renders

### Requirement: Events empty state (v3)
When the home events query returns zero events in the active language, the section SHALL render the v3 empty state: 2px dashed `#B9B3A9` rounded container with centered "No events on the books yet" lead and a link to the calendar.

#### Scenario: Zero events
- **WHEN** no upcoming events exist in the active language
- **THEN** the dashed v3 empty state renders with a calendar link and no event rows

### Requirement: Blog teasers driven by published posts
The home "From the blog" section SHALL render the latest published posts **in the active language** (sticky-aware featured card + two rows) from context supplied by `rgvdsa_blog_front_page_context()`; the context keys SHALL always be set (nullable/empty allowed), category pill classes SHALL be built in Twig from the raw `cat` slug, and an empty state SHALL render when no posts exist in the active language.

#### Scenario: Real posts on home
- **WHEN** published posts exist
- **THEN** the featured card and rows show real titles/dates/categories, not fixtures

#### Scenario: Pre-seed empty state
- **WHEN** no posts are published
- **THEN** the section shows a "Posts coming soon" state — never lorem ipsum

#### Scenario: Spanish posts on the Spanish home
- **WHEN** the Spanish front page renders and Spanish post translations exist
- **THEN** the featured card and rows show the Spanish posts; when none exist the empty state renders

### Requirement: Ponte Trucha CTA
The front page SHALL end with a Ponte Trucha CTA: full-width flames artwork, with the luchador panel artwork overlaid anchored to its bottom (proportional geometry: `left/right: 3.2%`, `bottom: 4.1%`, panel `width: 100%`). Over the panel, an inset column (`padding-left: 44%`) SHALL show "¡Ponte trucha sigue la lucha!" in Special Season Brush uppercased via CSS `text-transform` (source text keeps `¡`/`!`), right-aligned, plus an orange JOIN DSA pill linking to the chapter join URL. The brush line SHALL render identically in both languages (it is Spanish brand copy). Below ~700px the text SHALL stack rather than shrink below readable size.

#### Scenario: Brush line preserved
- **WHEN** the CTA renders in either language
- **THEN** the line displays uppercase via CSS with `¡` and `!` intact in the DOM

#### Scenario: Composition scales
- **WHEN** the viewport width changes
- **THEN** flames, panel, and overlay scale proportionally with text clear of the luchador

### Requirement: Front page is language-aware
The front page SHALL resolve its content for the active Polylang language. At `/` it SHALL render the English `page_on_front`; at `/es/` it SHALL render that page's Spanish translation, sourcing hero and who-we-are copy from the Spanish page's own ACF fields. Language-neutral tokens (county names, brand, social handles) SHALL render identically in both languages.

#### Scenario: Spanish front page
- **WHEN** a visitor loads `/es/`
- **THEN** the same section layout renders with Spanish hero/who copy from the Spanish page's ACF fields, and no PHP/Twig errors

#### Scenario: English unchanged
- **WHEN** a visitor loads `/`
- **THEN** the English front page renders exactly as before
