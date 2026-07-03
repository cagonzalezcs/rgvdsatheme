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
| `inc/options.php` | "Chapter Settings" ACF options page (committees, counties, contact email, newsletter URL, footer tagline, "New here?" card…), the front-page Home hero + Home sections groups ("Who we are", "Get involved" steps), menu locations, chrome props |
| `inc/interior.php` | governing-documents repeater, page lede + search-description overrides, and the grievance callout (toggle + wysiwyg) on pages |
| `inc/pages.php` | About + Get Involved page ACF groups (mission band, timeline, county cards, governance docs, FAQ repeaters, join steps, channels, sidebar cards) + their Twig contexts, defaulted in PHP to the design copy |
| `inc/seo.php` | head SEO output: meta description, canonical, robots, Open Graph/Twitter cards, JSON-LD (`wp_head` priority 5) |

Template routers (`front-page.php`, `page.php`, `index.php`, `single.php`, …) expose filters (`rgvdsa/context/front_page`, `…/page`, `…/blog_archive`, `…/single`) the domain files hook to inject island props.

The calendar is driven by the **"Calendar" page template** (`page-templates/calendar.php`), not a magic `calendar` slug — assign it under Page Attributes → Template (the seeder does this). Renaming the page's slug/title won't break the events wiring. **About** and **Get Involved** work the same way (`page-templates/about.php`, `page-templates/get-involved.php`): the template locates the page's ACF group and pins the view.

**Editable content contract:** every content area in the templates is editable in wp-admin — ACF groups registered in PHP (`inc/`, never DB-only; `acf-json/` catches any group edited via the UI). Every field falls back in PHP to the design copy, so an empty field renders exactly the prototype. Page headers take the WP page title + the Interior "Lede" field. The header About▾ dropdown is the `about` menu location (Vue fixture fallback); the footer tagline and the shared "New here?" sidebar card live in Chapter Settings.

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

Real translated content via **Polylang Pro** — English at `/`, Spanish at `/es/…`. No machine translation: each language is its own content, and the header EN/ES toggle is a plain language switcher (an `<a>` to the current page's translation).

**Setup (Polylang, one-time):** languages EN (`en_US`, default) + ES (`es`, `es_MX`); URL modifications = language in the directory with the default language hidden; pretty permalinks on. The `event` CPT is made translatable in code (`pll_get_post_types` filter in `inc/i18n.php`); `page`/`post` are translatable by default.

**Theme layer:** `inc/i18n.php` — builds the `languages` switcher context (each language's translation URL via `pll_the_languages`), exposes `pll__`/`pll_e` as Twig functions, registers the theme's static UI strings, and passes Polylang-translated header nav labels + `joinLabel`/`aboutLabel` to the `SiteHeader` island. Front-page BODY copy (hero, who-we-are, get-involved) is **not** string-translated — it comes from the Spanish page's own ACF fields. `inc/options.php` reads that ACF from the **current** front page (`get_queried_object_id()`), so `/es/` serves the Spanish page's fields.

**Language-filtered teasers:** `rgvdsa_events_query()` passes the current language (`get_posts` would otherwise bypass Polylang via `suppress_filters`); the blog query is a `WP_Query` Polylang filters automatically.

**Seeding (`bin/seed.php`):** backfills `en` on untagged posts, creates the Spanish front page (#linked to the EN home) with Spanish ACF copy, seeds `es` string translations (via `PLL_MO`), and creates Spanish translations of the upcoming events (home teasers).

**Gotchas / known items:**
- After creating front-page translations programmatically, run `PLL()->model->clean_languages_cache()` + `flush_rewrite_rules()` — Polylang caches each language's `page_on_front`, and a stale cache makes `/es/` fall through to the blog index. The seed handles this via its final rewrite flush.
- The Spanish home currently resolves at `/es/inicio/` (Polylang 301s bare `/es/` there). It renders correctly and the toggle round-trips; making `/es/` the canonical front-page URL is a pending refinement (Polylang static-front-page canonical).
- Event teaser **dates** render in English (`DateTimeImmutable::format` isn't locale-aware); switch to `wp_date()`/`date_i18n` to localize — deferred.
- The blog demo posts are lorem-ipsum, so the Spanish home shows the translated "Posts coming soon" empty state rather than seeded ES posts.

## Design reference

The design handoff (specs, tokens, HTML prototypes) lives at the site root in `design_handoff_rgvdsa_vue/` — `03-DESIGN-SPEC.md` is the visual source of truth.
