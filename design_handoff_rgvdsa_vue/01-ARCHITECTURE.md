# 01 — Architecture & Technical Notes

Target repo: `rgvdsatheme` (WordPress theme, Timber 2 / Twig, Composer, Vite 7 via `@kucrut/vite-for-wp`, TypeScript, entry `src/ts/app.ts`, output `dist/`).

## Branching
Single dev, low risk. One long-lived feature branch, merged when the stack is proven:

```bash
git checkout -b feat/vue3-tailwind-shadcn
```

Commit at the end of every phase (see `02-PHASES.md`). If a phase goes sideways, `git checkout .` and re-run the phase brief in a fresh context — phases are written to be idempotent-ish. Tag `v0-pre-vue` on `main` before starting so there's an easy waypoint back.

## What already exists (don't rebuild)
- **Vite ↔ WP bridge**: `@kucrut/vite-for-wp` (npm) + `kucrut/vite-for-wp` (Composer, already in `vendor/`) handle dev-server/HMR and production enqueue. Keep the existing `v4wp()` plugin config; just add inputs.
- **`components.json`** at repo root is already configured for shadcn-vue (`new-york` style, TS, lucide, `@/` aliases, `tailwind.css: "src/css/tailwind.css"`, `tailwind.config: ""` — correct for Tailwind v4). Honor it.
- **Twig views** in `views/` (`base.twig`, `html-header.twig`, `footer.twig`, etc.) and `StarterSite.php` context.
- Legacy SCSS in `src/scss/` and TS in `src/ts/` (SiteHeader, home page scripts). Leave in place during migration; delete in the final cleanup phase.
- `@awesome.me/webawesome` is a dependency — audit for actual usage; expected outcome is removal once shadcn-vue lands.

## Rendering architecture: Vue islands on Timber (recommended)
Keep WordPress/Timber as the router + content source; render page shells in Twig; mount **Vue 3 islands** for anything interactive or componentized.

- Each island is a `createApp(Component, props).mount(el)` call. A tiny registry scans for `[data-vue-island]` elements and mounts the matching component:
  ```html
  {# in a twig template #}
  <div data-vue-island="EventCalendar" data-props='{{ events_json|e("html_attr") }}'></div>
  ```
- Props flow from Timber context → JSON attribute (or an inline `<script type="application/json">` block for big payloads).
- SEO-critical static content (hero copy, about text, footer links) can be plain Twig **or** server-rendered-looking Vue islands that receive their copy as props — decide per template; default to Twig for pure static, Vue for anything with state.
- This keeps the door open to "a variety of directions": the component library is framework-pure (no WP coupling), so a later pivot to headless (Nuxt / WP REST / SPA) reuses everything except the island registry.

**Alternative (not recommended now):** full SPA taking over `<body>` with WP as headless CMS. More moving parts (routing, data fetching, SEO) for a 4-page site. The islands approach converges to this later if wanted.

## Vue 3 setup
- Add: `vue`, `@vitejs/plugin-vue`, `vue-tsc` (replace `tsc` in the build/typecheck scripts with `vue-tsc` so `.vue` files typecheck).
- `vite.config.js`: add `vue()` to plugins; add a `resolve.alias` for `@` → `./src`.
- `tsconfig.json`: add `"paths": { "@/*": ["./src/*"] }` + `"baseUrl": "."`, include `.vue` via a `src/vite-env.d.ts` shim. Note existing flags (`verbatimModuleSyntax`, `erasableSyntaxOnly`, `noUnusedLocals`) — shadcn-vue generated code is generally compatible, but if `erasableSyntaxOnly` fights generated code, drop that flag; it's a nicety not a requirement.
- **Composition API + `<script setup lang="ts">` for all project components.** shadcn-vue generated components already follow this.
- New source layout:
  ```
  src/
    css/tailwind.css        ← Tailwind v4 entry + @theme tokens (path fixed by components.json)
    ts/app.ts               ← existing entry; imports css + island registry
    ts/islands.ts           ← island mount registry
    components/ui/          ← shadcn-vue generated library (project-owned)
    components/site/        ← app components (SiteHeader.vue, EventCalendar.vue, …)
    composables/            ← useA11ySettings.ts, etc.
    lib/utils.ts            ← cn() helper (created by shadcn-vue init)
  ```

## Tailwind v4 setup
- Add `tailwindcss` + `@tailwindcss/vite`; add the plugin to `vite.config.js`. **No `tailwind.config.js`** — v4 is CSS-first.
- `src/css/tailwind.css`:
  ```css
  @import "tailwindcss";
  @import "tw-animate-css";        /* added by shadcn-vue init */

  @theme {
    /* brand tokens — full list in 03-DESIGN-SPEC.md */
    --color-brand-red: #E9252E;     /* bright red — full-bleed bands */
    --color-red: #B01B22;           /* deeper red — text, links, controls, fills */
    --color-ink: #1C1917;
    --color-off-white: #F7F5F2;
    --font-display: "Montserrat", sans-serif;
    --font-sans: "Open Sans", system-ui, sans-serif;
    --radius: 0.875rem;             /* 14px cards/inputs; actions use pill 999px */
    --shadow-card: 0 2px 10px rgb(28 25 23 / 0.10);
    --shadow-pop: 0 14px 36px rgb(28 25 23 / 0.24);
  }
  ```
