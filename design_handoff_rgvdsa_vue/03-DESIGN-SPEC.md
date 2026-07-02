# 03 — Design Spec: Tokens, Components, Interactions

Source of truth: the prototypes in `designs/`. This doc extracts the values so agents don't have to re-derive them.

> **Design version: v2 (current).** This is the warm, rounded redesign — the intended build target. Any earlier "neobrutalism" description (hard offset shadows, zero radius, uppercase display type) is **superseded**; ignore it. If you find flat 0-radius / hard-shadow styling anywhere, it's stale.

## Visual language
Warm, modern, approachable. **Generous corner radius** everywhere (pills for actions, 14–20px for cards), **soft blurred shadows** for depth (never hard offset), **flat brand-red fills**, and **sentence-case** display type (uppercase is reserved for small eyebrows, tags, and micro-labels). Red is used with intent: a bright brand red for full-bleed bands, a deeper red for text and controls. Motion is a gentle lift or a fill shift — no translate-into-shadow, no glows. All hover fills must keep their label at ≥ 4.5:1 (see Accessibility).

## Tokens

### Colors
| Token | Hex | Usage |
|---|---|---|
| `brand-red` | `#E9252E` | Bright primary. Header, hero, page-header bands, event date badges, section accents (card top-borders, the 3px red `h2` underline), ★ |
| `red` | `#B01B22` | Deeper red. Links, red button fills (Come to a meeting, RSVP, Read the post, Update dues), red text on white, EN-active, breadcrumb "Home", `Chapter-Wide` category |
| `red-hover` | `#8F151B` | Hover darken for red fills, red text, and outline-button borders |
| `red-hc` | `#9E0B13` | High-contrast mode only (tone override) |
| `ink` | `#1C1917` | Body text, dark bands (mission, get-involved, subscribe, footer bar, sidebar cards), secondary button fill, active chip fill |
| `pink` | `#F0908F` | Accent **on ink**: eyebrows on dark bands, ink-button hover fill, RSS/subscribe hover |
| `white` | `#FFFFFF` | Page background, cards, calendar day cells, callout surfaces |
| `off-white` | `#F7F5F2` | Alt section background (events, read-next), sidebar "On this page" panel, author card, spec-note blocks |
| `tint` | `#FDF0EE` | Light red wash: dropdown/nav-item hover, dues callout bg, "Related" sidebar bg, accordion-row hover |
| `wash` | `#F7D7D3` | Stronger rose wash — **outline-button hover fill** (a clearly-visible hover the red label still reads on) |
| `text-strong` | `#292524` / `#292420` | Long-form article prose; darkest ink cards on Home |
| `text-body` | `#44403C` | Body copy on white |
| `text-muted` | `#57534E` | Secondary text, meta, captions |
| `text-faint` | `#78716C` | Tertiary, disabled, "(to be filled in)" notes |
| `muted-on-ink` | `#CFC8BD` | Body text on ink bands |
| `border` | `#ECE6DA` | Footer top border, hairlines on cream |
| `divider` | `#F0EDE8` | Inner dividers inside white cards/tables |
| `divider-warm` | `#E3D9C6` | Row dividers between list items on cream |
| `border-control` | `#D6D0C4` | Search input, inactive chip/tag borders, disabled pagination |
| `border-dashed` | `#B7AC9B` | Dashed borders — members-only pill, empty states, spec notes |
| `placeholder stripes` | `#F5F1EA` / `#ECE6DA` | 45° repeating stripes for image placeholders |

Event **and** blog category colors (shared taxonomy term colors in WP; white text sits on the fill):
`chapter` Chapter-Wide `#B01B22` · `poled` Political Education `#33518F` · `mutual` Mutual Aid `#1B6B40` · `labor` Labor `#8F5715` · `electoral` Electoral `#6E3B87` · `social` Social `#0A6B74`

### shadcn semantic variable mapping (`:root`)
```
--background: #FFFFFF      --foreground: #1C1917
--card: #FFFFFF           --card-foreground: #1C1917
--primary: #B01B22        --primary-foreground: #FFFFFF
--secondary: #1C1917      --secondary-foreground: #FFFFFF
--muted: #F7F5F2          --muted-foreground: #57534E
--accent: #FDF0EE         --accent-foreground: #B01B22
--destructive: #B01B22
--border: #ECE6DA         --input: #D6D0C4         --ring: #1C1917
--radius: 0.875rem   /* 14px — cards/inputs; actions & chips use the pill radius 999px */
```
Brand-red bands (header, hero, page headers, post hero) are painted with `brand-red` `#E9252E`, not `--primary`. Default component border-width is 1px hairlines (`--border`) on cards; 2px `red` on outline buttons. Popover/dropdown/dialog shadow = `0 14px 36px rgba(28,25,23,0.24)`.

