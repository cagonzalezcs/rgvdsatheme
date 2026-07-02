# 02 — Phased Build Plan (for Claude Code agents)

Each phase below is a **self-contained brief**: paste it (plus `README.md`, `01-ARCHITECTURE.md`, `03-DESIGN-SPEC.md`, and the `designs/` folder) into a fresh Claude Code context, or dispatch phases 2A–2C to parallel sub-agents. Every phase ends with: build passes (`npm run build`), typecheck passes, styleguide page renders clean, **one commit** with the stated message.

Orchestrator note: Phases 0 → 1 → 2A are strictly sequential. 2B and 2C can run as parallel sub-agents after 2A. Phases 3–6 are sequential, except 5 (Calendar) and 5B (Blog) which can run as parallel sub-agents after Phase 4. Phase 7 is cleanup.

---

## Phase 0 — Branch & toolchain
**Agent brief:** Prepare the `rgvdsatheme` repo for Vue 3 + Tailwind v4 development. Do not build any UI.

1. `git tag v0-pre-vue && git checkout -b feat/vue3-tailwind-shadcn`
2. Install: `vue`, `@vitejs/plugin-vue`, `vue-tsc`, `tailwindcss`, `@tailwindcss/vite`.
3. `vite.config.js`: add `vue()` and `tailwindcss()` plugins; `resolve.alias` `@` → `./src`. Keep `v4wp()` and `liveReload` exactly as configured.
4. `tsconfig.json`: `baseUrl` + `paths` for `@/*`; add `src/vite-env.d.ts` with the `.vue` module shim; switch `build`/`typecheck` scripts to `vue-tsc`.
5. Create `src/css/tailwind.css` (`@import "tailwindcss";` + placeholder `@theme` block) and import it at the top of `src/ts/app.ts`, **before** the legacy `app.scss` import.
6. Smoke test: a throwaway `HelloIsland.vue` mounted from `app.ts` onto a div temporarily added to `views/base.twig`; confirm HMR in `npm run dev` and a clean `npm run build`. Remove the throwaway before committing.

**Accept:** dev + build + typecheck green; site renders unchanged.
**Commit:** `chore: vue3 + tailwind v4 toolchain on vite-for-wp`

---

## Phase 1 — shadcn-vue init + island infrastructure + styleguide
**Agent brief:** Initialize shadcn-vue and the app scaffolding every later phase depends on.

1. `npx shadcn-vue@latest init` — must consume the existing `components.json` unchanged (new-york, lucide, `@/` aliases, css at `src/css/tailwind.css`). Verify it created `src/lib/utils.ts` (`cn()`).
2. Add the brand `@theme` tokens and the shadcn semantic-variable mapping to `src/css/tailwind.css` — exact values in `03-DESIGN-SPEC.md` § Tokens. This is the **brutalist re-theme**: `--radius: 0px`, hard offset shadows, 2–3px ink borders.
3. Add `shadcn-vue add button badge card separator label input` as the seed batch; verify they render on-brand.
4. Build the island registry (`src/ts/islands.ts`): scans `[data-vue-island]`, parses `data-props` JSON, mounts the registered component. Register islands lazily (dynamic `import()`) so page JS stays small.
5. Styleguide: `page-styleguide.php` + `views/page-styleguide.twig` + a `Styleguide.vue` island showing the token palette, type scale, and every installed ui component in all variants. Noindex.
6. Self-host fonts: move Montserrat + Open Sans from `designs/assets/fonts/` into `static/fonts/` (convert to woff2 if trivial), `@font-face` in `tailwind.css`, weights per `03-DESIGN-SPEC.md`.

**Accept:** styleguide page shows seed components with zero radius, hard shadows, brand palette, correct fonts.
**Commit:** `feat: shadcn-vue init, brand theme, island registry, styleguide`

---

## Phase 2 — Full shadcn-vue registry rollout (3 sub-agent batches)
Goal: every shadcn-vue registry component generated into `src/components/ui/`, on-brand, demoed on the styleguide. Each sub-agent: run `shadcn-vue add <components>`, install that batch's peer deps, fix any TS/lint fallout, add a styleguide section per component, verify the brutalist theme holds (radius 0, ink borders, hard shadows — fix via semantic vars first, targeted component edits second).

### 2A — Static & form primitives (sequential, before 2B/2C)
`alert aspect-ratio avatar badge breadcrumb button card checkbox input kbd label pagination progress radio-group separator skeleton slider switch table textarea toggle toggle-group`
**Commit:** `feat(ui): shadcn-vue batch A — primitives`

### 2B — Overlay & navigation (reka-ui heavy)
`accordion alert-dialog collapsible context-menu dialog drawer dropdown-menu hover-card menubar navigation-menu popover scroll-area sheet sidebar tabs tooltip`
Peer deps: `vaul-vue` (drawer).
Pay attention: dialog/popover/dropdown shadows must be the hard `6px 6px 0 ink` style; overlay animations must respect reduced motion.
**Commit:** `feat(ui): shadcn-vue batch B — overlays & nav`

