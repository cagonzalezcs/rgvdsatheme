# front-page Specification

## Purpose
TBD - created by archiving change chapter-theme-foundation. Update Purpose after archive.
## Requirements
### Requirement: Front page template
The theme SHALL provide `front-page.php` rendering `views/front-page.twig` via Timber, containing in order: hero, about/mission, upcoming events, get-involved grid, newsletter/social CTA. Sections SHALL be composed from `views/ui/` partials.

#### Scenario: Front page renders all sections
- **WHEN** a visitor loads the site root with a static front page configured
- **THEN** all five sections render with RGV DSA copy and no PHP/Twig errors

### Requirement: Hero section
The hero SHALL display the headline "We're fighting for the Rio Grande Valley we deserve.", the tagline "From each according to their ability, to each according to their needs", a primary Join CTA linking to the DSA national join/dues URL, and a secondary CTA to events. Decorative art SHALL be inline SVG (no raster assets).

#### Scenario: Join CTA
- **WHEN** a visitor clicks the hero Join button
- **THEN** they are taken to the act.dsausa.org membership page

### Requirement: Upcoming events section
The front page SHALL list up to 3 published `chapter_event` posts with `event_date` >= now, ordered soonest first, each card showing formatted date, title, location, event type term (if any), and an RSVP/link when `event_link` is set. When no upcoming events exist, an empty-state message with a link to `/events/` SHALL render instead.

#### Scenario: Past events excluded
- **WHEN** the only published events have `event_date` in the past
- **THEN** the empty-state message renders and no past events appear

#### Scenario: Soonest-first ordering
- **WHEN** multiple future events exist
- **THEN** cards render in ascending `event_date` order, max 3

### Requirement: Get involved grid
The front page SHALL display up to 6 `working_group` posts ordered by `menu_order`, each card showing the group name, excerpt, and a solid-color/SVG placeholder instead of a photo.

#### Scenario: Working groups render
- **WHEN** working groups are published
- **THEN** the grid shows them by menu order with placeholder art

### Requirement: Newsletter and social CTA
The front page SHALL include a section linking to the Action Network newsletter signup and the chapter's Facebook, Instagram, and Twitter/X profiles with inline SVG icons.

#### Scenario: Social links resolve
- **WHEN** a visitor clicks a social icon
- **THEN** they reach the corresponding public chapter profile

### Requirement: Blog teasers driven by published posts
The home "From the blog" section SHALL render the latest published posts (sticky-aware featured card + two rows) from context supplied by `rgvdsa_blog_front_page_context()`; the context keys SHALL always be set (nullable/empty allowed), category pill classes SHALL be built in Twig from the raw `cat` slug, and an empty state SHALL render when no posts exist.

#### Scenario: Real posts on home
- **WHEN** published posts exist
- **THEN** the featured card and rows show real titles/dates/categories, not fixtures

#### Scenario: Pre-seed empty state
- **WHEN** no posts are published
- **THEN** the section shows a "Posts coming soon" state — never lorem ipsum