### Radius scale (rounded — never 0)
`999px` pills — buttons, chips, filter/category tags, badges, the language toggle, avatar frames · `8px` — segmented-control segments, sidebar nav links, skip link, focus-ring rounding · `9px` dropdown items · `10px` nav hover area / PDF chip · `12px` step-number badge, event date badge, spec-note blocks · `14px` standard cards, tables, nav panels, document rows, meta-rail cards · `16px` step cards, sidebar ink cards, dues callout, search-result rows, blog grid cards, read-next cards, gallery figures · `18px` photo frames, featured image, pull quote, person quote, compact blog rows, home featured card, author card · `20px` blog featured card, action callout.

### Shadows (soft, blurred — no hard offset)
- Card resting: `0 2px 10px rgba(28,25,23,0.10)`
- Card hover lift: `0 12px 30px rgba(28,25,23,0.16)` (home blog cards go `0 14px 34px …0.16`)
- Subtle: `0 1px 4px rgba(28,25,23,0.06–0.07)` (home event rows, search input)
- Header: `0 2px 14px rgba(28,25,23,0.22)`
- Dropdown / popover / dialog: `0 14px 36px rgba(28,25,23,0.24)`
- Featured blog card: `0 8px 28px rgba(28,25,23,0.14)` · media/photo frames `0 8px 26px …0.14`, gallery figures `0 4px 18px …0.12`, pulled-up featured image `0 16px 44px …0.24`, Who-We-Are photo `0 10px 30px …0.12`
- White CTA focus ring on red: `0 0 0 3px rgba(28,25,23,0.25)`

**Hover motion:** cards `translateY(-2px)`; white CTAs `translateY(-1px)` or the ring above; media play buttons `scale(1.05)`. All motion is gated behind reduce-motion (see Accessibility).

### Typography
- **Display: Montserrat** — headings, nav, labels, numbers. Weights 600/700/800/900.
- **Body: Open Sans** — everything else. Weights 400/600/700/800. Fallback `system-ui, sans-serif`.
- **Case:** headings are **sentence case**. Uppercase is only for eyebrows, category tags, and micro-labels.
- `h1` (page header / hero): Montserrat 900, `clamp(2rem, 4.8vw, 3.4rem)` — home hero `clamp(2.3rem, 5.6vw, 4rem)`, post hero `clamp(2rem, 4.6vw, 3.3rem)`; line-height 1.08–1.12; letter-spacing −0.01em; max 18–24ch; `text-wrap: balance`.
- `h2` section: Montserrat 800, `clamp(1.5rem, 3.6vw, 2.5rem)`, line-height 1.15.
- `h2` in-content (About / Get Involved / article): Montserrat 800, 1.55rem (1.5rem in articles), letter-spacing −0.01em, `padding-bottom:10–12px; border-bottom:3px solid #E9252E`, `scroll-margin-top:110px`.
- Lede: 1.5rem, line-height 1.5, max ~48ch.
- Eyebrow / kicker: 0.82–0.85rem, weight 700, letter-spacing 0.12–0.14em, uppercase — `red` on white, `pink` on ink, cream on red.
- Body: 1–1.08rem, line-height 1.6–1.78. Article prose: 1.15rem, line-height 1.8, color `#292524`.
- Nav links: Montserrat 700, 1.17rem, sentence case; the active page link is underlined (offset 6px, thickness 3px) + `aria-current`.
- Micro-labels / meta: 0.72–0.9rem, weight 600–700 (0.06–0.14em tracking where uppercase).
- Root font-size is the a11y text-size lever: 16 / 18 / 20px. Use `rem` everywhere.

### Layout
Content max-width **1140px** (header/footer bars 1220px; blog & calendar content 1200px; article measure 980px with a `min(66ch,100%)` prose column; post hero 880px), side padding 24px. Section vertical padding 88–96px (home); page-header band 48px top / 56px bottom; mission band 64px; content 64px top / 96px bottom. Interior grid: `minmax(300px,1fr) 300px`, gap 56px; sidebar `position:sticky; top:110px`. Header: sticky, min-height 64px, `brand-red` background, soft shadow (**no** bottom border); logo height 58px. Footer: white with a 1px `#ECE6DA` top border, grid `minmax(240px,1.2fr) repeat(auto-fit, minmax(160px,1fr))` gap 40, ink bottom bar.

## Chrome components

