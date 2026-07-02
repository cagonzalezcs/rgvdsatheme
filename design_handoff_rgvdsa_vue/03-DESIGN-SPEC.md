# 03 — Design Spec: Tokens, Components, Interactions

Source of truth: the prototypes in `designs/`. This doc extracts the values so agents don't have to re-derive them.

## Visual language
Neobrutalism: **no border radius anywhere**, hard offset shadows with zero blur, thick ink borders (2–4px), flat saturated fills, uppercase display type. Hover states move the element into its shadow (translate) or swap to the deep-red fill — never opacity fades or soft glows.

## Tokens

### Colors
| Token | Hex | Usage |
|---|---|---|
| `brand-red` | `#E9252E` | Primary. Header, hero, page-header bands, accents, primary shadow accent |
| `brand-red-deep` | `#9E0B13` | Hover/active fill on red & CTAs; high-contrast red |
| `cream` | `#FAF4EA` | Page background; text on red/ink |
| `ink` | `#1C1917` | Text, borders, hard shadows, dark bands |
| `white` | `#FFFFFF` | Cards, calendar day cells, callouts |
| `ink-soft` | `#3A352F` | Dividers on ink |
| `muted-on-ink` | `#CFC8BD` | Body text on ink |
| `muted-on-cream` | `#5C544A` | Secondary text on cream |
| `muted-2` | `#6B6257` | Tertiary text, off-state labels |
| `border-muted` | `#B7AC9B` | Muted borders, dashed empty states |
| `divider-cream` | `#E3D9C6` | Row dividers on cream |
| `cell-outmonth` | `#F0E7D6` | Calendar out-of-month cells (at 0.55 opacity) |
| `placeholder stripes` | `#F1E8D8` / `#E9DFCC` | 45° repeating stripes for image placeholders |

Event category colors (also the taxonomy term colors in WP — **shared by post categories on the blog**):
`chapter` Chapter-Wide `#E9252E` · `poled` Political Education `#3A5BA0` · `mutual` Mutual Aid `#1F7A48` · `labor` Labor `#A3641C` · `electoral` Electoral `#7C4396` · `social` Social `#0E7C86`

### shadcn semantic variable mapping (`:root`)
```
--background: cream        --foreground: ink
--card: #FFFFFF            --card-foreground: ink
--primary: brand-red       --primary-foreground: cream
--secondary: ink           --secondary-foreground: cream
--muted: #F1E8D8           --muted-foreground: #5C544A
--accent: brand-red-deep   --accent-foreground: #FFFFFF
--destructive: brand-red-deep
--border: ink              --input: ink            --ring: brand-red
--radius: 0px
```
Plus: default component border-width 2px (3px for cards/dialogs/popovers); popover/dialog shadow `6px 6px 0 ink`; card shadow none by default, `8px 8px 0` in red or ink for featured cards.

### Shadows (hard, zero blur, always)
`5px 5px 0 ink` (buttons) · `6px 6px 0 ink` (dropdowns, popovers, list-row hover) · `8px 8px 0 #E9252E` (photo frames, callout cards) · `10px 10px 0 ink` (dialogs). Button press: hover `translate(2px,2px)` + shadow 3px; active `translate(5px,5px)` + shadow 0.

### Typography
- **Display: Montserrat** — headings, nav, labels. Weights 600/700/800/900.
- **Body: Open Sans** — everything else. Weights 400/600/700/800. Fallback `system-ui, sans-serif`.
- `h1`: Montserrat 900, uppercase, `clamp(2rem, 4.8vw, 3.4rem)` (hero: `clamp(2.3rem, 5.8vw, 4.4rem)`), line-height 1.08–1.1, letter-spacing −0.01em.
- `h2` section: Montserrat 900 uppercase `clamp(1.8rem, 4vw, 2.9rem)`; `h2` in-content: Montserrat 800 uppercase 1.5rem with 3px ink bottom border, 10px padding-bottom.
- Eyebrow/kicker: 0.85–0.9rem, weight 800, letter-spacing 0.18em, uppercase, red on cream / cream on red.
- Body: 1–1.2rem, line-height 1.6–1.75. Nav links: Montserrat 600 0.95rem. Micro-labels: 0.7–0.85rem, 800, tracking 0.05–0.12em, uppercase.
- Root font-size is the a11y text-size lever: 16 / 18 / 20px. Use `rem` everywhere.

