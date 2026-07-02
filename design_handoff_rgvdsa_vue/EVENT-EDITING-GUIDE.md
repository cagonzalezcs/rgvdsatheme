# Event editing guide (admin cheatsheet)

How each field on an **Event** maps to the public event page. The `event` post type uses the **classic editor**, so the **Event details** box (with its **When / Where / Details / Contact / Body** tabs) and the **Event body** blocks appear right below the title — not in a collapsed "Meta Boxes" drawer.

## Content box vs. Summary — the one thing people miss

There are **two** summary-style fields, and they are not the same:

- **Content** box (the big editor under the title) → the short blurb shown in the **Calendar pop-up** and the calendar list (`desc`). It is **not** shown as the hero lede.
- **Summary** field (Details tab) → the **hero lede** at the top of the event page, under the title.

Fill both. Keep the Content box to a sentence or two; the Summary can be a touch longer (~52 characters per line reads best).

## Field → frontend map

| Field (tab) | Where it shows on the event page |
|---|---|
| **Content** box (editor) | Short blurb for the Calendar pop-up + calendar list — *not* the hero lede |
| **Summary** (Details) | Hero lede under the title |
| **Start / End date & time** (When) | Hero date chip, **Time** row, rail date block, calendar placement |
| **Doors open** (When) | "Doors open …" line under the Time row |
| **Location type** (Where) | Toggles the **Location** row vs the **Online** row + the **map** body block |
| **Venue / City** (Where) | **Location** row + "Get directions", hero location chip, map address |
| **Cost** (Details) | **Cost** row — blank shows "Free · open to the public" |
| **RSVP required** (Details) | **RSVP** status line + RSVP button label |
| **RSVP URL** (Details) | RSVP button destination / online "Get the link" join link |
| **Capacity** (Details) | **Space** row in the details rail (e.g. "40 spots"); blank hides the row |
| **Contact name / email / phone** (Contact) | **Contact** card; falls back to the chapter email when all three are blank |
| **Event body** blocks (Body) | Article body: prose · agenda · good-to-know · accessibility note · map |
| Featured image + Category term | Featured image; the category term **color** is the whole-page accent |

## How Location type and RSVP required change the layout

**Location type** drives three things at once:

- **In person** → shows the **Location** row (venue, city, "Get directions") and the **map** block (if you add a map block to the body). No Online row.
- **Online** → hides the Location row and the map block; shows the **Online** row ("Join link shared on RSVP" + "Get the link" if an RSVP URL is set).
- **Hybrid** → shows **both** the Location row and the Online row, and keeps the map.

**RSVP required** flips the wording in two places:

- On → RSVP row reads "RSVP required" and the button says "RSVP now →".
- Off → RSVP row reads "No RSVP needed — just show up" and the button says "RSVP →".

The RSVP button points at **RSVP URL** when set, otherwise it just anchors to the details card.

## Event body blocks

Add these in any order under the **Body** tab (they are reorderable):

- **Prose** — free rich text ("About this event").
- **Agenda** — numbered rows of title + short description.
- **Good to know** — a plain bulleted logistics list.
- **Accessibility & childcare** — the accent-tinted aside; recommended on nearly every event.
- **Getting there / map** — a map from the venue/city address. Automatically **dropped for online-only events**.