### SiteHeader (brand-red band, `data-tone="red"`)
Logo (`logo-red.png`, h 58px) · nav (Montserrat 700, 1.17rem, cream text, hover `background:rgba(28,25,23,0.18)`; active page = underline offset 6px / 3px thickness + `aria-current`): **About ▾** dropdown deep-linking the six About anchors — About the Chapter `#chapter`, Mission & History `#mission`, Counties We Serve `#counties`, Committees `#committees`, Bylaws & Code of Conduct `#bylaws`, FAQ `#faq` (white panel, 14px radius, `0 14px 36px` shadow, items hover `tint` bg / `red` text), then **Calendar**, **Blog**, **Get Involved** · right cluster: EN/ES toggle (white pill; EN active = `red` fill / white text; ES `title="Español — próximamente"`, `red` text, disabled-feeling), **Aa** a11y button (white pill, `red` text, hover `red-hover` text + ring), **Join DSA** CTA (white pill, `red` text, hover `red-hover` text + ring).

### A11yWidget (popover under "Aa")
280px white panel, 14px radius, `0 14px 36px` shadow. "Accessibility" heading; **Text size** segmented control A / A+ / A++ (active = ink fill / white text, 8px radius) → sets `document.documentElement.style.fontSize` to 16/18/20px; **High contrast** and **Reduce motion** rows, each a full-width button with an On/Off pill (on = `red` fill / white text; off = transparent, `#D6D0C4` border, `#57534E` text).
Persistence: localStorage `rgv-dsa-a11y` = `{textSize, highContrast, reduceMotion}`, applied on every page load. High contrast injects tone overrides: `[data-tone="cream"]` → white/black; `[data-tone="red"]` → `#9E0B13`; `[data-tone="ink"]` → black/white. Every band keeps its `data-tone` attribute — that's the hook. Reduce motion kills all animation/transition + smooth scroll. Vue port: `useA11ySettings()` composable owns state/persistence/application; widget is presentation.

### SiteFooter (white, 1px `#ECE6DA` top border)
Grid `minmax(240px,1.2fr) repeat(auto-fit, minmax(160px,1fr))`, gap 40. Logo `logo-green.png` (200px) + tagline ("Organizing across Hidalgo, Cameron, Willacy, and Starr counties."). Columns: About / Get involved / Resources / Contact (uppercase Montserrat 700 heads; links hover `red` + underline). Ink bottom bar: org name + "Built to be accessible — tell us how we can do better".

### PageHeader (brand-red band)
White pill breadcrumb (Home / Page, 0.85rem 700; "Home" is a `red` link, current page is ink) + `h1` + lede (1.5rem, max ~48ch).

### Buttons (cva variants — all pill radius 999px, weight 700)
- `primary` (red): `red` `#B01B22` fill, white text; hover fill `red-hover` `#8F151B`. Padding ≈ 11–16px / 22–34px. Used: Come to a meeting, RSVP, Read the post, Update my dues, featured "Read the post".
- `primary-on-red` (Join DSA in header & hero): white fill, `red` text; hover = `red-hover` text + focus ring `0 0 0 3px rgba(28,25,23,0.25)` (header) or `translateY(-1px)` + resting shadow `0 4px 14px rgba(28,25,23,0.25)` (hero).
- `secondary-ink`: ink `#1C1917` fill, white text; hover `#3A342E`. On ink bands the inverse also appears — white fill / ink text, hover `pink` `#F0908F` fill.
- `outline-red`: transparent, 2px `red` border, `red` text; **hover = `wash` `#F7D7D3` fill + border `red-hover`; the label stays red (~5.2:1).** A deliberately perceptible warm wash (not a barely-there tint). Used: View event, Follow, Write us, Add to calendar, Get involved, Older posts, Download, RSVP (embed), Copy link, Email it. See the hover note below — do **not** fill with `red` under red text.
- `outline-on-ink`: transparent, 2px `#57534E` border, white text; hover border `#FFFFFF`. Used: iCal / Outlook, Secondary action, Email us.
- `link-accent`: `red` text 700; hover underline (offset 4px) or `red-hover`. Sentence case.
- `chip` (calendar & blog filters): pill; inactive = white + 1px `#D6D0C4` border + ink text; active = ink fill + white text; leading category color swatch dot.

> **Outline-button hover — required behavior.** The design intent is a satisfying fill on hover. In the prototypes the fill is the light `tint` with the red label kept (verified ≥ 4.5:1) because a `:hover` rule can't override an element's *inline* resting `color`. In the Vue/Tailwind build you may instead **invert** to `red` fill + **white** label (`hover:bg-[#B01B22] hover:text-white`) — that also passes AA and matches the brand. **Either is fine; filling the background with `red` while leaving the label red is not** — it collapses the label to ~1:1 and it disappears (this was a real QA bug). See Accessibility.