### Layout
Content max-width **1100px** (header/footer bars 1200px), side padding 24px. Section vertical padding 96px (page-header band 48/56). Interior grid: `minmax(300px,1fr) 300px`, gap 56px. Header min-height 76px, sticky, `border-bottom: 3px solid ink`.

## Chrome components

### SiteHeader (red band, `data-tone="red"`)
Logo (`logo-red.png`, h 62px) · nav: **About ▾** dropdown — six items deep-linking to the About page anchors: About the Chapter `/about#chapter`, Mission & History `#mission`, Counties We Serve `#counties`, Committees `#committees`, Bylaws & Code of Conduct `#bylaws`, FAQ `#faq` (cream panel, 3px ink border, 6px hard shadow, items hover `#9E0B13`/white; `About.dc.html` is canonical for this menu), **Calendar**, **Blog**, **Get Involved** links (cream text, hover underline offset 4px) · right cluster: EN/ES toggle (2px cream border; EN active = cream fill/red text; ES disabled 0.65 opacity, title "Español — próximamente"), **Aa** a11y button, **Join DSA** CTA (cream fill, red text, uppercase, hover deep-red). Active page link = persistent underline + `aria-current="page"`.

### A11yWidget (Popover under "Aa")
280px cream panel, 3px ink border, 6px shadow. Contents: "ACCESSIBILITY" label; **Text size** segmented control A/A+/A++ (active = ink fill/cream text) → sets `document.documentElement.style.fontSize` to 16/18/20px; **High contrast** and **Reduce motion** rows with On/Off pills (on = red fill/cream text, ink border; off = transparent, `#B7AC9B` border, `#6B6257` text).
Persistence: localStorage `rgv-dsa-a11y` = `{textSize, highContrast, reduceMotion}`; applied on every page load.
High contrast injects tone overrides: `[data-tone="cream"]` → white bg/black text; `[data-tone="red"]` → `#9E0B13` bg; `[data-tone="ink"]` → black bg/white text. Every band component keeps its `data-tone` attribute — that's the hook. Reduce motion kills all animation/transition + smooth scroll. Vue port: `useA11ySettings()` composable owning state/persistence/application; widget is presentation.

### SiteFooter (cream, 3px ink top border)
Grid `minmax(240px,1.2fr) repeat(auto-fit, minmax(160px,1fr))`, gap 40px. Logo `logo-green.png` (220px) + tagline ("Organizing across Hidalgo, Cameron, Willacy, and Starr counties."). Columns: About / Get involved / Resources / Contact (links hover red). Ink bottom bar: org name + "Built to be accessible — tell us how we can do better".

### PageHeader (red band)
Breadcrumb (Home / Page, 0.85rem 700 uppercase) + H1 + lede (1.15rem, max 60ch).

### Buttons (cva variants)
- `primary`: cream fill, red text, 3px ink border, `5px 5px 0` ink shadow, uppercase 800, padding 16px 32px; hover/active press behavior above.
- `outline-light`: transparent, 3px cream border, cream text; hover deep-red fill.
- `outline-ink`: transparent, 2px ink border, ink text, uppercase 0.9rem; hover deep-red fill/white text.
- `link-accent`: red text 800 uppercase w/ 3px red bottom border; hover deep-red.

## Pages

### Home
1. **Hero** (red): eyebrow "RIO GRANDE VALLEY DSA ★ FROM ROMA TO BROWNSVILLE"; H1 "A better world is possible. We're building it in the Valley." (max 18ch, balanced); lede ¶; CTAs [Join DSA primary] [Come to a meeting ↓ outline-light]; footnote "New here? Start with RGV-DSA 101…". Padding 88/96.
2. **CountiesStrip** (ink): centered uppercase Montserrat 800 city names separated by red ★. Toggleable (`showCountiesStrip`).
3. **WhoWeAre** (cream): grid `minmax(300px,1.1fr) minmax(280px,0.9fr)` gap 56; eyebrow red; H2; 2 ¶; link-accent "More about our chapter →". Photo frame: 3px ink border, `8px 8px 0 #E9252E` shadow, 4:3, striped placeholder + monospace caption until a real photo exists.
4. **UpcomingEvents** (cream): H2 + "Full calendar →" over 3px ink rule; rows grid `88px 1fr auto` gap 24, divider `2px #E3D9C6`: red date block (2px ink border, day Montserrat 900 1.45rem / month 0.75rem 800), title 1.15rem 800 + meta `#5C544A`, "View event" outline-ink.
5. **FromTheBlog** (cream): H2 "From the blog" + "All posts →" over 3px ink rule; grid `minmax(300px,1.15fr) minmax(280px,1fr)` gap 36 — left: featured post card (3px ink border, 16:9 striped image area w/ category tag, date · read-time micro-label, uppercase Montserrat 900 title, excerpt, red "Read the post →"); right: 2 stacked compact rows (`140px 1fr` grid, striped thumb, category tag, 1.1rem 800 title, date). Hover: hard-shadow lift.
6. **GetInvolvedSteps** (ink): eyebrow + H2 "Three steps to start organizing"; 3 cards `repeat(auto-fit,minmax(260px,1fr))` gap 32 — 4px red top border, big red number (Montserrat 900 2rem), bold title, `#CFC8BD` body, red-underline link. Social strip below `2px #3A352F` rule: Instagram (cream fill chip) + Email (outline chip).

