# Handoff: RGV DSA Website — Vue 3 + Tailwind v4 + shadcn-vue

## Overview
Implementation handoff for the Rio Grande Valley DSA chapter website. Eight screens were designed and prototyped: **Home**, **About**, **Get Involved**, **Calendar**, **Blog** (archive), **Blog Post** (single), an **Interior Page Template** (used for Bylaws / Documents / governance-style pages), and a **Single Event** (single-post template for the event CPT). The target codebase is the existing `rgvdsatheme` WordPress theme (Timber/Twig + Vite via `@kucrut/vite-for-wp`).

The goal of this handoff: stand up **Vue 3 (Composition API) + Tailwind v4 + a project-owned shadcn-vue component library**, then implement the seven designed screens with it.

## About the Design Files
The files in `designs/` are **design references created in HTML** — interactive prototypes showing intended look and behavior. They are NOT production code. The `.dc.html` format includes a small runtime (`support.js`) and templating syntax (`{{ }}`, `<sc-if>`, `<sc-for>`); ignore the mechanics, read them for **markup structure, exact styles (all inline), copy, and interaction logic** (the `<script>` block at the bottom of each file holds state/behavior in plain JS — port it, don't paste it).

Open any `designs/*.dc.html` in a browser to see the working prototype.

## Fidelity
**High-fidelity.** Colors, typography, spacing, borders, shadows, copy, and interactions are final and should be recreated faithfully. The visual language (v2) is **warm and rounded**: generous corner radius (pills for actions, 14–20px cards), **soft blurred shadows** for depth, flat brand-red bands, and **sentence-case** display type (uppercase only for small eyebrows/tags). The default shadcn-vue look is close in spirit but must be re-tokened to the palette + radius + shadow scale in `03-DESIGN-SPEC.md`. (An earlier neobrutalist direction — 0 radius, hard offset shadows, uppercase — was replaced; if any doc or file still shows it, `03-DESIGN-SPEC.md` v2 wins.)

## Documents in this package
1. **`01-ARCHITECTURE.md`** — technical implementation notes: how Vue 3, Tailwind v4, and shadcn-vue slot into the existing Timber/vite-for-wp theme; branch strategy; rendering architecture options; BEM stance; existing-code coexistence.
2. **`02-PHASES.md`** — the phased build plan, written as self-contained briefs to hand to separate Claude Code agents/contexts, with acceptance criteria per phase.
3. **`03-DESIGN-SPEC.md`** — design tokens, typography scale, per-page component inventory, interaction specs, the shadcn-vue component mapping, and the **Accessibility (WCAG 2.1 AA)** requirements (contrast in every state incl. hover, focus, motion, targets, ARIA + a per-phase a11y gate).
4. **`04-V2-HANDOFF-NOTES.md`** — **start here for the build.** Which files are the canonical v2 source, the v1→v2 re-skin delta, and the implementer gotchas (the Home-only functional EN/ES toggle, resolved cross-page anchor links, per-page tweak props, and corrections to stale "brutalist" wording in the phase docs).
5. **`05-RESPONSIVE-HANDOFF-NOTES.md`** — **mobile (320px) + tablet (768px) reflow spec.** Read alongside `04` before building the shared header. Covers breakpoints/container widths, the three-form responsive header (hamburger → two-tier → desktop), footer/a11y/toggle reflow, and per-page reflow specs — including the Calendar's mobile agenda-list (the month grid is replaced below `lg`). Backed by the six `* Mobile.dc.html` prototypes in `designs/`.
6. **`designs/`** — the eight desktop HTML prototypes + six responsive (mobile+tablet) companions + runtime + logo assets.
7. **`UPDATE-ABOUT-PAGE.md`** — delta brief for the About page (added 2026-07-02, after the phase docs were first written). If you are starting fresh, the phase docs already incorporate it; if a build is underway, paste this brief into the active Claude Code context.
8. **`UPDATE-SINGLE-EVENT-PAGE.md`** — delta brief for the Single Event template (added 2026-07-02). Same usage: the phase docs incorporate it for a fresh start; paste it into an active context to retrofit an in-progress build (it changes where the Calendar modal + blog `event_embed` link).

## Screens
- **Home** (`RGV DSA Home.dc.html`) — sticky red header w/ About dropdown + a11y widget; red hero; ink counties strip; Who We Are (text + photo placeholder); Upcoming Events list; From the Blog (1 featured card + 2 compact rows); three-step Get Involved section on ink; footer.
- **About** (`About.dc.html`) — the canonical target of the header About dropdown: red page header; ink mission band; content+sidebar layout with six anchored sections mirroring the dropdown 1:1 (`#chapter` About the Chapter, `#mission` Mission & History w/ timeline, `#counties` county cards, `#committees` committee rows, `#bylaws` governance-documents table, `#faq` tabular Q&A) + dues-switching callout. History timeline years are `20XX` placeholders for the chapter to fill in.
- **Get Involved** (`Get Involved.dc.html`) — red page header w/ breadcrumb; two-column content+sidebar layout; How to Join; Committees; Communication Channels; FAQ accordion.
- **Calendar** (`Calendar.dc.html`) — month-grid / list view toggle; category filter chips w/ color swatches; event chips; event detail modal; month navigation; subscribe strip.
- **Blog** (`Blog.dc.html`) — archive: red page header; search + category filter chips (same 6 colors as events); featured post card; editorial grid (mixed card spans); filtered/search state switches to uniform result rows; pagination; email-subscribe strip on ink.
- **Blog Post** (`Blog Post.dc.html`) — single: red hero w/ category tag, dek, byline (named-author or committee mode); featured image pulled up over the red band; article body = a stack of content blocks (prose, image, pull quote, gallery, person quote, video, audio, document, related event, action callout) intended as ACF flexible content; tags + share; author card; optional sticky meta rail; Read Next. Both blog prototypes have a **"</> ACF spec" toggle** (bottom-left) that overlays field-mapping annotations for implementers.
- **Interior Page Template** (`Interior Page Template.dc.html`) — the generic v2 shell for all future interior/governance pages (Bylaws, Documents, Resolutions, Education Library, Grievance): shared header/footer, red PageHeader (title/breadcrumb/lede as props), content+sidebar layout, a governing-documents list (rows with a PDF chip + Download button), code-of-conduct prose, and a grievance CalloutCard. Props: `pageTitle`, `breadcrumbSection`, `lede`, `showSidebar`.
- **Single Event** (`Single Event.dc.html`) — single-post template for the `event` CPT (added 2026-07-02): red event hero (breadcrumb Home / Calendar / event, category tag, title, summary lede, date/time/location meta chips + RSVP); featured image pulled up over the red band; a content + sticky 340px **details rail** layout. Main column = the `event_body` flexible-content stack (overview prose, numbered agenda, good-to-know list, accessibility/childcare note, getting-there/map). Rail = event-details card (`event_details` ACF group — date, time+doors, location/online toggled by location type, cost, RSVP), contact card, share card. "More upcoming events" band closes it. Every accent is driven by the event's category term color. Has the same **"</> ACF spec" toggle** as the blog files. Props: `category`, `locationType`, `rsvpRequired`, `showRelated`, `specMode`. No author/byline (events aren't posts).

**Note on blog copy:** all blog titles, excerpts, prose, quotes, captions, and author details are deliberately lorem ipsum / generic placeholders. Do not invent real editorial content — implement the layouts and let the chapter write the posts.

## Assets
- `designs/assets/logo-red.png` — header logo (on red)
- `designs/assets/logo-green.png` — footer logo (on cream/white)
- `designs/assets/cactus-mark-red.png` — decorative hero mark (Home)
- Fonts: **Montserrat** (400–900) and **Open Sans** (400–800), self-hosted TTFs in `designs/assets/fonts/`. The theme already has a `static/fonts/` directory — move them there (or swap to woff2).
- `designs/image-slot.js` — design-time-only helper (drag-and-drop image placeholder used by the blog prototypes). Do **not** port it; in production those slots are ordinary `<img>` / featured-image fields.

## How to use this with Claude Code
Work through `02-PHASES.md` in order. Each phase is written to be pasted into a **fresh Claude Code context** (or dispatched to a sub-agent) along with this folder. Do all work on a fresh branch (see `01-ARCHITECTURE.md` § Branching). Single-dev project — risk tolerance is high, but each phase should end with a passing build and a commit.