### 2C — Data & complex (heavy deps)
`calendar carousel combobox command data-table date-picker form input-otp number-field pin-input range-calendar resizable sonner stepper tags-input` (+ `chart` if present in current registry)
Peer deps: `@internationalized/date`, `@tanstack/vue-table`, `embla-carousel-vue`, `vee-validate` + `@vee-validate/zod` + `zod`, `vue-sonner` (+ `@unovis/vue` for charts).
Note: shadcn `calendar` is a **date-picker**, not the events calendar — the events calendar is a custom component in Phase 5.
**Commit:** `feat(ui): shadcn-vue batch C — data & complex`

**Phase 2 exit check (orchestrator):** styleguide renders every component; `npm run build` bundle size reviewed; no component still showing default-shadcn rounded/soft styling.

---

## Phase 3 — Site chrome
**Agent brief:** Build the shared shell components from the prototypes (all six `designs/*.dc.html` share header/footer — `RGV DSA Home.dc.html` is canonical). Spec: `03-DESIGN-SPEC.md` § Chrome.

Components (`src/components/site/`, Composition API, inline Tailwind, block class on root):
- `SkipLink.vue`
- `SiteHeader.vue` — sticky red bar; logo; About dropdown (DropdownMenu); Calendar / Blog / Get Involved links; EN/ES toggle (ES disabled, `title="Español — próximamente"`); A11yWidget; Join DSA CTA. Add a mobile disclosure menu (Sheet) — the prototype only wraps; breakpoint behavior is yours to design within the visual language.
- `A11yWidget.vue` + `useA11ySettings.ts` composable — Popover panel: text-size segmented control (A / A+ / A++ → root font 16/18/20px), High-contrast toggle, Reduce-motion toggle; persists to localStorage `rgv-dsa-a11y`; applies via `data-tone` overrides + global style injection exactly as specced.
- `SiteFooter.vue` — logo, 4 nav columns (props-driven from WP menus later; hardcode prototype content now), ink bottom bar.
- `PageHeader.vue` — red band: Breadcrumb + uppercase H1 + lede (props: `title`, `lede`, `crumbs`).
- Twig integration: replace legacy header/footer markup in `views/` with header/footer islands (or one whole-page island per template — pick one strategy and note it in the commit).

**Accept:** chrome pixel-matches prototypes at 1280w; a11y widget settings persist across reload; keyboard nav works (dropdown, popover, skip link).
**Commit:** `feat(site): header, footer, a11y widget, page header`

---

## Phase 4 — Pages: Home, Get Involved, Interior template
**Agent brief:** Implement three page templates against the prototypes. Spec: `03-DESIGN-SPEC.md` § Pages. Hardcode prototype content as component defaults/props; WP data wiring is Phase 6.

- **Home** (`views/front-page.twig` + islands): Hero, CountiesStrip, WhoWeAre (photo is an `<img>` slot with the striped placeholder as fallback), UpcomingEvents list (`EventListItem.vue`), FromTheBlog (1 featured `PostCard.vue` + 2 compact `PostRow.vue`), GetInvolvedSteps (3 numbered `StepCard.vue`), social strip.
- **Get Involved** (`views/page-get-involved.twig`): PageHeader; content+sidebar grid (`minmax(300px,1fr) 300px`, gap 56px); How to Join; Committees; Channels; FAQ (Accordion). Reproduce sidebar cards from the prototype.
- **Interior Page Template** (`views/page.twig` — the default): PageHeader; content+sidebar; documents list; prose styles for WP content (`h2` w/ bottom border, links, lists — match prototype); grievance `CalloutCard.vue` (white, ink border, red hard shadow).

**Accept:** side-by-side match vs prototypes at 1280w and 375w; all in-page anchors (`#committees`, `#faq`, `#documents`, `#grievance`) work; lighthouse a11y ≥ 95.
**Commit:** `feat(pages): home, get involved, interior template`

---

## Phase 5 — Events Calendar
**Agent brief:** Build the calendar page — the most stateful surface. Spec: `03-DESIGN-SPEC.md` § Calendar. Reference `designs/Calendar.dc.html` including its script block.

`EventCalendar.vue` island composed of:
- Toolbar: month prev/next + current month label; Month/List view toggle (ToggleGroup); category filter chips with color swatches (aria-pressed).
- `MonthGrid.vue` — 7-col CSS grid on ink gap lines; day cells cream (adjacent-month cells muted); event chips (category-colored, truncated, clickable).
- `EventListView.vue` — stacked rows: date block + title/when/where + hover hard-shadow lift; empty state (dashed border).
- `EventDetailDialog.vue` — Dialog: category tag, title, datetime, venue, description, RSVP CTA.
- Subscribe strip (ink band, ICS/Google links — hrefs stubbed).
- State: current month, view mode, active category, selected event. View + filter survive reload (URL params preferred over localStorage).
- Data: typed `ChapterEvent[]` prop; ship with the prototype's sample events as fixture.