### About (`About.dc.html`)
PageHeader ("About RGV DSA" / lede, max 60ch). Directly below: ink **mission band** (padding 72/24) — red eyebrow "WHAT WE BELIEVE" + Montserrat 800 statement `clamp(1.4rem,3vw,2.2rem)`, line-height 1.35, max 32ch. Then content+sidebar grid. Six sections mirror the header About dropdown 1:1; every h2 anchor has `scroll-margin-top:100px` (sticky header clearance):
- `#chapter` **About the Chapter** — 2 ¶ (1.1rem then 1.05rem, /1.75) + photo slot (full-width, 340px, 3px ink border, striped placeholder) + CTA row: "Come to a meeting" (ink fill/cream, uppercase 800) + "Get involved" / "Students: UTRGV YDSA" (outline-ink).
- `#mission` **Mission & History** — 1 ¶ + timeline: white block, 2px ink border; rows grid `110px 1fr` gap 16, padding 16/20, 2px ink row dividers; year = Montserrat 900 red 1.05rem. Rows: 1982 DSA founded; two `20XX` chapter milestones — **years are placeholders for the chapter, don't invent**.
- `#counties` **Counties We Serve** — 1 ¶ + 4 cards `repeat(auto-fit,minmax(220px,1fr))` gap 14 (white, 2px ink border, padding 20): Montserrat 800 uppercase county name; muted `#5C544A` 0.9rem city list (McAllen gets a red ★); Hidalgo adds red uppercase micro-label "Home base — most meetings held here".
- `#committees` **Committees** — 1 ¶ + definition-style rows in one white 2px-ink-border block: grid `200px 1fr` gap 16, padding 14/20, 2px ink dividers; red uppercase Montserrat 800 0.95rem name + 0.95rem description. Same six committees/copy as Get Involved — share one fixture. Below: link-accent "Join a committee →" to Get Involved `#committees`.
- `#bylaws` **Bylaws & Code of Conduct** — 1 ¶ + a real `<table>` (2px ink border, white; `border-collapse:collapse`): ink header row (cream Montserrat 800 uppercase 0.85rem: Document / What it covers / action col 90px); rows (2px ink dividers, `th scope="row"` bold nowrap doc name): Chapter Bylaws, Code of Conduct, Grievance Policy, Meeting Minutes; action = red uppercase "Read"/"Browse" links into the Interior template (`#documents`, `#grievance`).
- `#faq` **FAQ** — informational **tabular Q&A, NOT the accordion** used on Get Involved: one white 2px-ink-border block; rows grid `minmax(200px,2fr) 3fr` gap 20, padding 16/20, 2px ink dividers; bold 0.98rem question / 0.95rem answer. 6 rows: events-without-membership, dues amount, **switching to monthly/Solidarity Dues**, no-experience, privacy, time commitment. Below: **dues CalloutCard** `#dues` (white, 3px ink border, `8px 8px 0 #E9252E`, red uppercase kicker "Switching your dues rate?", body ¶, "Update my dues" ink-fill button → join URL).
Sidebar: "On this page" nav = the same six labels/anchors as the dropdown (3px ink left border) + ink "New here?" card (same as Get Involved). Footer About column also deep-links to these anchors.

### Get Involved
PageHeader ("Get involved" / "No experience needed, no perfect politics required…"). Content+sidebar grid. Sections (in-content h2 style, anchors): `#join` How to Join, `#committees` Committees (cards; copy in prototype), `#channels` Communication Channels, `#faq` FAQ → **Accordion** (2px ink borders, bold question rows). Sidebar: cards per prototype (see file).

