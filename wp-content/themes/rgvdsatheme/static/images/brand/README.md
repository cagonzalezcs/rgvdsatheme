# Brand artwork (v3 brand refresh)

Semantic copies of the designer's exports in
`design_handoff_rgvdsa_vue/design-assets/SVG/` (canonical). Mapping recorded
during `home-v3-brand-refresh` task 1.1 — Illustrator numbered the exports, so
match by viewBox/content, not by number:

| File | Source | Notes |
|---|---|---|
| `logo-header.svg` | `Asset 16.svg` (213×89) | Transparent lockup; brand-red bg set in CSS (SiteHeader.vue) |
| `logo-footer.svg` | `Asset 12.svg` (231×96) | Transparent lockup; ink-footer bg in CSS (SiteFooter.vue) |
| `icon-twitter.svg` / `icon-instagram.svg` / `icon-facebook.svg` | `Asset 13/14/15.svg` | Fill → `currentColor`; inlined in SiteFooter.vue (files kept for reference) |
| `sparkle.svg` | `Asset 2.svg` (42×46) | Hero star art (prototype `sparkle.png`) |
| `star-notch.svg` | `Asset 3.svg` (58×74) | Hero star art (`star-notch.png`) |
| `star.svg` | `Asset 4.svg` (62×71) | Hero star art (`star.png`) |
| `county-map.svg` | `Asset 5.svg` (639×326) | `<style>` classes → presentation attrs. County labels are live `<text>` in Bowlby One, so the map is **inlined** via `views/partials/county-map.twig` (page `@font-face` applies); this file is the source of that partial |
| `flames-full.svg` | `Asset 8.svg` (1566×1026) | Pure vector; designer fill `#f75414` (= `--color-flame`). Reference only — the band uses the tile |
| `flames-tile.svg` | derived from `flames-full.svg` | Original + horizontal mirror (3133×1026) so `repeat-x` is seamless. Painted as a CSS **mask** by `.ponte-trucha::after` (src/css/tailwind.css) so the color is the `--color-flame` token and swappable in high contrast |
| `luchador-panel.svg` | `Asset 11.svg` (1281×563) | Vector panel (`#668043` = `--color-green-panel`) + stars; the luchador is a designer raster (1252×1125, ~2x) embedded in the export — re-encoded PNG→WebP q85 (1.3 MB → 512 KB), texture intact |
| `hero-photo*.jpg/.webp` | `Asset 7.svg` (embedded 2642×1988 raster) | Duotone photo; exported at 951w (1x) + 1902w (2x) as JPEG q82 / WebP q80. Served via `<picture>` |
| `hero-headline.svg` | designer vector export (613×188) | The page `<h1>` (white + layered green 3-D offset). Unoptimized export (~425 KB) — run through svgo if it ever matters |

Not shipped: `social-icons.png` (prototype placeholder strip), `luchador.png`,
`flames.png`, `logo-*.png`, `hero-headline.png` (prototype rasters), Jost /
Myriad Pro fonts.

Fonts: `static/fonts/{bowlby-one,manifold-dsa,special-season}/*.woff2`
converted from the handoff TTF/OTF with fontTools (`font.flavor = "woff2"`).
Manifold DSA Heavy reports `usWeightClass` 900 → declared `font-weight: 800 900`.