**Accept:** month/list toggle, filtering, modal (focus-trapped, Esc closes), keyboard operable; matches prototype.
**Commit:** `feat(calendar): events calendar island`

---

## Phase 5B — Blog (archive + single)
**Agent brief:** Implement the blog archive and single-post templates. Spec: `03-DESIGN-SPEC.md` § Blog. Reference `designs/Blog.dc.html` and `designs/Blog Post.dc.html` — both have an in-prototype **"</> ACF spec" toggle** (bottom-left button) that overlays the intended field mapping per section; turn it on while porting. All blog copy in the prototypes is lorem ipsum placeholder — keep it as fixture text, don't write editorial content.

- **`BlogArchive.vue`** island (replaces `index.twig` / `category.twig` / `search.twig` rendering): PageHeader; search input + category filter chips (post categories share the 6 event colors); **browse state** = featured post card (sticky post ?? latest) + editorial grid (6-col; card spans by index `[3,3,2,2,2,2,2,2]`; excerpt only on span-3 cards); **filter/search state** = uniform result rows + result-count line + "Clear filters"; empty state (dashed border); pagination; email-subscribe strip (ink band, form action stubbed until Phase 6).
- State: `query` + `activeCat` sync to URL (`?s=`, `?category=`); any active filter/search switches browse → results layout.
- **`SinglePost.vue`** island (`single.twig`): red hero (breadcrumb, category tag, uppercase H1, dek, byline, "Léelo en español" stub link); featured image pulled up over the red band (negative margin); article body renders the **post_blocks** flexible-content stack — one Vue component per block: prose, image (w/ caption + credit), pull_quote, gallery (essay/grid), person_quote (photo + bilingual quote), video (oEmbed + CC badge + transcript link), audio (player row + transcript link), document (reuses Interior's DocumentRow), event_embed (reuses Calendar's list-row), action_callout (ink card, red hard shadow, button repeater). Block accents inherit the post category's term color.
- End matter: tags + copy-link/email share; author card (named vs committee byline mode); Read Next = latest 3 same-category posts (no ACF field); optional sticky meta rail (`showMetaRail` prop, off by default).
- Data: typed `BlogPost` fixture from the prototype's `POSTS` array; ACF field group definitions can be stubbed as JSON now and registered in Phase 6.

**Accept:** archive browse/filter/search/empty states match prototype; single post renders every block type; byline mode switch works; keyboard + focus-visible pass; matches prototypes at 1280w and 375w.
**Commit:** `feat(blog): archive + single post islands`

---

## Phase 6 — WordPress data wiring
**Agent brief:** Replace hardcoded content with WP data. Spec: `01-ARCHITECTURE.md` § WP data model.

- CPT `event` + `event_category` taxonomy (color term meta); Timber query → props for UpcomingEvents + EventCalendar.
- Blog: standard posts + `category` taxonomy sharing the same color term meta; register the `post_blocks` ACF flexible content group (block layouts per § Blog spec) + `dek` text field + `byline_mode` select; featured post = sticky ?? latest; archive search/filter params (`?s=`, `?category=`) wired to WP_Query; Home FromTheBlog = latest 3 posts.
- WP menus → header/footer nav props via `StarterSite.php` context.
- Options (join URL, email, Instagram, EN/ES flag) → ACF options page or Customizer → context.
- Interior pages: WP editor content flows into the prose area; documents list from a repeater or media query.
- Seed realistic demo content (the prototype's events/committees, a handful of lorem posts covering every block type) via a WP-CLI seed script or manual fixture instructions in the PR description.

**Accept:** all six screens fully driven by WP data; creating an event in wp-admin shows it on Home + Calendar; publishing a post shows it on Home + Blog archive and renders its blocks on the single template.
**Commit:** `feat(wp): event CPT, menus, options wiring`

---

## Phase 7 — Cleanup & hardening
**Agent brief:**
- Delete legacy `src/scss/` and `src/ts/components|pages` once nothing imports them; remove `@awesome.me/webawesome` if unused; prune dead Twig partials.
- Audit bundle: islands lazy-loaded, fonts preloaded, images sized.
- Full a11y pass (axe + keyboard walk of every page, both a11y-widget modes).
- Cross-browser: Chrome / Firefox / Safari / iOS Safari.
- Update `README.md` in the theme: stack, commands, how to add a shadcn component, island pattern.

**Accept:** clean lint/typecheck/build; no legacy CSS shipped.
**Commit:** `chore: remove legacy scss/ts, docs, a11y + perf pass` → open PR to `main`.
