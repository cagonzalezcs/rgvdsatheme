# 05 — Responsive (Mobile + Tablet) Handoff Notes

Added 2026-07-02. Companion to `04-V2-HANDOFF-NOTES.md`. The original eight prototypes are drawn at desktop width; this doc + the `* Mobile.dc.html` files pin down how each v2 page behaves at **two smaller breakpoints** so the responsive build isn't left to guesswork. Everything here is v2 (warm/rounded) — same tokens, copy, and interactions as desktop, only the layout reflows.

---

## 1. What was added

Six responsive companion prototypes, one per public page. Each file shows **two frames side by side** — a **320px mobile** frame and a **768px tablet** frame — on a pan/zoom canvas:

- `designs/RGV DSA Home Mobile.dc.html`
- `designs/About Mobile.dc.html`
- `designs/Get Involved Mobile.dc.html`
- `designs/Calendar Mobile.dc.html`
- `designs/Blog Mobile.dc.html`
- `designs/Blog Post Mobile.dc.html`

> **Scope:** only the two small tiers were designed (per request). **Interior Page Template** and **Single Event** were not given dedicated responsive frames — they reflow with the same rules documented here (single-column stack, sticky rails drop below content, two-tier header). Apply the patterns below when you build them.

Open any file in a browser; each frame carries a `data-screen-label` (`… — Mobile 320` / `… — Tablet 768`) and a monospace size badge. The `<script>` block holds the same kind of plain-JS state the desktop files use — port behavior, don't paste.

---

## 2. Breakpoints & container widths

Design was done at the two edges; Tailwind's default scale covers the between-space.

| Tier | Design width | Tailwind bucket | Page gutter | Notes |
|---|---|---|---|---|
| **Mobile** | 320px | base (`< sm`) | 16–20px | Narrowest supported. Single column everywhere. |
| **Tablet** | 768px | `md` | 28–40px | Two-tier header; grids collapse to 1–2 cols. |
| Desktop | ≤1220px | `lg+` | 24px, max-w 1140–1220 | The original eight prototypes. |

Rule of thumb: **the desktop layout holds down to `md`; below `md` (base) it becomes the mobile layout.** Most switches are a single `md:` prefix. No custom breakpoints are needed.

---

## 3. Shared chrome — responsive behavior

### 3a. Header (the big one)
Three forms of the **same** `SiteHeader.vue`:

- **Mobile (base):** logo (h40) + a **44×44 hamburger** button, nothing else in the bar. Tapping toggles a full-width **drop panel** on white: the four nav items stacked (About is a plain link to the About page here — the desktop hover-dropdown collapses into it), then a row with the EN/ES toggle + `Aa` a11y button, then a full-width **Join DSA** button. The mobile prototypes ship this panel **open** on Home (so you can see it) and **closed** on the other pages — production default is **closed**; drive it with an `isMenuOpen` ref.
- **Tablet (`md`):** **two-tier** header — top tier = logo (h46) + EN/ES + `Aa` + Join DSA; bottom tier = a centered `#B01B22` nav strip with the four links. No hamburger.
- **Desktop (`lg`):** the existing single-row header (logo h58, inline nav with the About▾ hover dropdown, right-side controls).

The About **hover-dropdown** only exists at desktop. At mobile/tablet, "About" is a direct link to the About page (its six sections are anchored there anyway). Hit targets are ≥44px at every tier (a11y gate).

### 3b. Footer
- **Mobile:** the 4 nav columns **stack vertically**; logo block on top; ink bottom bar becomes a two-line stack.
- **Tablet:** nav columns go to a **2×2 grid** (the "Resources" and "Contact" columns are merged into one on tablet to keep it 2-up); bottom bar stays a single justified row.
- **Desktop:** the existing auto-fit multi-column row.

### 3c. A11y widget + language toggle
Behavior is **unchanged** from `04` §3b/§3c — same `localStorage` keys, same `data-tone` high-contrast hook on every band. Only their **placement** moves: inside the hamburger panel on mobile, in the top tier on tablet, in the right-side control cluster on desktop. Build once, mount in three slots.

---

## 4. Per-page reflow specs

Every page: **red page-header** padding tightens (36–44px mobile / 44–52px tablet vs. 48–56px desktop); `h1` steps down (~1.75rem mobile / ~2.4rem tablet / clamp to 3.4rem desktop); lede drops from 1.5rem to ~1.02rem mobile. Card radii, shadows, colors, and copy are **identical** across tiers.

### Home
- Hero: two-column (text + vertical DSA lockup) → **single column**, lockup drops below the copy and centers (150px mobile / 190px tablet). CTAs go **full-width stacked** on mobile.
- Counties strip: same centered wrap, smaller type.
- Who We Are: text|photo grid → stacked (photo below text).
- Events: 3-col row (`date | title | button`) → mobile becomes a **tappable card** (date chip + title/meta stacked, "View event →" inline; the standalone button is dropped in favor of the whole row being the link). Tablet keeps the 3-col row.
- From the Blog: `1 featured + 2 compact` → **all stacked** on mobile (featured card, then two horizontal mini-cards); tablet = featured full-width + a 2-col row of the two.
- Get Involved (3 steps): 3-col → 1-col stack (mobile) / 3-col (tablet, tighter). Follow-along buttons full-width stacked on mobile.

