# 04 — Build State & Handoff (2026-07-02)

Read this FIRST if you are a fresh agent/model picking up the RGV DSA site. It supersedes the "how to start" framing in `README.md`/`02-PHASES.md` — **the phased build is complete** (Phases 0–7, Phase 7 in its "minimal wrap" form). Your job is maintenance, polish, and the deferred list below.

## Repo & environment

- Git root = the whole WP install: `/Users/cesargonzalez/Sites/rgv-dsa`. Theme: `wp-content/themes/rgvdsatheme`. Branch: `cg/poc/blog-implementation` (not yet PR'd to `main`; tag `v0-pre-vue` marks pre-Vue main).
- Serving: **MAMP PRO** → `https://rgvdsa.test:8890` (self-signed; `curl -k`). Vite dev server: `npm run dev` → `https://rgvdsa.test:8891` (config in theme `.env`; vite-for-wp bridges via `dist/vite-dev-server.json`).
- **wp-cli** (not on PATH; MAMP phar + Homebrew PHP noise + socket workarounds required):
  ```bash
  php -d error_reporting=0 -d display_errors=0 \
    -d mysqli.default_socket=/Applications/MAMP/tmp/mysql/mysql.sock \
    /Applications/MAMP/Library/bin/wp --path=/Users/cesargonzalez/Sites/rgv-dsa <cmd>
  ```
- Theme commands (run in theme dir): `npm run build` (vue-tsc + vite), `npm run typecheck`, `npm run dev`, `composer test`.
- ACF Pro 6.8.5 active. `wp-content/plugins/` is **intentionally untracked** (owner hasn't decided whether to commit ACF Pro).
- Git commits: GPG signing fails in headless harnesses (pinentry needs TTY) → `git commit --no-gpg-sign`; owner re-signs if needed.

## What's built (all seven screens, WP-driven)

Stack: Timber 2 (Twig shells) + Vue 3 islands + Tailwind v4 + project-owned shadcn-vue library. Theme `README.md` documents the island pattern, commands, and conventions — read it.

Key history (one commit per phase): toolchain `8483109` → shadcn init `ae1abb6` → registry batches `0a62ebb`/`9631d`/`c0593` → chrome `5075d46` → pages `83b189b`+`092eaac` → calendar `3cd9f5e` → blog `3f585e8` → **WP wiring `c380e9b`** → **cleanup `5605063`**.

### Data layer (the part built most recently — Phase 6)

- `inc/events.php` — `event` CPT, `event_category` tax + ACF color term meta, event fields (start/end datetime, venue, city, rsvp_url), ICS feed at `/feed/rgvdsa-events/`, per-event Google-Calendar URLs. Public fns: `rgvdsa_event_to_chapter_event()`, `rgvdsa_event_categories()`.
- `inc/blog.php` — category color term meta, post settings (dek, byline_mode, committee, featured caption/credit, read_minutes override, show_meta_rail), `post_blocks` flexible content (10 layouts), serializers `rgvdsa_post_to_blog_post()` / `rgvdsa_post_to_single()`, archive query honoring `?s=`/`?category=` + pagination.
- `inc/options.php` — "Chapter Settings" ACF options page (join/newsletter URLs, contact email, socials, EN/ES flag, event count, counties strip, committees repeater), `rgvdsa_chapter_committees()`.
- `inc/interior.php` — governing-documents repeater on pages.
- Template routers expose filters the inc files hook: `rgvdsa/context/front_page`, `…/page` (w/ `$timber_post`), `…/blog_archive`, `…/single`.
- **Load-bearing invariants**: category term slugs `chapter|poled|mutual|labor|electoral|social` (in URLs and Vue union types — never rename); island props are camelCase JSON via `data-props='{{ …|json_encode|e("html_attr") }}'`; every data prop is optional with a design-fixture default (omit key → prototype content renders — great for dev, but means a silently-missing context key shows lorem, not an error).
- Canonical category palette = the built tailwind hexes (chapter `#E9252E`, poled `#3A5BA0`, mutual `#1F7A48`, labor `#A3641C`, electoral `#7C4396`, social `#0E7C86`). The hexes in `03-DESIGN-SPEC.md` are stale v1; term meta (wp-admin editable) is the runtime source of truth.

### Seeded content (live in the local DB)

`wp eval-file wp-content/themes/rgvdsatheme/bin/seed.php` — idempotent. Already run: 6+6 category terms w/ colors, 14 events (Jul–Aug 2026), 9 lorem posts (first one sticky + carries all 10 block types), Blog page (`page_for_posts`), Primary + 4 footer menus assigned, options filled, bylaws-page documents. Stock "Hello world!"/Sample Page still exist (owner undecided about deleting).

## How to verify anything

- Island payloads are server-rendered into `data-props` — `curl -sk <url>`, regex `data-vue-island="X" data-props='…'`, html-unescape, JSON-parse. (This is how acceptance was verified; no browser needed.)
- Acceptance criteria met on 2026-07-02: all pages 200/no PHP warnings; calendar props = 14 events + 6 cats + real ICS/GCal URLs; archive filtering works server-side; featured post serializes all 10 block types; live create-event/create-post mutation test passed.

## Deferred work (the actual to-do list)

1. **Full a11y pass** (axe + keyboard walk, both a11y-widget modes, hover-contrast audit per `03-DESIGN-SPEC.md` § Accessibility) + cross-browser (Chrome/Firefox/Safari/iOS).
2. **Bundle**: `Styleguide-*.js` chunk 586 kB (>500 kB warning) — split or accept; fonts not yet preloaded; image sizing audit.
3. Email-subscribe strip: form is `@submit.prevent` stub — needs a provider/endpoint (+ options field).
4. ES i18n: header toggle + "Léelo en español" link are affordances only (`es_enabled` option exists, no translated content).
5. Per-event ICS download (dialog uses per-event Google link; ICS is feed-level only).
6. Blog tag chips link to the archive, not tag archives.
7. Open PR `cg/poc/blog-implementation` → `main` when owner says so.
8. Owner decisions pending: commit `wp-content/plugins/`? delete stock WP content? sign the three unsigned commits (`0e984ef`, `c380e9b`, `5605063`)?

## Gotchas for a fresh agent

- Read theme `README.md` + this file before touching code; `03-DESIGN-SPEC.md` for anything visual (v2 warm/rounded system — ignore any neobrutalist remnants in older docs).
- `page.php` routes by slug → `page-{slug}.twig` (about, get-involved, calendar, styleguide). No per-page PHP templates.
- ACF field groups are **PHP-registered** (`inc/*.php`, keys prefixed `group_rgvdsa_<domain>_…`) — no acf-json, no DB sync; edit code, not the ACF UI.
- Owner communication style: extremely concise, sacrifice grammar; plans end with a terse "unresolved questions" list.