### Interior Page Template (default `page.twig`)
PageHeader driven by WP title + excerpt/lede. Prose styles matching prototype (h2 rule style, 1.05–1.1rem/1.75 body). `#documents` list; `#conduct` section; `#grievance` **CalloutCard**: white, 3px ink border, `8px 8px 0 #E9252E`, red uppercase kicker "Need to report something?", body w/ mailto.

### Calendar
Toolbar row: month label (Montserrat 900) + prev/next; view toggle Month|List (segmented, active ink fill); filter chips: "Filter:" label + All events + 6 category chips (2px ink border; active ink fill/cream text; 10px color swatch square).
- **Month grid**: ink 2px gap lines (grid gap on ink bg); weekday header row; cells white min-height 112px (out-of-month `#F0E7D6` @ 0.55); today's date number = red fill/cream; event chips = category-color fill, white 0.74rem 700 text, ellipsized, full title+time in tooltip/`title`.
- **List view** (per current month, date-sorted): white rows, 3px ink border; 72px date block in category color (weekday/daynum/month); category tag (2px category-color border + text, uppercase 0.7rem); title + `time · location` meta; hover: `6px 6px 0 ink` + `translate(-2px,-2px)`. Empty state: dashed `#B7AC9B` border, "No events in this category this month — try another month or clear the filter."
- **EventDetailDialog**: overlay `rgba(28,25,23,0.55)`; panel cream, 3px ink border, `10px 10px 0 ink`, max-w 520px; header bar in category color (category label + close ✕, 3px ink bottom border); body: title, full date line, time, location, description, RSVP CTA. Esc/overlay closes; focus trapped.
- **Subscribe strip** (ink band): heading + Google Calendar / ICS buttons (hrefs stubbed until Phase 6).
- State: `view` (default via prop `defaultView`), `monthOffset`, `activeCat`, `selectedId`. Sample data: 14 events across Jul–Aug 2026 in the prototype script — use as the fixture and WP seed.

### Blog (archive)
**All blog copy is lorem ipsum placeholder** — implement layout, keep placeholder text as fixtures. Both blog prototypes have a bottom-left **"</> ACF spec"** toggle that overlays field-mapping annotations; the notes below summarize them.
- **PageHeader**: breadcrumb Home / Blog; H1 "Chapter Blog"; lede.
- **Toolbar** (cream): search input (3px ink border, ink ⌘ block w/ ⅌ glyph) + "Filter:" label + chips: All posts + 6 category chips (2px ink border; active ink fill/cream text; 11px color swatch square). State syncs to URL: `?category=slug`, `?s=term`.
- **Browse state** (no filter/search): **featured post card** — sticky post ?? latest; white, 3px ink border, `10px 10px 0 ink` shadow; 2-col grid `minmax(300px,1.1fr) minmax(300px,1fr)`; left = featured image (3px ink divider), right = "★ FEATURED" red micro-label + category tag (term color fill, 2px ink border) + uppercase Montserrat 900 title (hover deep-red) + dek + byline/date/read-time + "Read the post" button (cream, 3px ink border, 4px hard shadow). Fields: title · dek (ACF text) · category · featured_image.
  Below: **editorial grid** — 6-col grid, gap 24; card spans by index `[3,3,2,2,2,2,2,2]`; cards white, 3px ink border, 16:9 striped image area w/ category tag, date micro-label, Montserrat 800 title (1.3rem on span-3 / 1.05rem on span-2), excerpt on span-3 only; hover `6px 6px 0 ink` + `translate(-2px,-2px)`. Pagination: Newer/Older outline buttons (disabled = `#B7AC9B`).
- **Filter/search state**: layout switches to a 920px column of uniform result rows — result-count line ("N posts · Category · “term”", `role="status"`) + "Clear filters ✕" over 3px ink rule; rows grid `auto 1fr auto`: 14×44 category-color bar, category·date micro-label in term color, title, excerpt, →. Empty state: dashed `#B7AC9B` border, "No posts match".
- **Subscribe strip** (ink band): "Get new posts by email" + email input (3px cream border, transparent) + Subscribe button (cream fill) + RSS link. Form action stubbed until Phase 6.

