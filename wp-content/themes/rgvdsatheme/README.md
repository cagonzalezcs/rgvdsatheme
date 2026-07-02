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
| `inc/interior.php` | governing-documents repeater, page lede override, and the grievance callout (toggle + wysiwyg) on pages |

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

## Design reference

The design handoff (specs, tokens, HTML prototypes) lives at the site root in `design_handoff_rgvdsa_vue/` — `03-DESIGN-SPEC.md` is the visual source of truth.