## Accessibility (WCAG 2.1 AA)

Target conformance: **WCAG 2.1 AA**. The prototypes bake in the baseline below — preserve it in the Vue/Tailwind port; the shadcn-vue components must not regress it.

### Contrast, in every state (1.4.3 / 1.4.11)
Text ≥ **4.5:1** against its background; large text (≥ 24px, or ≥ 18.66px bold) and the visible boundary of UI components ≥ **3:1**. This must hold in **default, hover, active, and focus** — not just at rest. Run each screen through axe/Lighthouse **and** manually hover every control.

**Outline-button hover trap — caught in QA.** An outline/ghost button that fills its background on hover must *simultaneously* keep its label readable on the new fill. Two safe patterns, both verified:
- **Wash (prototype default):** fill `#F7D7D3`, keep the `#B01B22` label → **5.2:1** — a clearly-visible warm hover.
- **Invert (on-brand, production):** fill `#B01B22`, flip label to white → **6.9:1**.

Never fill with a hue that matches the label (red fill under red text ≈ 1:1) and never change only the background.

### Focus visible (2.4.7)
Every interactive element shows a visible focus indicator: **3px ink outline, 2px offset, 6px rounding** (`a/button/input:focus-visible`). Do not strip outlines when theming shadcn-vue; `--ring` is `ink`. Focus order follows DOM order; no positive `tabindex`.

### Motion (2.3.3 + user preference)
Card lifts, translate/scale hovers, and transitions must be gated behind reduced motion. Honor **both** the A11y widget's Reduce-motion toggle **and** the OS `prefers-reduced-motion: reduce` query — both kill animation/transition and smooth scroll.

### Targets & inputs
Interactive hit targets ≥ **44×44px** (nav links, toolbar chips, calendar chips, pagination — pad small controls to meet this). Every form control has a programmatic label; icon-only controls (Aa, calendar prev/next, dialog close, search, media play) carry an `aria-label`.

### Structure & ARIA (already in the prototypes — keep on port)
Skip-to-main link (first focusable, visible on focus) · one `<main id="main">` + `<header>` / `<nav aria-label>` / `<footer>` landmarks · `aria-current="page"` on the active nav link · dropdown & dialog use `aria-expanded` / `role="dialog"` + focus trap + Esc-to-close · calendar month label is `aria-live="polite"`, filter/result counts use `role="status"` · decorative placeholders, dividers, and ★ glyphs are `aria-hidden` · the language toggle marks the ES control `lang="es"` and the port should set `document.documentElement.lang`.

### Per-phase a11y gate
A phase isn't "done" until, on every screen it touches: axe reports zero critical/serious issues, keyboard-only tab + activate reaches and operates every control with a visible focus ring, and every hover/active state has been eyeballed for the contrast trap above.

## Pages

### Home (`RGV DSA Home.dc.html`)
1. **Hero** (red): eyebrow pill "Rio Grande Valley Democratic Socialists"; `h1` "A better world is possible. We're building it in the Valley." (max 18ch, balanced); lede ¶ (1.5rem); CTAs [Join DSA `primary-on-red`] [Come to a meeting ↓ `secondary-ink`]; footnote pill "New here? Start with RGV-DSA 101…"; cactus mark image (`cactus-mark-red.png`, decorative). Padding 88/96.
2. **CountiesStrip** (ink): centered Montserrat 700 uppercase city names separated by red ★. Toggleable (`showCountiesStrip`).
3. **WhoWeAre** (white): grid `minmax(300px,1.1fr) minmax(280px,0.9fr)` gap 64; red eyebrow; `h2` "We are DSA-RGV"; 2 ¶ (`#44403C`); `link-accent` "More about our chapter →". Photo frame: 18px radius, `0 10px 30px` shadow, 4:3, striped placeholder + monospace caption until a real photo exists.
4. **UpcomingEvents** (off-white `#F7F5F2`): `h2` + `link-accent` "Full calendar →"; rows are white cards (14px radius, `0 1px 4px` shadow) grid `76px 1fr auto` gap 24 — red date badge (12px radius, day Montserrat 800 1.4rem / month 0.72rem), title 1.12rem 700 + `#57534E` meta, "View event" `outline-red`. Count via `eventCount` (1–6).
5. **FromTheBlog** (white): `h2` "From the blog" + `link-accent` "All posts →"; grid `minmax(300px,1.15fr) minmax(280px,1fr)` gap 32 — left: featured post card (18px radius, `0 2px 10px`→hover `0 14px 34px` + `translateY(-2px)`, 16:9 striped image w/ category tag, date · read-time, Montserrat 800 title, excerpt, red "Read the post →"); right: 2 stacked compact rows (`130px 1fr` grid, striped thumb, outline category tag, 1.08rem 700 title, date).
6. **GetInvolvedSteps** (ink): pink eyebrow + `h2` "Three steps to start organizing"; 3 cards `repeat(auto-fit,minmax(260px,1fr))` gap 28 — `#292420` fill, 18px radius, big pink number (Montserrat 800 1.7rem), bold title, `#CFC8BD` body, white underline link (hover pink). Social strip below a `#3A342E` rule: Instagram (white pill, hover pink) + Email (`outline-on-ink`).

