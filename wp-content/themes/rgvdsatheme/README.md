# RGV DSA Theme

WordPress theme for the Rio Grande Valley Democratic Socialists of America chapter site.

## Stack

- **WordPress + Timber 2** (Twig templates) — routing + content
- **Vue 3** (Composition API, `<script setup lang="ts">`) — interactive islands
- **Tailwind CSS v4** (CSS-first config in `src/css/tailwind.css`, no `tailwind.config.js`)
- **shadcn-vue** — project-owned component library generated into `src/components/ui/`
- **Vite 7** via `@kucrut/vite-for-wp` (npm) + `kucrut/vite-for-wp` (Composer) — dev server/HMR + production enqueue
- **ACF Pro** — field groups registered in PHP (see `inc/`), editable content in wp-admin

## Commands

```bash
npm run dev        # Vite dev server w/ HMR (reads .env; https://rgvdsa.test:8890)
npm run build      # vue-tsc typecheck + production build to dist/
npm run typecheck  # vue-tsc only
npm run lint       # eslint
composer test      # PHPUnit (WorDBless) smoke tests
```

## Architecture

### Vue islands on Timber

Twig renders page shells; Vue mounts on `[data-vue-island]` elements:

```twig
<div data-vue-island="EventCalendar" data-props='{{ props|json_encode|e("html_attr") }}'></div>
```

`src/ts/islands.ts` holds the registry (lazy `import()` per island — page JS stays small). Props are camelCase JSON. **There are no production fixture fallbacks** — PHP contexts always set their keys (possibly empty) and islands render designed empty states; the design fixtures live in `src/lib/fixtures/` for the styleguide only. Islands: `SiteHeader`, `SiteFooter`, `PageHeader`, `FaqAccordion`, `EventCalendar`, `BlogArchive`, `SinglePost`, `Styleguide`.

**Embedded vs fetched:** chrome islands, front-page sections, and `SinglePost` receive embedded props (first paint, SEO — `single.twig` also server-renders the article inside the mount element as the crawlable/no-JS fallback). `BlogArchive` embeds its first browse page and fetches every interaction (search/filter/page, debounced + abortable) from the REST layer; `EventCalendar` fetches its whole window on mount with a skeleton. Client code: `src/lib/api.ts`.

### WP data layer (`inc/`, one file per domain)

| File | Owns |
|---|---|
| `inc/events.php` | `event` CPT, `event_category` taxonomy + color term meta, event ACF fields, ICS feed (`/feed/rgvdsa-events/`), ChapterEvent serialization |
| `inc/blog.php` | category term colors, post settings (dek, byline mode, committee…), post_content block → BlogPost/SinglePostData serialization (`rgvdsa_blog_blocks_from_content`) |
| `inc/blocks.php` | the six `rgvdsa/*` ACF blocks (`blocks/*/block.json`), gallery block styles, restricted 14-block post inserter, attachment `credit` field |
| `inc/rest.php` | `rgvdsa/v1` read API (`/posts`, `/posts/{slug}`, `/events`, `/categories`), transient + ETag/304 caching |
| `inc/categories.php` | canonical category registry (`categories.json`), term-name/color merge, canonical-slug rename guard |
| `inc/cache.php` | `rgvdsa_cache_remember()` transient helper + content-version invalidation |
| `inc/options.php` | "Chapter Settings" ACF options page (committees, counties, contact email, newsletter URL…), the front-page Home hero group, menu locations, chrome props |
| `inc/interior.php` | governing-documents repeater, page lede + search-description overrides, and the grievance callout (toggle + wysiwyg) on pages |
| `inc/seo.php` | head SEO output: meta description, canonical, robots, Open Graph/Twitter cards, JSON-LD (`wp_head` priority 5) |

Template routers (`front-page.php`, `page.php`, `index.php`, `single.php`, …) expose filters (`rgvdsa/context/front_page`, `…/page`, `…/blog_archive`, `…/single`) the domain files hook to inject island props.

The calendar is driven by the **"Calendar" page template** (`page-templates/calendar.php`), not a magic `calendar` slug — assign it under Page Attributes → Template (the seeder does this). Renaming the page's slug/title won't break the events wiring.

Category slugs `chapter | poled | mutual | labor | electoral | social` are load-bearing (URLs + Vue types) — don't rename terms. Colors live on the terms (ACF color picker) and flow to the islands via props.

### Adding a shadcn-vue component

```bash
npx shadcn-vue@latest add <component>   # respects components.json; generates into src/components/ui/
```

Re-theme via the semantic CSS variables in `src/css/tailwind.css`, not per-component forks. Demo every component on the styleguide page (`/styleguide/`, mounted from `views/page-styleguide.twig`).

### Styling conventions

- Inline Tailwind utilities in templates; extract to `cva()` variants only when a pattern repeats.
- Every component root gets one kebab-case block class (`site-header`, `event-calendar`) — a style-free hook for debugging/tests.
- Accessibility: a11y widget settings persist to localStorage `rgv-dsa-a11y`; respect `prefers-reduced-motion`; keep ≥4.5:1 label contrast in hover states.

### REST API (`/wp-json/rgvdsa/v1`)

GET-only, public, publish-only; handlers reuse the domain serializers so REST shapes match the embedded contexts by construction. Additive changes stay on `/v1`; renames/removals go to `/v2`.

| Route | Returns |
|---|---|
| `/posts?page&per_page&category&s` | `{ posts: BlogPost[], page, perPage, total, totalPages }` |
| `/posts/{slug}` | `SinglePostData` + `readNext: BlogPost[]` (404 `rgvdsa_post_not_found`) |
| `/events?after&before` | `{ events: ChapterEvent[], categories }` (default −1 → +12 months) |
| `/categories` | `{ categories: EventCategory[] }` |

