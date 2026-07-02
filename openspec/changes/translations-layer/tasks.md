# Tasks: translations-layer

Theme paths relative to `wp-content/themes/rgvdsatheme/`. Sections ordered by dependency; each ends with a Verify task. Contract source: `wp-content/plugins/gtranslate/js/base.js` (doGTranslate, googtrans cookie, `gt_translate_script` guard).

## 1. Plugin config + PHP gate

- [x] 1.1 Pin GTranslate option (`GTranslate` in wp_options): `default_language` en, languages en+es only, `detect_browser_language` off, no widget/block/menu placement; add optional seeding in `bin/seed.php` and document values
- [x] 1.2 New `inc/translation.php`: `rgvdsa_translation_active()` (`!is_admin() && es_enabled && is_front_page()`) wrapped in `apply_filters('rgvdsa/translation/active', …)`; `timber/context` filter adding `translation.active`; comment marking `is_front_page()` as the liftable home-only gate
- [x] 1.3 `require_once` from `functions.php` alongside existing inc requires
- [ ] 1.4 Verify: with `es_enabled` on, front-page view-source shows gtranslate `base.js` + `gtranslateSettings` blob (after 2.1); a page/post/archive shows neither; `es_enabled` off removes both

## 2. Twig bootstrap + chrome safety

- [x] 2.1 `views/base.twig`: when `translation.active`, emit `<div class="notranslate" hidden aria-hidden="true">{{ function('do_shortcode', '[gt-link lang="es" label="Español"]') }}</div>` before `wp_footer()`
- [x] 2.2 `views/base.twig`: `data-translation-scope="{{ translation.active ? 'page' : 'none' }}"` on `<body>`; `header_props.esEnabled: translation.active` (replacing raw `chapter.es_enabled`); `notranslate` on SiteHeader + SiteFooter island mount wrappers
- [x] 2.3 `src/components/site/SiteHeader.vue`: `notranslate` class on portaled `DropdownMenuContent`; audit for other portaled chrome surfaces (only the About dropdown portals)
- [ ] 2.4 Verify: after ES flip on home, hidden `#google_translate_element2` exists, no visible gtranslate UI, header/footer/dropdown text untouched

## 3. translation.ts bridge + toggle

- [x] 3.1 New `src/ts/translation.ts`: `pageTranslatable()` (body dataset), `isSpanishPreferred()` (reuse exported `rgvdsa_lang` reader), `ensureTranslateLib()` (element.js injection honoring `window.gt_translate_script` guard), `activateSpanish()` (in-place doGTranslate w/ cookie+reload fallback), `restoreEnglish()` (expire all googtrans variants + reload), `initTranslation()` (startup reconcile: ES preferred + translatable + no googtrans → activate)
- [x] 3.2 `src/composables/useLanguagePreference.ts`: export cookie reader for the bridge; refresh stale "site is not translated yet" comments
- [x] 3.3 `src/ts/app.ts`: call `initTranslation()` after `mountIslands()` / `initNavigation()`
- [x] 3.4 `src/components/site/LanguageToggle.vue`: `onToggle` additionally calls `activateSpanish()`/`restoreEnglish()` when `esEnabled`; tooltip copy → "Español — disponible en la página de inicio"; refresh header comments
- [ ] 3.5 Verify: ES flip on home translates in place (no reload); hard-reload stays ES; EN flip reloads clean English with `googtrans` gone and `rgvdsa_lang=en`; toggle on inner page records preference only

## 4. Navigation standdown

- [ ] 4.1 `src/ts/navigation.ts`: early-return in `onClick` (and prefetch-intent path) when `isSpanishPreferred()`
- [ ] 4.2 `src/ts/navigation.ts` `syncHead()`: sync `data-translation-scope` alongside the existing `data-template` sync
- [ ] 4.3 Verify: with ES active on home, clicking Blog does a full load and Blog renders EN; returning home auto-resumes ES; EN user still gets partial swaps and can flip ES after SPA-navigating onto home

## 5. notranslate template pass (all templates)

- [ ] 5.1 `views/front-page.twig`: county names in counties strip, `@dsa_rgv`, contact email, "dsausa.org", "RGV DSA"/"DSA" brand tokens
- [ ] 5.2 `views/single.twig` + `views/single-event.twig` server-rendered fallbacks: venue names, addresses, emails/RSVP URLs rendered as text
- [ ] 5.3 Sweep remaining views (`page*.twig`, `index.twig`, `archive.twig`, `search.twig`, `author.twig`, `404.twig`, `page-calendar.twig`): emails, handles, brand tokens; do NOT mark content-island mounts
- [ ] 5.4 Verify: ES home leaves county names, `@dsa_rgv`, emails, "RGV DSA" intact; markup-only spot-check of inner templates

## 6. Wrap-up

- [ ] 6.1 Vitest coverage: translation.ts cookie/gate/fallback logic, LanguageToggle wiring (esEnabled on/off paths)
- [ ] 6.2 `npm run typecheck`, `npm test`, `composer test` green
- [ ] 6.3 README "Translations" section: cookie contract, plugin settings, gate-lift instructions for the in-company team (`rgvdsa/translation/active` filter, island data strategy per design D4)