### About (`About.dc.html`)
PageHeader ("About RGV DSA" / lede). Below: ink **mission band** (padding 64/24) — pink eyebrow "What we believe" + Montserrat 700 statement `clamp(1.35rem,2.8vw,2rem)`, line-height 1.4, max 34ch. Then content+sidebar grid. Six sections mirror the header About dropdown 1:1; every `h2` is the in-content style (3px red underline) with `scroll-margin-top:110px`:
- `#chapter` **About the Chapter** — 2 ¶ + photo slot (`image-slot`, full-width 340px, 16px radius, striped placeholder) + CTA row: "Come to a meeting" (`primary`) + "Get involved" / "Students: UTRGV YDSA" (`outline-red`).
- `#mission` **Mission & History** — 1 ¶ + timeline: white card (14px radius, `0 2px 10px`); rows grid `110px 1fr` gap 16, `#F0EDE8` dividers; year = Montserrat 800 `red` 1.05rem. Rows: 1982 DSA founded; two `20XX` chapter milestones — **years are placeholders for the chapter, don't invent**.
- `#counties` **Counties We Serve** — 1 ¶ + 4 cards `repeat(auto-fit,minmax(220px,1fr))` gap 14 (white, 14px radius, `0 2px 10px`, **4px `brand-red` top border**): Montserrat 800 county name; muted `#57534E` city list; Hidalgo adds `red` micro-label "Home base — most meetings held here".
- `#committees` **Committees** — 1 ¶ + definition rows in one white card: grid `200px 1fr` gap 16, `#F0EDE8` dividers; `red` Montserrat 700 name + `#44403C` description. Same six committees as Get Involved — share one fixture. Below: `link-accent` "Join a committee →".
- `#bylaws` **Bylaws & Code of Conduct** — 1 ¶ + a real `<table>` (white, 14px radius, `0 2px 10px`, `border-collapse:separate`): ink header row (white Montserrat 700 uppercase 0.82rem: Document / What it covers / action col 90px); rows (`th scope="row"` bold nowrap, `#F0EDE8` dividers): Chapter Bylaws, Code of Conduct, Grievance Policy, Meeting Minutes; action = `red` "Read"/"Browse" links into the Interior template.
- `#faq` **FAQ** — informational **tabular Q&A, NOT the accordion** used on Get Involved: one white card; rows grid `minmax(200px,2fr) 3fr` gap 20, `#F0EDE8` dividers; bold 0.98rem question / 0.95rem answer. 6 rows: events-without-membership, dues amount, **switching to monthly/Solidarity Dues**, no-experience, privacy, time commitment. Below: **dues CalloutCard** `#dues` — `tint` `#FDF0EE` bg, 16px radius, `border-left:5px solid #E9252E`, `red` kicker "Switching your dues rate?", body ¶, "Update my dues" `primary` → join URL.
Sidebar: "On this page" nav (off-white panel, 14px radius, items hover `tint`/`red`) = the six labels/anchors + ink "New here?" card (white pill CTA, hover pink).

### Get Involved (`Get Involved.dc.html`)
PageHeader ("Get involved" / "No experience needed, no perfect politics required…"). Content+sidebar grid. Sections (in-content `h2`, anchors): `#join` How to join — 3 numbered step cards (16px radius, `0 2px 10px`, grid `64px 1fr`, red rounded number chip); `#committees` Committees — 6 cards `repeat(auto-fit,minmax(240px,1fr))`, 14px radius, 4px `brand-red` top border; `#channels` Communication channels — 3 rows (WhatsApp w/ dashed "Members only" pill; Instagram "Follow" + Email "Write us" `outline-red`); `#faq` Common questions → **Accordion** (white cards, 14px radius, button rows hover `red` text, +/− icon). Sidebar: "On this page" (off-white), ink "Ready right now?" card, "Related" panel (`tint` bg). `showFaq` toggles the FAQ block.

