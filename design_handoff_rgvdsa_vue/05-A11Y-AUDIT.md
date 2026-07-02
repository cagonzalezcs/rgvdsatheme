# 05 — Accessibility Audit & Perf Pass (2026-07-02)

Follows `04-BUILD-STATE-HANDOFF.md`. Covers Deferred items #1 (a11y) and #2 (bundle/fonts). Done on branch `cg/poc/blog-implementation`.

## What was changed (committed code)

- **Global focus indicator** — `src/css/tailwind.css` had `--ring`/`--color-ring` vars but no `:focus-visible` rule. Added a base-layer rule (`a, button, input, select, textarea, summary, [tabindex]:focus-visible`) → 3px ink outline, 2px offset, 6px radius, per `03-DESIGN-SPEC.md` § Accessibility (2.4.7). Verified live: nav links, Join button, and the shadcn dropdown trigger all render `outline: 3px solid #1c1917; offset 2px; radius 6px` on keyboard focus.
- **`--ring` → ink** — was `#e9252e` (brand-red), spec says ink. Now `#1c1917`. shadcn/reka components drive their ring off `--color-ring`, so their focus is now ink too.
- **`lang="es"`** — added to the disabled ES `<span>` fallback in `SiteHeader.vue` (the enabled ES `<a>` already had it). `<html lang="en-US">` is already emitted server-side via `language_attributes()`, so no JS `documentElement.lang` setter was added (it would clobber the correct WP value).
- **a11y lint tooling** — added `eslint-plugin-vuejs-accessibility` (flat/recommended) to `eslint.config.ts`. Triaged: relaxed `label-has-for`/`form-control-has-label`/`no-static-element-interactions` for vendored `components/ui/**` + dev-only styleguide (false positives for a reusable-primitive library); `BlockAudio` `media-has-caption` disabled inline (audio-only content → transcript link satisfies WCAG, caption `<track>` is for video). `npm run lint` is clean.
- **Fonts → woff2 + preload** — the 9 referenced faces were `.ttf` (~330 KB/weight Montserrat). Converted to `.woff2` (Montserrat ~100 KB, Open Sans ~59 KB), updated `@font-face` in `tailwind.css`. Added a manifest-driven `wp_head` preload (`StarterSite::preload_fonts`, priority 2) that reads `dist/manifest.json` and preloads the above-fold faces (Open Sans 400, Montserrat 700/800) — hashes change per build so it can't be hard-coded. Verified live: 3 `<link rel=preload as=font type=font/woff2 crossorigin>` print before the stylesheet; fonts fetch 200. Source `.ttf` left in `static/fonts/` (unreferenced → not bundled).
- **Bundle warning** — added `build.chunkSizeWarningLimit: 700` to `vite.config.js`. The 586 kB `Styleguide` chunk is a lazy import gated to `/styleguide` (never on real pages), so the 500 kB warning was cosmetic. Build is now warning-free.

## Audit method

axe-core 4.10.2 injected via the Chrome extension, run against `https://rgvdsa.test:8890` on: front, calendar, blog archive, single post (sticky post w/ all 10 block types). About + Get Involved share the same chrome + interior template. Keyboard focus + both a11y-widget modes verified live.

## Audit result: foundation is solid

- **0 critical, 0 serious structural** issues on every page. reka-ui dialogs/sheets/menus/accordion give correct roles + focus-trap + Esc; skip-link, `aria-current`, `aria-live` month label, `role=status` result counts, `aria-label` on icon controls all present and working.
- **Both widget modes verified**: reduce-motion injects `*{animation/transition:none}`; high-contrast injects the `[data-tone]` overrides + white body; text-size "large" → 18px root.
- **No hover-contrast trap** (the documented "real QA bug"): the hand-rolled calendar + blog toolbars toggle to `bg-ink text-cream` and hover-invert to `brand-red-deep` + light text. No red-fill/red-label anywhere.
- Moderate/minor axe flags are non-actionable: `aria-allowed-role` is entirely the WordPress **admin bar** (logged-in only, WP core markup); `region`/`landmark-complementary` are the skip-link and a nested meta-rail `<aside>` — benign.

## THE ONE REAL FINDING — brand-red contrast (owner decision)