### About
- Kills the **sticky sidebar**. The "On this page" nav moves **inline, above the article**: a vertical list on mobile, a horizontal **chip row** on tablet.
- Timeline / committee / bylaws / FAQ tables: every `label | value` two-column grid **collapses to stacked** rows on mobile (label above value); tablet keeps two columns but narrows the label track (90–180px).
- Bylaws table stays a real `<table>` on tablet; on mobile it becomes a **stack of cards** (title, description, "Read →"). Dues callout + "New here?" card stack full-width.

### Get Involved
- Same sidebar→inline treatment as About (list on mobile, chip row on tablet).
- How-to-join steps: number-badge + body grid holds, just narrower. Committees: auto-fit grid → 1-col (mobile) / 2-col (tablet). Channels rows go **column** on mobile (label block, then the action button below, left-aligned). FAQ accordion is unchanged (full-width, tap to expand) — **it's an `sc-for` over a `FAQS` array in the mobile file; port that as the canonical accordion data**.

### Calendar ⚠️ (biggest reflow)
- The **month grid does not survive 320px.** Mobile replaces it with an **agenda list** — the same events as compact tappable rows (color date-block + category tag + title + time/place), grouped under the month, with the month nav (`←  July 2026  →`) and the filter chips kept above. The month/list view toggle is **hidden on mobile** (list is the only view); keep it on tablet+.
- **Tablet keeps the full 7-col month grid**, just at 768px with smaller day cells (~86px min-height) and smaller event chips. Weekday header abbreviations stay.
- Event chips/rows link to **Single Event** (per `04` §3d); the desktop modal is optional and not reproduced in the mobile file.
- Subscribe strip: side-by-side → stacked (mobile) / left-text + button row (tablet).
- The mobile prototype carries a working month-nav + category-filter in its `<script>` (two independent instances, `m*` for the agenda + `t*` for the grid) — read it for the filter/agenda logic.

### Blog
- Toolbar: search field + filter chips wrap; search goes full-width on mobile.
- **Browsing state:** featured post (image-top card on mobile, side-by-side on tablet) + the editorial mixed-span grid → **1-col stack** on mobile / **2-col** on tablet (the desktop 6-col span rhythm is a desktop-only flourish; don't force it below `lg`).
- **Filtered/search state:** the uniform result rows stay rows at every tier (color bar + kicker + title + excerpt), just narrower; the `→` chevron is dropped on mobile.
- Both states + the empty state are wired in the mobile file (`mBrowsing`/`mFiltering`, `tBrowsing`/`tFiltering`).

### Blog Post
- Single column throughout (desktop's optional meta rail is **not** shown at these tiers — it would drop below the article; `showMetaRail` is effectively desktop-only).
- Featured image still **pulls up over the red band** (negative top margin: -64px mobile / -84px tablet vs -100px desktop).
- Content blocks reflow individually: prose clamps to a comfortable measure (`min(62ch,100%)`, centered, on tablet; full-width on mobile). **Gallery** → stacked on mobile, wide+2-col on tablet. **Person quote** → photo-above-text (mobile) / photo-beside-text (tablet). **Related-event** + **document** + **audio** blocks go column-stacked on mobile. Video keeps 16:9. Pull quote, action callout unchanged but narrower.
- Tags + share and the author card stack their internals on mobile.
- "Read next": 3-col → 1-col (mobile) / 3-col (tablet). The copy-link button uses the same `navigator.clipboard` behavior as desktop (`mCopy`/`tCopy` in the file).

---

## 5. Implementation guidance (Tailwind/Vue)

- **Mobile-first.** Author base styles for the 320 layout, add `md:` for tablet, `lg:` for the existing desktop. The prototypes give you the two endpoints — the desktop files remain the `lg` source of truth.
- **One header component, three layouts.** Don't ship three headers. Use `md:`/`lg:` visibility + an `isMenuOpen` ref for the hamburger panel. The About hover-dropdown is `lg:`-only; below that it's a link.
- **Calendar is the one place mobile ≠ desktop structurally** (agenda list vs. month grid) — gate the two renderings on the breakpoint (`hidden lg:block` grid / `lg:hidden` agenda), sharing the same filtered-events computed. Everything else is the same DOM reflowing.
- **44px minimum hit targets** at mobile — already true in the prototypes (hamburger 44×44, full-width CTAs, ≥44px rows). Keep it through the a11y gate in `03-DESIGN-SPEC.md`.
- These files reuse the shared `designs/assets/` (logos, fonts) and the `.dc.html` runtime — same porting rules as `04`: read markup/styles/copy/logic, don't paste the runtime.

---

## 6. Not covered (apply the rules, don't wait for a mockup)

- **Interior Page Template** and **Single Event** responsive frames — reflow them with §3 (two-tier→hamburger header, stacked footer) and the §4 patterns (sticky rail → below content; `label|value` grids → stacked; content blocks single-column). Single Event's sticky **details rail** behaves like Blog Post's meta rail: it moves **below** the event body on mobile/tablet (don't keep it sticky-beside at these widths).
- A **1024px laptop tier** was not designed — the desktop files cover `lg` and up; add an `xl:` only if the team wants a wider max-width treatment later.