### Interior Page Template (`Interior Page Template.dc.html`)
The generic v2 shell for all interior/governance pages — same header/footer/a11y chrome as the other pages, PageHeader driven by props (`pageTitle`, `breadcrumbSection`, `lede`), content+sidebar grid. Default content = the Bylaws & Code of Conduct page: `#documents` governing-documents list (white 14px-radius rows, `0 2px 10px` shadow, mono PDF chip + title/meta + Download `outline-red`), `#conduct` code-of-conduct prose + bullet list + "Amending these documents" `h3`, and a `#grievance` **CalloutCard** (`tint` `#FDF0EE` bg, 16px radius, `border-left:5px solid #E9252E`, `red` kicker "Need to report something?", mailto). Sidebar: "On this page" (off-white panel), "Related" (`tint` panel), ink "New here?" card. In production this is `views/page.twig` (the default WP page template); PageHeader comes from the WP title + excerpt/lede.

### Calendar (`Calendar.dc.html`)
Toolbar: month label (Montserrat 900) + prev/next (ghost, hover `tint`); view toggle Month | List (segmented, active ink fill); filter chips — "Filter:" label + All events + 6 category chips (pill, inactive white/`#D6D0C4` border, active ink fill, 10px color swatch).
- **Month grid**: white cells min-height 112px (out-of-month `#F5F3EF` @ 0.6 opacity); today's date number = `red` fill / white pill; event chips = category-color fill, white 0.74rem 700 text, ellipsized, hover `outline:2px solid #1C1917`; full title + time in `title`.
- **List view** (current month, date-sorted): white rows (16px radius, `0 2px 10px`), 72px date block in category color, category tag, title + `time · location` meta; hover `0 12px 30px` + `translateY(-2px)`. Empty state: dashed `#B7AC9B` border, "No events in this category this month…".
- **EventDetailDialog**: overlay `rgba(28,25,23,0.55)`; white panel, 18px radius; header bar in category color (category label + close ✕); body: title, full date line, time, location, description, [RSVP `primary`] [Add to calendar `outline-red`]. Esc/overlay closes; focus trapped. **The primary action (RSVP / View event) now navigates to the full Single Event page** (added 2026-07-02) — the modal is an optional fast preview, not the RSVP endpoint. Event chips and List-view rows link to the same permalink.
- **Subscribe strip** (ink): heading + [Google Calendar (white pill, hover pink)] [iCal / Outlook (`outline-on-ink`)] — hrefs stubbed until Phase 6.
- State: `view` (via `defaultView`), `monthOffset`, `activeCat`, `selectedId`. `showCategoryColors`, `showSubscribe` props. Sample data: 14 events across Jul–Aug 2026 — use as the fixture and WP seed.

### Blog (`Blog.dc.html`)
**All blog copy is lorem ipsum placeholder** — implement layout, keep placeholder text as fixtures. A bottom-left **"</> ACF spec"** toggle overlays field-mapping annotations (dashed off-white blocks); the notes below summarize them.
- **PageHeader**: breadcrumb Home / Blog; `h1` "Chapter Blog"; lede.
- **Toolbar** (white): rounded search input (`#D6D0C4` border, pill, ink ⌕ block, `:focus` border ink) + "Filter:" label + chips: All posts + 6 category chips (pill, active ink fill, 11px color swatch). State syncs to URL: `?category=slug`, `?s=term`.
- **Browse state** (no filter/search): **featured post card** — white, 20px radius, `0 8px 28px` shadow; 2-col grid `minmax(300px,1.1fr) minmax(300px,1fr)`; left = featured `image-slot`, right = "★ Featured" red micro-label + category tag (term-color fill) + Montserrat 800 title (hover `red`) + dek + byline/date/read-time + "Read the post" `primary`. Fields: title · dek (ACF text) · category · featured_image.
  Below: **editorial grid** — 6-col grid, gap 24; card spans by index `[3,3,2,2,2,2,2,2]`; cards white, 16px radius, `0 2px 10px`→hover `0 12px 30px` + `translateY(-2px)`, 16:9 striped image w/ category tag, date, Montserrat 700 title (1.3rem on span-3 / 1.05rem on span-2), excerpt on span-3 only. Pagination: Newer (disabled `#D6D0C4`) / Older (`outline-red`).
- **Filter/search state**: layout switches to a 920px column — result-count line (`role="status"`) + "Clear filters ✕" over a 3px red rule; rows white (16px radius, `0 2px 10px`, hover lift) grid `auto 1fr auto`: 12×48 category-color bar, category·date micro-label in term color, title, excerpt, →. Empty state: dashed `#D6D0C4` border, "No posts match".
- **Subscribe strip** (ink): "Get new posts by email" + email input (pill, `#57534E` border, transparent) + Subscribe (white pill, hover pink) + RSS link. Form action stubbed until Phase 6.