### Blog Post (single)
- **Hero** (red, padding 48/140 bottom): breadcrumb Home / Blog / post; category tag (term color fill, 2px ink border); uppercase Montserrat 900 H1 (max 24ch, balanced); dek 1.25rem; byline row — **two modes** via `byline_mode` select: `named` (52px circle avatar + "By **Name** · Committee") or `committee` ("By the **X Committee**"); date · read time · "Léelo en español →" stub link.
- **Featured image**: pulled up over the red band (`margin-top:-100px`), 3px ink border, `8px 8px 0 ink` shadow, caption + credit below.
- **Article body** = `post_blocks` ACF flexible content stack (article column max 980px, prose measure `min(66ch,100%)`, block accents inherit the post category term color; every image field pairs with required `alt_text`):
  - `acf/prose` — wysiwyg. 1.15rem/1.8; in-content h2 = Montserrat 800 uppercase 1.5rem w/ 3px ink bottom border.
  - `acf/image` — image · alt_text · caption · credit · breakout (true_false). 3px ink border, `8px 8px 0` category-color shadow.
  - `acf/pull_quote` — quote · attribution. White card, 3px ink border, category-color hard shadow, Montserrat 800 quote, uppercase attribution in category color.
  - `acf/gallery` — repeater (image · alt_text · caption) · layout (essay | grid). 2-col grid, first image full-width; per-figure ink borders + caption bars.
  - `acf/person_quote` — photo · alt_text · quote · translation (optional) · name · role · lang (en|es). 96px circle photo, bilingual quote pair, name in category color.
  - `acf/video` — url (oEmbed) · poster · caption · captions-required reminder. 16:9, 3px ink border, hard shadow, red play button, CC badge, transcript link.
  - `acf/audio` — file · title · duration · transcript (required). White card row: red play button, title, progress bar in category color, mono timestamps, transcript link.
  - `acf/document` — file · title · description. Reuses Interior template's DocumentRow (PDF chip, title, meta, Download outline button).
  - `acf/event_embed` — relationship → event CPT. Renders Calendar's list-row: category-color date block, "Upcoming event" micro-label, title, meta, RSVP outline button.
  - `acf/action_callout` — heading · body · buttons repeater (label · url · style primary|outline). Ink card, `8px 8px 0 #E9252E` shadow, uppercase Montserrat 900 heading, cream primary + outline-light secondary buttons.
- **End matter**: tags row (category tag + muted-border tag chips) + share (Copy link w/ "Copied ✓" state, Email); **author card** (white, 3px ink border) — avatar (named) or committee-initials block (committee), red "About the author" kicker, name, bio, committee link.
- **Meta rail** (optional, `showMetaRail` prop, default off): sticky 280px right rail — "Posted in" tag, share links, ink subscribe mini-card.
- **Read Next**: H2 + "All posts →" over ink rule; 3 cards `repeat(auto-fit,minmax(260px,1fr))` — same card anatomy as archive grid. Query: same category, latest 3, excluding current (no ACF field).

## Data types
```ts
interface ChapterEvent {
  id: string; date: string;            // ISO yyyy-mm-dd
  time: string;                        // display string, e.g. "7:00–8:30 PM"
  cat: 'chapter'|'poled'|'mutual'|'labor'|'electoral'|'social';
  title: string; location: string; desc: string; rsvpUrl?: string;
}
interface EventCategory { id: string; label: string; color: string | null }
interface BlogPost {
  id: string; title: string; slug: string;
  cat: ChapterEvent['cat'];            // same taxonomy color set
  date: string; excerpt: string; dek?: string;
  bylineMode: 'named' | 'committee'; author?: string; committee?: string;
  featured?: boolean; readMinutes?: number;
}
```

## Tweakable settings (→ WP options in Phase 6)
`joinUrl` (default `https://www.dsausa.org/join`) · `eventCount` on Home (1–6, default 5) · `showCountiesStrip` (bool) · `defaultView` (`month|list`) · `showCategoryColors` (bool) · `showSubscribe` (bool) · Blog Post: `bylineMode` (`named|committee`, per-post ACF select) · `showMetaRail` (bool, default off).

## Known placeholders (flag for the chapter, don't invent)
About-page history timeline `20XX` years/milestones · `hello@example.org` email · Instagram `@dsa_rgv` (real) · WhatsApp invite links · subscribe URLs · the Who-We-Are photo · **all blog copy (titles, excerpts, prose, quotes, captions, author names) is intentionally lorem ipsum** · blog subscribe/RSS endpoints · Spanish translations ("Léelo en español" is a stub).