Anonymous responses carry `Cache-Control: public, max-age=300, stale-while-revalidate=3600` + ETag/304; logged-in requests are `no-store`. Payloads are transient-cached via `rgvdsa_cache_remember()`.

### Contract governance

`src/lib/schemas.ts` (zod) is the single contract definition — `posts.ts`/`events.ts` re-export `z.infer` types, and the API client validates responses (throws in dev, error state in prod). The PHP↔TS bridge is `tests/fixtures/*.json`, asserted from both sides: PHPUnit byte-equality (`tests/test-contracts.php`) and vitest zod parse (`src/lib/__tests__/contracts.spec.ts`). A contract change fails one side until both agree. Regenerate fixtures deliberately:

```bash
RGVDSA_WRITE_FIXTURES=1 vendor/bin/phpunit --filter TestContracts
```

### SEO (`inc/seo.php`)

Hand-rolled head output (no SEO plugin) hooked once at `wp_head` priority 5; every copy/image source is the same first-party data the islands use. Emitted on every page: `<meta name="description">`, `rel=canonical`, OG set (`og:site_name/type/title/description/url/image` + `width/height/alt` when known), `twitter:card`, and one JSON-LD `@graph` script.

**Description ladder** (plain-text, ~155 chars, word-boundary trim): post → dek → excerpt; page → `seo_description` (interior group) → lede → tagline; posts page uses the page ladder on the `page_for_posts` page; event → post content; front page → hero lede. Empty tagline bottoms out at the hero-lede design copy — never empty.

**Canonical + robots:** singular pages get their permalink (core's `rel_canonical` is removed — this file owns the tag); island filter params (`?s=` / `?category=` / `?paged=`) canonicalize to the clean posts-page URL while server-paged `/page/N/` keeps its own. `noindex,follow` (merged into core's `wp_robots` meta) on search, filtered archive states, date/author archives, and 404.

**Share image ladder:** featured image (`large`) → Chapter Settings **Default share image** (seeded as the theme logo) → `static/images/logos/logo-lg.png`. A per-content image cards as `summary_large_image`; fallbacks card as `summary`.

**JSON-LD:** `Organization` site-wide (name/url/logo/`sameAs` from the socials options); `Article` on posts (author is a Person, or the committee as an Organization per byline mode); `Event` on event permalinks (chapter-tz ISO-8601 start/end, `Place` from venue/city, `offers` → RSVP URL — same fields as the ICS feed).

## Testing

```bash
composer test   # PHPUnit via WorDBless (no DB/WP install needed)
npm test        # vitest — category-token drift + contract fixtures
```

PHPUnit runs on [WorDBless](https://github.com/Automattic/wordbless): the first run creates a `wordpress/` directory in the theme (the WorDBless WP install + a symlink back to the theme). It is a test artifact — **untracked and expected**, not part of the theme. ACF Pro is absent under WorDBless, so `tests/bootstrap.php` polyfills `get_field()` (post meta / options / term meta backed).

## Seeding demo content

Idempotent seed (categories + colors, 14 events, lorem posts covering every block type, menus, options, interior documents):

```bash
wp eval-file wp-content/themes/rgvdsatheme/bin/seed.php
```

Local MAMP invocation (socket + noisy-PHP workaround):

```bash
php -d error_reporting=0 -d display_errors=0 \
  -d mysqli.default_socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  /Applications/MAMP/Library/bin/wp --path=/path/to/site \
  eval-file wp-content/themes/rgvdsatheme/bin/seed.php
```

## Translations (EN/ES)

Home-only machine translation via the GTranslate plugin (free tier — client-side Google Translate, no `/es/` URLs). The header EN/ES toggle drives it.

**Gate:** `inc/translation.php` — active when the Chapter Settings **Spanish site enabled** option is on AND `is_front_page()`. To extend translation to inner pages, hook `rgvdsa/translation/active` (or lift the `is_front_page()` predicate). Pages where the gate is off ship zero gtranslate assets — a stale cookie is inert.

**Flow:** `base.twig` renders a hidden `[gt-link]` shortcode on active pages → plugin enqueues its `base.js` (defines `window.doGTranslate`, hides Google's UI). `src/ts/translation.ts` bridges the toggle: ES loads Google's `element.js` and fires `doGTranslate('en|es')` in place; EN expires the `googtrans` cookie variants and reloads (avoids `<font>` artifacts).

**Cookie contract:** `rgvdsa_lang` (theme, authoritative) · `googtrans` (Google, derived). While the ES preference is set, the client-nav layer (`src/ts/navigation.ts`) stands down — full page loads only.

**notranslate policy:** identifiers only (county names, `@dsa_rgv`, emails, "RGV DSA" tokens) plus the LanguageToggle (EN/ES are codes). Header and footer islands translate. Content-island mounts must stay translatable — future inner-page ES should come from data/props, not DOM machine translation.

**Pinned plugin config** (`GTranslate` option, seeded by `bin/seed.php`): `default_language: en`, languages `en,es` only, `detect_browser_language` OFF (it fights the toggle), no widget placement.

**Gotchas:** never pre-set `<html lang>` from the preference — Google then treats the page as already Spanish and silently skips translation (Google owns `<html lang>` while translating). Rapid automated flip/reload cycles trip Google's rate limiting (`element.js` → 503 + `/sorry/` interstitial); it clears on its own.

## Design reference

The design handoff (specs, tokens, HTML prototypes) lives at the site root in `design_handoff_rgvdsa_vue/` — `03-DESIGN-SPEC.md` is the visual source of truth.
