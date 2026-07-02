# Handoff: RGV DSA Website — Vue 3 + Tailwind v4 + shadcn-vue

## Overview
Implementation handoff for the Rio Grande Valley DSA chapter website. Seven screens were designed and prototyped: **Home**, **About**, **Get Involved**, **Calendar**, **Blog** (archive), **Blog Post** (single), and an **Interior Page Template** (used for Bylaws / Documents / governance-style pages). The target codebase is the existing `rgvdsatheme` WordPress theme (Timber/Twig + Vite via `@kucrut/vite-for-wp`).

The goal of this handoff: stand up **Vue 3 (Composition API) + Tailwind v4 + a project-owned shadcn-vue component library**, then implement the seven designed screens with it.

## About the Design Files
The files in `designs/` are **design references created in HTML** — interactive prototypes showing intended look and behavior. They are NOT production code. The `.dc.html` format includes a small runtime (`support.js`) and templating syntax (`{{ }}`, `<sc-if>`, `<sc-for>`); ignore the mechanics, read them for **markup structure, exact styles (all inline), copy, and interaction logic** (the `<script>` block at the bottom of each file holds state/behavior in plain JS — port it, don't paste it).

Open any `designs/*.dc.html` in a browser to see the working prototype.

## Fidelity
**High-fidelity.** Colors, typography, spacing, borders, shadows, copy, and interactions are final and should be recreated pixel-faithfully. The visual language is deliberate neobrutalism: **zero border radius, hard offset shadows (no blur), thick ink borders**. The default shadcn-vue look (rounded, soft) must be re-themed — see `03-DESIGN-SPEC.md`.

## Documents in this package
1. **`01-ARCHITECTURE.md`** — technical implementation notes: how Vue 3, Tailwind v4, and shadcn-vue slot into the existing Timber/vite-for-wp theme; branch strategy; rendering architecture options; BEM stance; existing-code coexistence.
2. **`02-PHASES.md`** — the phased build plan, written as self-contained briefs to hand to separate Claude Code agents/contexts, with acceptance criteria per phase.
3. **`03-DESIGN-SPEC.md`** — design tokens, typography scale, per-page component inventory, interaction specs, and the shadcn-vue component mapping.
4. **`designs/`** — the seven HTML prototypes + runtime + logo assets.
5. **`UPDATE-ABOUT-PAGE.md`** — delta brief for the About page (added 2026-07-02, after the phase docs were first written). If you are starting fresh, the phase docs already incorporate it; if a build is underway, paste this brief into the active Claude Code context.

## Screens
- **Home** (`RGV DSA Home.dc.html`) — sticky red header w/ About dropdown + a11y widget; red hero; ink counties strip; Who We Are (text + photo placeholder); Upcoming Events list; From the Blog (1 featured card + 2 compact rows); three-step Get Involved section on ink; footer.
- **About** (`About.dc.html`) — the canonical target of the header About dropdown: red page header; ink mission band; content+sidebar layout with six anchored sections mirroring the dropdown 1:1 (`#chapter` About the Chapter, `#mission` Mission & History w/ timeline, `#counties` county cards, `#committees` committee rows, `#bylaws` governance-documents table, `#faq` tabular Q&A) + dues-switching callout. History timeline years are `20XX` placeholders for the chapter to fill in.
- **Get Involved** (`Get Involved.dc.html`) — red page header w/ breadcrumb; two-column content+sidebar layout; How to Join; Committees; Communication Channels; FAQ accordion.
- **Calendar** (`Calendar.dc.html`) — month-grid / list view toggle; category filter chips w/ color swatches; event chips; event detail modal; month navigation; subscribe strip.
- **Blog** (`Blog.dc.html`) — archive: red page header; search + category filter chips (same 6 colors as events); featured post card; editorial grid (mixed card spans); filtered/search state switches to uniform result rows; pagination; email-subscribe strip on ink.
- **Blog Post** (`Blog Post.dc.html`) — single: red hero w/ category tag, dek, byline (named-author or committee mode); featured image pulled up over the red band; article body = a stack of content blocks (prose, image, pull quote, gallery, person quote, video, audio, document, related event, action callout) intended as ACF flexible content; tags + share; author card; optional sticky meta rail; Read Next. Both blog prototypes have a **"</> ACF spec" toggle** (bottom-left) that overlays field-mapping annotations for implementers.
- **Interior Page Template** (`Interior Page Template.dc.html`) — same shell as Get Involved; documents list; code of conduct; grievance callout card. This is the generic template for all future interior pages.

**Note on blog copy:** all blog titles, excerpts, prose, quotes, captions, and author details are deliberately lorem ipsum / generic placeholders. Do not invent real editorial content — implement the layouts and let the chapter write the posts.

## Assets
- `designs/assets/logo-red.png` — header logo (on red)
- `designs/assets/logo-green.png` — footer logo (on cream)
- Fonts: **Montserrat** (400–900) and **Open Sans** (400–800), self-hosted TTFs in `designs/assets/fonts/`. The theme already has a `static/fonts/` directory — move them there (or swap to woff2).
- `designs/image-slot.js` — design-time-only helper (drag-and-drop image placeholder used by the blog prototypes). Do **not** port it; in production those slots are ordinary `<img>` / featured-image fields.

## How to use this with Claude Code
Work through `02-PHASES.md` in order. Each phase is written to be pasted into a **fresh Claude Code context** (or dispatched to a sub-agent) along with this folder. Do all work on a fresh branch (see `01-ARCHITECTURE.md` § Branching). Single-dev project — risk tolerance is high, but each phase should end with a passing build and a commit.
