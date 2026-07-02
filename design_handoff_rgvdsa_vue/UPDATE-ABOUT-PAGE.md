# UPDATE — About page added (2026-07-02)

**How to use this file:** paste its full contents into your active Claude Code session in the `rgvdsatheme` repo. If you are starting the build fresh instead, ignore this file — `README.md`, `02-PHASES.md`, and `03-DESIGN-SPEC.md` already incorporate everything below.

---

A seventh screen has been added to the design package: **About** (`designs/About.dc.html`). Open it in a browser to see the working prototype. It changes one thing you may have already built (the header About dropdown) and adds one page to Phase 4.

## 1. SiteHeader change (Phase 3 — retrofit if already built)

The About ▾ dropdown is no longer a grab-bag of links into other pages. It now has **six items that deep-link to anchored sections on the About page**:

| Item | Anchor |
|---|---|
| About the Chapter | `/about#chapter` |
| Mission & History | `/about#mission` |
| Counties We Serve | `/about#counties` |
| Committees | `/about#committees` |
| Bylaws & Code of Conduct | `/about#bylaws` |
| FAQ | `/about#faq` |

Panel styling is unchanged (cream, 3px ink border, `6px 6px 0` ink shadow, items hover `#9E0B13`/white). `About.dc.html` is now canonical for this menu. The footer "About" column deep-links to the same anchors.

## 2. New page: About (Phase 4)

Template suggestion: `views/page-about.twig` + island(s), same pattern as the other pages. Full measurements in `03-DESIGN-SPEC.md` § About; the prototype is the source of truth. Structure top to bottom:

1. **PageHeader** (red): breadcrumb Home / About; H1 "About RGV DSA"; lede.
2. **Mission band** (ink, full-width, padding 72/24): red eyebrow "WHAT WE BELIEVE" + Montserrat 800 statement, `clamp(1.4rem,3vw,2.2rem)`, max 32ch.
3. **Content + sidebar grid** (`minmax(300px,1fr) 300px`, gap 56px) with six anchored sections mirroring the dropdown 1:1. All h2 anchors need `scroll-margin-top:100px` for the sticky header.
   - `#chapter` About the Chapter — two paragraphs, full-width photo slot (340px, 3px ink border), CTA row (ink-fill primary + two outline-ink).
   - `#mission` Mission & History — paragraph + timeline block (white, 2px ink border; rows `110px 1fr`; red Montserrat 900 year). **The `20XX` years are placeholders for the chapter — keep them, don't invent dates.**
   - `#counties` Counties We Serve — 4 county cards (`repeat(auto-fit,minmax(220px,1fr))`): Hidalgo (home base label), Cameron, Willacy, Starr.
   - `#committees` Committees — definition-style rows (`200px 1fr`, red uppercase name + description) in one bordered block. **Same six committees as Get Involved — extract a shared fixture, don't duplicate copy.** "Join a committee →" link-accent to Get Involved `#committees`.
   - `#bylaws` Bylaws & Code of Conduct — a semantic `<table>`: ink header row (Document / What it covers / action); rows Chapter Bylaws, Code of Conduct, Grievance Policy, Meeting Minutes; red "Read"/"Browse" links into the Interior template (`#documents`, `#grievance`).
   - `#faq` FAQ — **tabular two-column Q&A rows, intentionally NOT the accordion** used on Get Involved (`minmax(200px,2fr) 3fr` grid rows, bold question / answer, 2px ink dividers). Six rows, including "How do I switch to a monthly or Solidarity Dues rate?". Below it, the dues **CalloutCard** (`#dues`): white, 3px ink border, `8px 8px 0 #E9252E`, kicker "Switching your dues rate?", "Update my dues" button → join URL.
4. **Sidebar**: "On this page" nav (the six labels/anchors) + the ink "New here?" card shared with Get Involved.

## 3. Acceptance additions

- Header dropdown items on **any** page navigate to the correct About section (cross-page anchor + `scroll-margin-top` verified).
- About matches `designs/About.dc.html` at 1280w and 375w.
- FAQ renders as static rows (no disclosure widgets); the bylaws table is a real `<table>` with `scope` attributes.
- Committee copy comes from the same fixture as Get Involved.

Commit alongside the rest of Phase 4: `feat(pages): home, about, get involved, interior template`.