- Map shadcn's semantic CSS variables (`--background`, `--foreground`, `--primary`, `--border`, `--ring`, …) to brand tokens in `:root` so generated components come out on-brand by default (spec in `03-DESIGN-SPEC.md`).
- Tailwind's preflight will collide with legacy SCSS globals. During migration, scope is small enough to just let Tailwind win: import order = tailwind first, legacy `app.scss` second, and prune legacy rules as pages are rebuilt. Final phase deletes the SCSS tree.
- **Styling convention: inline Tailwind utilities in templates** (per project preference). Extract to a `cva()` variant or a component only when a pattern repeats; never build a parallel utility-class design-system layer.

## shadcn-vue
- Init: `npx shadcn-vue@latest init` (it will respect the existing `components.json`). Core deps it brings: `reka-ui`, `class-variance-authority`, `clsx`, `tailwind-merge`, `lucide-vue-next`, `tw-animate-css`.
- Components are **generated into the repo** (`src/components/ui/`) — this is the "own component library" property. After generation they're ours: re-theme freely, but keep the file/API structure so future `shadcn-vue add` diffs stay readable.
- Full-registry rollout is phased with per-batch heavy deps (`@tanstack/vue-table`, `@internationalized/date`, `embla-carousel-vue`, `vee-validate` + `zod`, `vue-sonner`, `@unovis/vue`, `vaul-vue`) — batches and acceptance criteria in `02-PHASES.md`.
- The v2 re-theme is done **once, centrally**, via the semantic CSS variables + a small set of targeted edits (radius, soft shadows, border colors) — not by forking every component. Verify per batch on the styleguide page.

## Styleguide page (build early, keep forever)
A WP page template (`page-styleguide.php` + `views/page-styleguide.twig`) mounting one big island that renders every `ui/` component in brand theme + every `site/` component. This is each phase-agent's acceptance surface and the permanent visual regression page. Noindex it.

## BEM — decision
Full BEM is redundant on top of Vue SFCs + Tailwind utilities (the component boundary already does what BEM blocks do). Adopt **"B without E-M"**:
- Every component's root element gets a single kebab-case block class: `class="site-header …utilities…"`, `event-calendar`, `a11y-widget`.
- These classes carry **no styles** — they are stable hooks for debugging, tests, analytics, and the rare WP-content override.
- Variants/modifiers are props + `cva()` variants, not `--modifier` classes. Child elements are just utilities.

## WordPress data model (Phase 6, but design for it early)
- **Events**: CPT `event` — public, with a **single template** (`single-event.twig` → `SingleEvent.vue`) in addition to the calendar list. Fields: title, `event_summary`, category taxonomy, featured image + alt; an `event_details` ACF group (`event_date`, `start_time`, `end_time`, `doors_time`, `location_type`, `location_name`, `location_address`, `online_url`, `cost`, `rsvp_required`, `rsvp_url`, `capacity`); and an `event_body` ACF flexible-content stack (prose · agenda · logistics · a11y note · map). The calendar chip needs only a subset; the single page needs the full group. Timber query → JSON → `EventCalendar` island props; the single template reads the full event context. Categories carry a color field (term meta) matching the calendar filter swatches — it also drives every accent on the single-event page. No author/byline fields on this CPT.
- **Documents** (Interior template): CPT or simply media + ACF repeater; low volume.
- **Menus**: WP menus mapped in `StarterSite.php` context → `SiteHeader`/`SiteFooter` props, so nav is editable in wp-admin.
- **Global settings** (join URL, Instagram, contact email): ACF options page or Customizer → context → props. The prototypes expose exactly these as tweakable props (`joinUrl`, `eventCount`, `showCountiesStrip`) — mirror that.

## Accessibility requirements (non-negotiable, from the design)
- Skip link, `aria-expanded` on all disclosure buttons, `aria-pressed` on toggles, `role="dialog"` + focus trap on modals, `aria-current="page"` on nav.
- The **A11y widget** (text size / high contrast / reduce motion) persists to `localStorage` key `rgv-dsa-a11y` and applies at document level — implement as `useA11ySettings()` composable + `A11yWidget.vue`; behavior spec in `03-DESIGN-SPEC.md`.
- Respect `prefers-reduced-motion` in addition to the manual toggle.
- Language toggle EN/ES is present-but-disabled in the design ("próximamente") — build the affordance, defer i18n.
