# UPDATE — Single Event page added (2026-07-02)

> This brief is **v2-native** — it was written after the v2 redesign, so every visual detail below is current (unlike `UPDATE-ABOUT-PAGE.md`, whose styling is superseded). `designs/Single Event.dc.html` is the canonical prototype.

**How to use this file:** paste its full contents into your active Claude Code session in the `rgvdsatheme` repo. If you are starting the build fresh, the sibling docs (`README.md`, `01`/`02`/`03`) already incorporate everything below.

---

An **eighth screen** has been added: **Single Event** (`designs/Single Event.dc.html`). It is the single-post template for the `event` CPT — the full detail page a calendar/list event chip now links **to**, replacing the previous behavior where the only event detail lived inside the Calendar's `EventDetailDialog` modal. Open the prototype and toggle the bottom-left **"</> ACF spec"** button to see the field mapping overlaid in place.

This changes two things you may already have built (the Calendar modal's destination and the blog `event_embed` block's link target) and adds one template to the build.

## 1. What it is

`single-event.twig` → **`SingleEvent.vue`** island. Same shared chrome as every other page (SkipLink, SiteHeader with Calendar marked `aria-current="page"`, SiteFooter, A11yWidget). It is **not a blog post** — the `event` CPT has no author/byline fields. Structure top to bottom:

1. **Event hero** (`brand-red` band, `data-tone="red"`, padding `44/24/150` — the big bottom pad leaves room for the featured image to overlap up into it):
   - White-pill **breadcrumb**: Home / Calendar / *event title* (Calendar is the parent, not Blog).
   - Category **tag** (ink pill w/ a category-color dot; label + color from the shared taxonomy) linking back to Calendar.
   - `h1` (Montserrat 900, `clamp(2rem,4.6vw,3.3rem)`), then the **event summary** lede (1.5rem, max 52ch).
   - **Meta chip row**: date, time, and location chips (`rgba(28,25,23,0.85)` fills) + white **RSVP →** pill anchoring to `#rsvp`.
2. **Content + details rail** — grid `minmax(300px,1fr) 340px`, gap 56, max 1140px:
   - **Main column** (`<article>`): the **featured image** first, pulled up over the red band (`margin-top:-108px`, 18px radius, `0 16px 44px` shadow, `image-slot` in the prototype → `<img>` + `alt_text` in prod) with a figcaption (caption + credit). Then the **event_body** block stack (see §2).
   - **Details rail** (`<aside>`, `position:sticky; top:110px`, also pulled up `margin-top:-108px` so its card overlaps the red band alongside the image): the **Event details card**, a **Contact card**, and a **Share card** (see §3).
3. **More upcoming events** (off-white `#F7F5F2` band, toggle `showRelated`): `h2` + "Full calendar →" link; 3 event cards (category date block + tag + title + meta) linking to sibling event pages. **No ACF field** — the query is *next 3 events by `event_date`, excluding the current one.*

Everything that reads as an accent — the section `h2` underlines, agenda number badges, the accessibility aside, the details-card header bar, the RSVP button, the date badge — is driven by **one value: the event's category term color** (`accent`, with a 10%-alpha `accentSoft` for soft fills). Wire the term color once and let it cascade, exactly as the prototype's `renderVals()` does.

## 2. `event_body` — ACF flexible content (reorderable blocks)

The main-column stack mirrors the blog's `post_blocks` pattern but with an event-appropriate block set. Section accents inherit the category term color. Blocks shown in the prototype:

- **prose** ("About this event") — wysiwyg; 1.12rem/1.8, `#292524`; in-content `h2` = 3px underline in `accent`.
- **agenda** — ordered list rendered as numbered rows (round `accentSoft` badge w/ `accent` numeral + bold lead + description).
- **good-to-know** — a plain bulleted logistics list.
- **accessibility & childcare** aside — `accentSoft` bg, `border-left:5px solid accent`, mailto for accommodation requests. Recommend making this a standard (near-always-present) block given the chapter's a11y commitment.
- **getting there / map** (toggle `showMap`, shown when `location_type ≠ online`) — `h2` + a **map embed** placeholder (striped) rendered from the geocoded `location_address`, plus an address paragraph.