The primary brand red **`#e9252e` fails WCAG AA 4.5:1 for normal text against every neutral**, so it's the sole source of every serious axe flag:

| Pairing | Ratio | Needs | Where |
|---|---|---|---|
| cream `#faf4ea` ↔ brand-red `#e9252e` | **4.02** | 4.5 | header nav (About/Calendar/Get Involved), EN/ES toggle, buttons, prose links |
| brand-red `#e9252e` on white `#fff` | 4.4 | 4.5 | red-on-white text |
| brand-red `#e9252e` on ink `#1c1917` | 3.97 | 4.5 | red text on dark bands |
| cream on labor-brown `#a3641c` | 4.36 | 4.5 | "labor" category tag/chip |
| dimmed via `opacity-55/65` | 2.25–3.46 | 4.5 | out-of-month calendar cells, disabled ES span, hover wash |

This is a **palette decision, not a code bug** — it can't be fixed without repainting the brand, so it was **not** changed unilaterally. Notes:
- The high-contrast widget mode already maps red → `#9e0b13`, which passes — so an escape hatch exists, but the default theme still fails AA.
- `#e9252e` is a load-bearing token (canonical category color for `chapter`; also `--primary`). Category colors are WP term-meta editable; the brand red is in `tailwind.css` + term meta.

### Options (need owner pick)
1. **Darken the brand red** used behind/as text to ~4.5:1 with cream (roughly `#ce1b22`–`#c9161d`) — cleanest AA fix, changes brand hue slightly everywhere.
2. **Use `brand-red-deep` `#9e0b13` for text contexts** (nav labels, links, small text) while keeping bright `#e9252e` for large display/decorative fills (large text only needs 3:1). More surgical, keeps the bright red as an accent.
3. **Accept AA-large / document exception** — keep the palette, rely on the high-contrast widget. Fails a strict AA gate.
4. Also decide: nudge `labor` category color `#a3641c` (4.36) and reduce `opacity-*` dimming on out-of-month cells / disabled ES.

### Resolution applied (Option 2)

Owner chose **Option 2**. `brand-red-deep #9e0b13` is now used for red **text on light surfaces**;
bright `#e9252e` stays for large display + decorative fills. Contrast verified: `#9e0b13` on cream
`#faf4ea` ≈ **7.6:1**, on white ≈ **8.4:1** (AA + AAA).

What changed:
- **Red text on light** (`text-brand-red` → `text-brand-red-deep`): prose links (`.prose-rgv a`,
  hover→ink), header EN chip + Join label, footer nav-link hover, a11y-widget trigger, FAQ trigger +
  chevron, blog eyebrows / share links / "All posts" / featured tag, and the Twig eyebrows / committee
  names / links across `front-page`, `page-about`, `page-get-involved`, `page`.
- **Buttons/toggles with cream-on-red small text → darkened fill to `#9e0b13`**: event-dialog RSVP,
  mobile Join, a11y-widget active state, and the two tiny chips (calendar today-pill, home date badge).
- **Red text/hover on ink bands → light instead of deep** (deep fails worse on dark): footer contact
  hover + subscribe RSS hover → white; bright-red hover regressions on light → ink.

What was intentionally **kept bright** (`#e9252e`):
- Signature **bands** carrying cream text (sticky header, `PageHeader`, blog/home hero) — the residual
  small-text-on-band cases are a documented **AA-large exception**, covered by the high-contrast widget
  which maps `data-tone="red"` → `#9e0b13`. *(Owner Q pending: darken these bands too?)*
- Large display **numerals** (`text-[1.8rem]/[2rem]`, pass 3:1), decorative **stars**, and media
  **play-button icons** (graphical, 3:1).

Confirmed no shadcn `--primary` buttons render on real pages (only the styleguide), so the global
`--primary` token was left bright. **Still open (deferred):** `labor` category color `#a3641c` (4.36)
and `opacity-55/65` dimming on out-of-month cells / disabled ES.

## Still deferred (unchanged from doc 04)
Email-subscribe endpoint (#3), ES i18n content (#4), per-event ICS (#5), tag archives (#6), PR to main (#7), owner repo decisions (#8: commit plugins?, delete stock content?, sign commits?).