### Blog Post (`Blog Post.dc.html`)
- **Hero** (red, padding 48/140 bottom, 880px): breadcrumb Home / Blog / post; category tag (term-color fill, hover ink); Montserrat 900 `h1` (max 24ch, balanced); dek 1.5rem; byline row — **two modes** via `bylineMode` (`named`: 40px avatar + "By **Name** · Committee"; `committee`: "By the **X Committee**") as white pills; date · read-time pill; "Léelo en español →" stub (ink pill).
- **Featured image**: pulled up over the red band (`margin-top:-100px`), 18px radius, `0 16px 44px` shadow, caption + credit below.
- **Article body** = `post_blocks` ACF flexible content stack (article max 980px, prose measure `min(66ch,100%)`, block accents inherit the post category term color; every image field pairs with required `alt_text`):
  - `acf/prose` — wysiwyg. 1.15rem/1.8, `#292524`; in-content `h2` = 3px red underline.
  - `acf/image` — image · alt_text · caption · credit · breakout. 18px radius, `0 8px 26px` shadow.
  - `acf/pull_quote` — quote · attribution. White card, 18px radius, `0 8px 26px`, `border-left:6px solid` category color; Montserrat 700 quote; uppercase attribution in category color.
  - `acf/gallery` — repeater (image · alt_text · caption) · layout (essay | grid). 2-col grid, first image full-width; 16px-radius figures + caption bars.
  - `acf/person_quote` — photo · alt_text · quote · translation (optional) · name · role · lang. White card 18px radius, 96px circle photo, bilingual quote pair, name in category color.
  - `acf/video` — url (oEmbed) · poster · caption · captions-required reminder. 16:9, 18px radius, `0 8px 26px`, red circular play button (hover `red-hover` + `scale(1.05)`), CC badge, transcript link.
  - `acf/audio` — file · title · duration · transcript (required). White card 18px radius: red circular play button, title, category-color progress bar, mono timestamps, transcript link.
  - `acf/document` — file · title · description. DocumentRow: white 14px-radius row, PDF chip, title, meta, Download `outline-red`.
  - `acf/event_embed` — relationship → event CPT. Calendar list-row: category-color date block (12px radius), "Upcoming event" micro-label, title, meta, RSVP `outline-red`.
  - `acf/action_callout` — heading · body · buttons repeater (label · url · style primary|outline). Ink card 20px radius, Montserrat 800 heading, [white primary (hover pink)] + [`outline-on-ink` secondary].
- **End matter**: tags row (category tag + `#D6D0C4`-outline tag chips) + share ([Copy link → "Copied ✓"] + [Email it], both `outline-red`); **author card** — off-white `#F7F5F2`, 18px radius, avatar (named) or category-color initials block (committee), `red` kicker "About the author", name, bio, committee link.
- **Meta rail** (optional, `showMetaRail`, default off): sticky 280px right rail — "Posted in" tag, share links, ink subscribe mini-card (14–16px radius panels).
- **Read Next** (off-white band): `h2` + `link-accent` "All posts →"; 3 cards `repeat(auto-fit,minmax(260px,1fr))` (16px radius, hover lift) — same anatomy as the archive grid. Query: same category, latest 3, excluding current.

