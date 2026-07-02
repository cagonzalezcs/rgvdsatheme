# Tasks: seo-meta-layer

All theme paths relative to `wp-content/themes/rgvdsatheme/`. Additive head output; depends on existing serializers/options.

## 1. Meta + canonical + robots

- [ ] 1.1 New `inc/seo.php` (required from `functions.php`): `wp_head` priority-5 hook; `rgvdsa_seo_description()` ladder (post dek → excerpt; page seo_description → lede → tagline; event content; front hero lede), kses_plain + ~155-char word-boundary trim
- [ ] 1.2 `rel=canonical` (permalink / `get_pagenum_link`); filtered archive params (`?s=`/`?category=`/`?paged=`) canonicalize to the clean posts-page URL
- [ ] 1.3 `noindex,follow` on search, filtered archive states, date/author archives, 404
- [ ] 1.4 Interior group: optional `seo_description` field (`inc/interior.php`)
- [ ] 1.5 Verify: view-source description/canonical on post, page, `/blog/?s=x`, front page

## 2. Social cards

- [ ] 2.1 OG set (`site_name/type/title/description/url/image` + width/height/alt when known) + `twitter:card` (summary vs summary_large_image by image presence)
- [ ] 2.2 Chapter Settings `default_share_image` image field (`inc/options.php`); image ladder featured → surface → default → `logo-lg.png`
- [ ] 2.3 `bin/seed.php`: seed a default share image (placeholder attachment)
- [ ] 2.4 Verify: Meta Sharing Debugger + a messenger paste against seeded post/event/home

## 3. Structured data

- [ ] 3.1 `Organization` JSON-LD site-wide (name/url/logo/sameAs from options)
- [ ] 3.2 `Article` JSON-LD on posts (byline mode → Person vs committee Organization; dates; image)
- [ ] 3.3 `Event` JSON-LD on event permalinks (ISO-8601 chapter-tz start/end, Place from venue/city, offers → rsvp_url)
- [ ] 3.4 Verify: Google Rich Results test passes for a post and an event

## 4. Wrap-up

- [ ] 4.1 PHPUnit `tests/test-seo.php`: description ladder, canonical, noindex flags, OG completeness, JSON-LD decodes with right `@type` per surface
- [ ] 4.2 Full pass: `composer test`, `npm run typecheck`, `npm test`, reseed, spot-check head output on every template
- [ ] 4.3 README: SEO section (ladder, share-image field, what's emitted where)