Treat these as reorderable flexible-content layouts, same as blog blocks.

## 3. `event_details` — ACF group (the sidebar rail)

Custom fields powering the details card (spec overlay lists the whole group): `event_date · start_time · end_time · doors_time · location_type · location_name · location_address · online_url · cost · rsvp_required · rsvp_url · capacity`. The card renders:

- Header bar in `accent` with the category label pill.
- A **date block** (weekday / big day / month, `accent` fill) + "Saturday, July 11 / 2026".
- Field rows: **Time** (start–end + "Doors open" from `doors_time`); **Location** (shown when `location_type ≠ online` — venue, address, "Get directions →" to a maps URL); **Online** (shown when `location_type ≠ in-person` — Zoom label + join link); **Cost**; **RSVP** status.
- A **RSVP button** (`accent` fill) whose label/copy flips on `rsvp_required` (required → "RSVP to reserve a spot"; optional → "RSVP (let us know you're coming)").
- **Add to calendar** — Google / iCal outline buttons (hrefs stubbed until Phase 6).

Below the details card: **Contact card** (off-white; name + email + phone) and **Share card** (off-white; "Copy link" → "Copied ✓" via `navigator.clipboard`, + "Email this event" mailto). Reuse the copy-link interaction from the Blog Post prototype.

## 4. Tweakable props (already declared on the prototype)

| Prop | Type | Default | Effect |
|---|---|---|---|
| `category` | enum `chapter\|poled\|mutual\|labor\|electoral\|social` | `chapter` | Sets the accent/term color threaded through the whole page. |
| `locationType` | enum `in-person\|online\|hybrid` | `hybrid` | Toggles the Location row, Online row, and the map block. |
| `rsvpRequired` | boolean | `false` | Flips the RSVP status text + button label. |
| `showRelated` | boolean | `true` | Shows/hides the "More upcoming events" band. |
| `specMode` | boolean | `false` | Prototype-only ACF-spec annotation overlay — **don't ship.** |

In production `category`, `locationType`, and `rsvpRequired` come from the event's own fields (not global options); `showRelated` is a template/theme setting.

## 5. Retrofits to already-built work

- **Calendar (Phase 5).** The event chips and List-view rows should link to the single-event permalink. Keep `EventDetailDialog` as a fast-preview affordance if you like, but its primary action ("View event / RSVP") should navigate to `SingleEvent`, which is now the canonical RSVP surface.
- **Blog Post (Phase 5B).** The `acf/event_embed` block's "RSVP →" link points at the event's `SingleEvent` permalink.
- **`ChapterEvent` data type.** The single template needs more than the calendar chip did — extend the fixture/CPT with `summary`, `doorsTime`, `locationName`, `locationAddress`, `onlineUrl`, `cost`, `rsvpRequired`, `capacity`, `featuredImage`/`alt`, and the `event_body` block array (see `03-DESIGN-SPEC.md` § Data types).

## 6. Acceptance additions

- Single Event matches `designs/Single Event.dc.html` at 1280w and 375w; the featured image and details card overlap the red band correctly (negative top margins) and the rail is sticky.
- Changing the event category re-tints every accent (h2 underlines, agenda badges, aside, details header, RSVP button) — verify no hard-coded reds leak through.
- `location_type` switches the Location/Online rows and the map block correctly (`online` hides both address + map; `in-person` hides the online row; `hybrid` shows all).
- Calendar chips/rows and the blog `event_embed` navigate to the correct event permalink.
- RSVP button label reflects `rsvp_required`; Copy-link shows the "Copied ✓" confirmation; keyboard + focus-visible pass on every control.

Suggested commit (fold into Phase 5, or a small dedicated phase): `feat(events): single-event template island`.