### Single Event (`Single Event.dc.html`)
The single-post template for the `event` CPT — the full detail/RSVP page that Calendar chips, List rows, and the blog `event_embed` block link to. Shared chrome (Calendar link `aria-current`). **No author/byline** (events aren't posts). One value threads the whole page: the event's **category term color** = `accent` (+ 10%-alpha `accentSoft`), driving the section `h2` underlines, agenda badges, the a11y aside, the details-card header, the date badge, and the RSVP button. A bottom-left **"</> ACF spec"** toggle overlays field mapping (prototype-only).
- **Event hero** (`brand-red`, `data-tone="red"`, padding `44/24/150` — deep bottom pad so content overlaps up into it): white-pill breadcrumb Home / **Calendar** / event; category tag (ink pill + `accent` dot) → Calendar; Montserrat 900 `h1` `clamp(2rem,4.6vw,3.3rem)` (max 24ch, balanced); **event_summary** lede (1.5rem, max 52ch); meta-chip row — date / time / location chips (`rgba(28,25,23,0.85)` fills) + white **RSVP →** pill to `#rsvp`.
- **Content + details rail**: grid `minmax(300px,1fr) 340px`, gap 56, max 1140px.
  - **Main column** (`<article>`): **featured image** first, pulled up over the red band (`margin-top:-108px`, 18px radius, `0 16px 44px` shadow, `image-slot` → `<img>`+`alt_text`) + figcaption (caption · credit). Then the **event_body** flexible-content stack (accents inherit `accent`): `prose` ("About this event", in-content `h2` = 3px `accent` underline) · `agenda` (ordered rows: round `accentSoft` badge w/ `accent` numeral + bold lead) · `good-to-know` (bulleted logistics) · **accessibility & childcare** aside (`accentSoft` bg, `border-left:5px solid accent`, accommodation mailto) · **getting there / map** (`showMap`, shown when `location_type ≠ online`: `h2` + striped map-embed placeholder from geocoded `location_address` + address ¶).
  - **Details rail** (`<aside>`, `position:sticky; top:110px`, also `margin-top:-108px` to overlap the band beside the image):
    - **Event details card** (`#rsvp`, 18px radius, `0 10px 34px` shadow): header bar in `accent` + category pill; date block (weekday / big day / month in `accent`) + full date; field rows — **Time** (start–end + "Doors open" from `doors_time`), **Location** (`location_type ≠ online`: venue + address + "Get directions →"), **Online** (`location_type ≠ in-person`: Zoom label + join link), **Cost**, **RSVP** status; **RSVP button** (`accent` fill, label flips on `rsvp_required`); **Add to calendar** Google / iCal `outline-red` (hrefs stubbed).
    - **Contact card** (off-white 16px radius): name + email + phone.
    - **Share card** (off-white): "Copy link" → "Copied ✓" (`navigator.clipboard`, reuse Blog Post interaction) + "Email this event" mailto.
- **More upcoming events** (off-white `#F7F5F2` band, toggle `showRelated`): `h2` + "Full calendar →"; 3 cards (category date block + tag + title + meta) → sibling event pages. **No ACF field** — query = next 3 events by `event_date`, exclude current.
- Props: `category` · `locationType` · `rsvpRequired` · `showRelated` · `specMode` (see § Tweakable settings). In production the first three come from the event's own fields.

## Data types
```ts
interface ChapterEvent {
  id: string; date: string;            // ISO yyyy-mm-dd
  time: string;                        // display string, e.g. "7:00–8:30 PM"
  cat: 'chapter'|'poled'|'mutual'|'labor'|'electoral'|'social';
  title: string; location: string; desc: string; rsvpUrl?: string;
  // Single Event template adds (event_details ACF group + event_body):
  summary?: string;                    // event_summary — hero lede
  startTime?: string; endTime?: string; doorsTime?: string;
  locationType?: 'in-person' | 'online' | 'hybrid';
  locationName?: string; locationAddress?: string; onlineUrl?: string;
  cost?: string; rsvpRequired?: boolean; capacity?: number;
  featuredImage?: string; alt?: string;
  body?: EventBlock[];                  // event_body flexible content
}
type EventBlock =
  | { type: 'prose'; html: string }
  | { type: 'agenda'; items: { title: string; desc?: string }[] }
  | { type: 'good_to_know'; items: string[] }
  | { type: 'a11y_note'; html: string }
  | { type: 'map'; address: string };  // rendered when locationType !== 'online'
interface EventCategory { id: string; label: string; color: string | null }
  id: string; title: string; slug: string;
  cat: ChapterEvent['cat'];            // same taxonomy color set
  date: string; excerpt: string; dek?: string;
  bylineMode: 'named' | 'committee'; author?: string; committee?: string;
  featured?: boolean; readMinutes?: number;
}
```

## Tweakable settings (→ WP options in Phase 6)
`joinUrl` (default `https://www.dsausa.org/join`) · `eventCount` on Home (1–6, default 5) · `showCountiesStrip` (bool) · About `showSidebar` / `showPhoto` (bool) · Get Involved `showFaq` (bool) · Calendar `defaultView` (`month|list`) · `showCategoryColors` (bool) · `showSubscribe` (bool) · Blog Post: `bylineMode` (`named|committee`, per-post ACF select) · `showMetaRail` (bool, default off) · Single Event: `category` (enum, per-event term color) · `locationType` (`in-person|online|hybrid`, per-event) · `rsvpRequired` (bool, per-event) · `showRelated` (bool, default on).

## Known placeholders (flag for the chapter, don't invent)
About-page history timeline `20XX` years/milestones · `hello@example.org` email · Instagram `@dsa_rgv` (real) · WhatsApp invite links · subscribe/RSS endpoints · the Who-We-Are & chapter photos (`image-slot` drop zones in the prototypes → real featured images in production) · **all blog copy (titles, excerpts, prose, quotes, captions, author names) is intentionally lorem ipsum** · Spanish translations ("Léelo en español" / ES toggle are stubs — ES is `title="Español — próximamente"`).
