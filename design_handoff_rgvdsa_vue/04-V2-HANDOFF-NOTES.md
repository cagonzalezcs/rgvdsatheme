# 04 — v2 Implementation Handoff Notes

Read this first. It tells you **which files are the real source of truth for the v2 build**, what "v2" changed from the first prototypes, and the handful of gotchas that will bite an implementer. It sits on top of the existing package — it does **not** replace it:

- Tokens, type scale, per-page anatomy, a11y baseline → **`03-DESIGN-SPEC.md`** (already written for v2).
- Stack / island architecture / branching → **`01-ARCHITECTURE.md`**.
- Phased build plan → **`02-PHASES.md`** (see the corrections at the bottom of this doc — a couple of lines still describe the old look).

---

## 1. What "v2" is

v2 is a **full visual re-skin** of the same seven screens — same layout, copy, IA, and interactions; new skin. The direction moved from an early **neobrutalist** treatment to the **warm, rounded** system that `03-DESIGN-SPEC.md` documents.

| | v1 (neobrutalist — superseded) | **v2 (build this)** |
|---|---|---|
| Page background | cream `#FAF4EA` | white `#FFFFFF`; alt sections `#F7F5F2` |
| Depth | hard offset shadows `6px 6px 0`, `8px 8px 0` | soft blurred shadows (`0 2px 10px …`, `0 14px 36px …`) |
| Corners | square (0 radius), hard borders | pills for actions, 14–20px cards, 999px chips |
| Borders | 2–3px solid ink `#1C1917` everywhere | 1px hairlines `#ECE6DA`; 2px `red` only on outline buttons |
| Display type | UPPERCASE, weight 900 | sentence case, weight 700/800; uppercase only for eyebrows/tags |
| Red | single `#E9252E` | bright `#E9252E` for bands + deeper `#B01B22` for text/controls/links |
| Header | 76px, 3px ink bottom border | 64px, soft shadow, no border; nav links larger (1.17rem) |
| Nav hover | underline | rounded translucent-ink hover pill `rgba(28,25,23,0.18)` |

If you see cream `#FAF4EA`, `6px 6px 0` / `8px 8px 0` shadows, or uppercase `h1`s anywhere, that's **stale v1** — ignore it.

---

## 2. Canonical v2 source files (use these, ignore the rest)

There are three copies of these screens in the project. Only one set is v2, and it exists in two identical forms:

**v2 (warm/rounded) — the build target:**
- Project root: `RGV DSA Home v2.dc.html`, `About v2.dc.html`, `Get Involved v2.dc.html`, `Calendar v2.dc.html`, `Blog v2.dc.html`, `Blog Post v2.dc.html`, plus `Interior Page Template.dc.html` (single version — already v2).
- `design_handoff_rgvdsa_vue/designs/*.dc.html` — **byte-for-byte the same v2 files**, differing only in internal link filenames (they link to `About.dc.html` etc. instead of `About v2.dc.html`). Either set is fine to read; the `designs/` folder is the one the phase docs reference.

**v1 (neobrutalist) — do NOT implement:**
- The root files **without** the ` v2` suffix (`RGV DSA Home.dc.html`, `About.dc.html`, `Blog.dc.html`, `Blog Post.dc.html`, `Calendar.dc.html`, `Get Involved.dc.html`). These are the earlier look, kept only for reference. Deleting them is safe once the team agrees v2 is final.

Bottom line for a Claude Code agent: **read `design_handoff_rgvdsa_vue/designs/` (or the root `* v2` files); treat `03-DESIGN-SPEC.md` as the value dictionary.** The link-name difference between the two v2 copies is irrelevant to the Vue build — links become real Vue Router / WP routes.

---

## 3. What's functionally new in v2 (beyond the re-skin)

Two behavioral changes rode along with the v2 skin:

### 3a. Cross-page anchor deep-links now resolve to real pages
v1 stubbed most nav to same-page anchors (`#about`, `#footer`). v2 links the header **About ▾** dropdown, footer, and in-page CTAs to the actual destination pages + anchors, e.g. `About#chapter`, `About#mission`, `About#counties`, `Get Involved#committees`, `About#bylaws`, `About#faq`, `Interior#documents`, `Interior#grievance`. Port these as real routes with a hash → the target section's `scroll-margin-top:110px` (already in the markup) handles the sticky-header offset.

### 3b. EN/ES language toggle — **now functional, but only on Home** ⚠️
This is the one real inconsistency to resolve before you build the shared header.

- **Home (`RGV DSA Home v2`)** ships a *working* toggle: EN/ES are `<button>`s with `aria-pressed`, they set `document.documentElement.lang`, and persist the choice to `localStorage['rgv-dsa-lang']`. (There's still no Spanish content — it flips the flag only.)
- **Every other v2 page** still has the **static** affordance: two `<span>`s, ES marked `title="Español — próximamente"`, no click, no persistence.

**Decision needed / recommended resolution:** build **one** `LanguageToggle.vue` and use the Home behavior everywhere (buttons, `aria-pressed`, `document.documentElement.lang`, `rgv-dsa-lang` persistence), with ES still deferred (no i18n yet). Don't reproduce two different headers. Note `01-ARCHITECTURE.md` §a11y and `02-PHASES.md` still describe the toggle as "present-but-disabled" — the Home page has already moved past that; follow Home.

### 3c. A11y widget — unchanged, and it's the shared-header keystone
Identical behavior across all v2 pages (this part is consistent): text size A/A+/A++ → root font 16/18/20px; High-contrast and Reduce-motion toggles; persisted to `localStorage['rgv-dsa-a11y']` as `{textSize, highContrast, reduceMotion}` and re-applied on load. High-contrast keys off each band's `data-tone` attribute (`red` → `#9E0B13`, `ink` → black/white, `cream` → white/black). Implement once as `useA11ySettings()` + `A11yWidget.vue` (already specced in `01`/`03`). Keep the `data-tone` attributes on every section — they're the high-contrast hook.

---

## 4. Per-page tweakable props (already declared in each v2 file)

These are the `data-props` on each prototype → map to WP options / ACF fields (Phase 6). Defaults shown.

- **Home** — `eventCount` (range 1–6, def 5) · `showCountiesStrip` (bool, def true) · `joinUrl` (text, def `https://www.dsausa.org/join`).
- **About** — `pageTitle` (text) · `lede` (text) · `showSidebar` (bool) — (also `showPhoto` per `03`).
- **Get Involved** — `joinUrl` (text) · `showFaq` (bool, def true).
- **Calendar** — `defaultView` (`month`|`list`, def month) · `showCategoryColors` (bool, def true) · `showSubscribe` (bool, def true).
- **Blog** — `specMode` (bool, def false — the "</> ACF spec" annotation overlay; prototype-only, don't ship).
- **Blog Post** — `bylineMode` (`named`|`committee`, def named — per-post ACF select) · `showMetaRail` (bool, def false) · `specMode` (bool, prototype-only).
- **Interior Page Template** — `pageTitle` · `breadcrumbSection` (def "About") · `lede` · `showSidebar`.

`specMode` on the two blog files drives the in-prototype field-mapping overlay for implementers; it is **not** a production setting — read it, then drop it.

### Home hero art (updated)
The Home hero's right-column art is now the **full vertical DSA lockup**, `assets/logo-vertical.png` (was `cactus-mark-red.png`). It's a tightly-cropped 312×587 PNG whose background is exactly `#E9252E` — identical to the hero band — so it sits edgelessly on the red with no card or padding. Displayed at `width:clamp(240px, 30vw, 360px)`, `height:auto`, centered. Ship this asset as the hero image; `cactus-mark-red.png` is no longer referenced by Home.

---

## 5. Shared chrome — build once, reuse on all seven pages

Every v2 page repeats the same header, footer, skip link, and `<style>` head block inline. In the Vue build these collapse to shared components (per `01`/`03`):

- `SkipLink.vue`, `SiteHeader.vue` (logo `logo-red.png` h58; About▾ dropdown; `LanguageToggle.vue`; `A11yWidget.vue`; Join DSA CTA), `SiteFooter.vue` (logo `logo-green.png`, 4 nav columns, ink bottom bar).
- The `<helmet><style>` block (font-faces, `scroll-behavior`, body reset, the global `:focus-visible` outline) becomes `tailwind.css` globals — **not** repeated per component.
- Active-page nav state: underline offset 6px / 3px + `aria-current="page"` (spec §SiteHeader).

---

## 6. Corrections to the existing phase docs (leftover v1 wording)

A few lines in `02-PHASES.md` predate the v2 rename and still say "brutalist." When you hit them, follow **v2 / `03-DESIGN-SPEC.md`**, not the literal text:

- Phase 2 rollout note says *"verify the brutalist theme holds (radius 0, ink borders, hard shadows)"* → should read **radius 14px / pill actions, hairline borders, soft blurred shadows**. Re-theme via the shadcn semantic vars first, targeted component edits second.
- `01-ARCHITECTURE.md` and `02-PHASES.md` describe the language toggle as *"present-but-disabled ('próximamente')"* → Home has upgraded it to a functional, persisted toggle (§3b). Build the functional version site-wide; ES content stays deferred.

Everything else in `01`/`02`/`03` is v2-correct.

---

## 7. Placeholders to fill (unchanged from `03` — don't invent)

About-page history timeline `20XX` years/milestones · `hello@example.org` · WhatsApp invite links · subscribe/RSS endpoints · Who-We-Are & chapter photos (striped placeholders → real featured images) · **all blog copy is intentional lorem ipsum** · Spanish translations (ES toggle flips the flag only). Instagram `@dsa_rgv` is real.
