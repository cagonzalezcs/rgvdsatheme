import { z } from "zod";

/* Single source of truth for the island contracts (contract-governance).
 * PHP serializers (inc/blog.php, inc/events.php, inc/rest.php) emit these
 * exact shapes; tests/fixtures/*.json is asserted against them from both
 * sides (PHPUnit equality, vitest zod parse). Edit contracts HERE — the
 * interfaces in posts.ts / events.ts are z.infer re-exports. */

/* Canonical category slugs. categories.json (theme root) is the runtime
 * registry; this literal tuple exists because TS cannot derive a literal
 * union from a JSON import — contracts.spec.ts asserts they stay in sync. */
export const POST_CATS = ["chapter", "poled", "mutual", "labor", "electoral", "social"] as const;

export const postCatSchema = z.enum(POST_CATS);

export const eventCategorySchema = z.object({
  id: z.string(),
  label: z.string(),
  /** null = the "All events" pseudo-category (no swatch) */
  color: z.string().nullable(),
});

export const chapterEventSchema = z.object({
  id: z.string(),
  /** ISO yyyy-mm-dd */
  date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  /** display string, e.g. "7:00–8:30 PM" */
  time: z.string(),
  cat: postCatSchema,
  title: z.string(),
  location: z.string(),
  desc: z.string(),
  rsvpUrl: z.string().optional(),
  /** Google Calendar render?action=TEMPLATE URL */
  gcalUrl: z.string().optional(),
  /** Single Event permalink — the modal/chip "View event" destination (04 §3d) */
  url: z.string().optional(),
});

export const blogPostSchema = z.object({
  id: z.string(),
  title: z.string(),
  slug: z.string(),
  cat: postCatSchema,
  /** display date, e.g. "Jun 14, 2026" */
  date: z.string(),
  excerpt: z.string(),
  dek: z.string().optional(),
  bylineMode: z.enum(["named", "committee"]),
  author: z.string().optional(),
  committee: z.string().optional(),
  featured: z.boolean().optional(),
  readMinutes: z.number().optional(),
  url: z.string(),
  /** featured/card image (null src = striped placeholder) */
  image: z
    .object({ src: z.string().nullable(), alt: z.string() })
    .nullable()
    .optional(),
});

export const postImageSchema = z.object({
  src: z.string().nullable(),
  alt: z.string(),
  caption: z.string().optional(),
  credit: z.string().optional(),
});

export const postBlockSchema = z.discriminatedUnion("type", [
  z.object({ type: z.literal("prose"), html: z.string() }),
  z.object({
    type: z.literal("image"),
    image: postImageSchema,
    breakout: z.boolean().optional(),
  }),
  z.object({
    type: z.literal("pull_quote"),
    quote: z.string(),
    attribution: z.string().optional(),
  }),
  z.object({
    type: z.literal("gallery"),
    layout: z.enum(["essay", "grid"]),
    images: z.array(postImageSchema),
  }),
  z.object({
    type: z.literal("person_quote"),
    photo: z.string().nullable(),
    alt: z.string(),
    quote: z.string(),
    translation: z.string().optional(),
    name: z.string(),
    role: z.string().optional(),
    lang: z.enum(["en", "es"]),
  }),
  z.object({
    type: z.literal("video"),
    url: z.string(),
    poster: z.string().nullable().optional(),
    caption: z.string().optional(),
    transcriptUrl: z.string().optional(),
  }),
  z.object({
    type: z.literal("audio"),
    file: z.string().nullable(),
    title: z.string(),
    duration: z.string().optional(),
    transcriptUrl: z.string(),
  }),
  z.object({
    type: z.literal("document"),
    url: z.string(),
    title: z.string(),
    description: z.string().optional(),
  }),
  z.object({ type: z.literal("event_embed"), event: chapterEventSchema.nullable() }),
  z.object({
    type: z.literal("action_callout"),
    heading: z.string(),
    body: z.string(),
    buttons: z.array(
      z.object({
        label: z.string(),
        url: z.string(),
        style: z.enum(["primary", "outline"]),
      }),
    ),
  }),
]);

export const singlePostDataSchema = z.object({
  title: z.string(),
  dek: z.string(),
  cat: postCatSchema,
  date: z.string(),
  readMinutes: z.number(),
  bylineMode: z.enum(["named", "committee"]),
  author: z.string(),
  authorAvatar: z.string().nullable(),
  committee: z.string(),
  authorBio: z.string(),
  committeeBio: z.string(),
  featuredImage: postImageSchema,
  blocks: z.array(postBlockSchema),
  tags: z.array(z.string()),
});

/* ---- Single Event (inc/events.php → SingleEvent island) ---- */

/** event_body flexible-content layouts (the event-appropriate block set). */
export const eventBlockSchema = z.discriminatedUnion("type", [
  z.object({ type: z.literal("prose"), html: z.string() }),
  z.object({
    type: z.literal("agenda"),
    items: z.array(z.object({ title: z.string(), desc: z.string().optional() })),
  }),
  z.object({ type: z.literal("good_to_know"), items: z.array(z.string()) }),
  z.object({ type: z.literal("a11y_note"), html: z.string() }),
  /** address auto-derived from venue/city; only present when locationType !== "online" */
  z.object({ type: z.literal("map"), address: z.string() }),
]);

export const eventContactSchema = z.object({
  name: z.string(),
  email: z.string(),
  phone: z.string(),
});

/** Related-events card (rail-free; carries its own permalink). */
export const relatedEventSchema = z.object({
  id: z.string(),
  date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  time: z.string(),
  cat: postCatSchema,
  title: z.string(),
  location: z.string(),
  url: z.string(),
});

export const singleEventDataSchema = z.object({
  title: z.string(),
  summary: z.string(),
  cat: postCatSchema,
  /** ISO yyyy-mm-dd — date block + full date line derive from this */
  date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  /** display range, e.g. "2:00–4:00 PM" */
  time: z.string(),
  /** display doors time, e.g. "1:30 PM"; "" when unset */
  doorsTime: z.string(),
  locationType: z.enum(["in-person", "online", "hybrid"]),
  venue: z.string(),
  city: z.string(),
  cost: z.string(),
  rsvpRequired: z.boolean(),
  /** "" when unset (button falls back to #rsvp) */
  rsvpUrl: z.string(),
  capacity: z.number().nullable(),
  /** maps URL from venue/city; "" when online / no location */
  directionsUrl: z.string(),
  /** Google Calendar render URL; "" when no start time */
  gcalUrl: z.string(),
  /** per-event iCal URL; "" = no endpoint exposed (button hidden) */
  icsUrl: z.string(),
  contact: eventContactSchema,
  featuredImage: postImageSchema,
  blocks: z.array(eventBlockSchema),
});

/* ---- REST envelopes (inc/rest.php) ---- */

export const postsEnvelopeSchema = z.object({
  posts: z.array(blogPostSchema),
  page: z.number().int(),
  perPage: z.number().int(),
  total: z.number().int(),
  totalPages: z.number().int(),
});

export const singlePostEnvelopeSchema = singlePostDataSchema.extend({
  readNext: z.array(blogPostSchema),
});

export const eventsEnvelopeSchema = z.object({
  events: z.array(chapterEventSchema),
  categories: z.array(eventCategorySchema),
});

export const categoriesEnvelopeSchema = z.object({
  categories: z.array(eventCategorySchema),
});

/* ---- Derived types (the one definition point) ---- */

export type PostCat = z.infer<typeof postCatSchema>;
export type EventCategory = z.infer<typeof eventCategorySchema>;
export type ChapterEvent = z.infer<typeof chapterEventSchema>;
export type BlogPost = z.infer<typeof blogPostSchema>;
export type PostImage = z.infer<typeof postImageSchema>;
export type PostBlock = z.infer<typeof postBlockSchema>;
export type SinglePostData = z.infer<typeof singlePostDataSchema>;
export type EventBlock = z.infer<typeof eventBlockSchema>;
export type EventContact = z.infer<typeof eventContactSchema>;
export type RelatedEvent = z.infer<typeof relatedEventSchema>;
export type SingleEventData = z.infer<typeof singleEventDataSchema>;
export type PostsEnvelope = z.infer<typeof postsEnvelopeSchema>;
export type SinglePostEnvelope = z.infer<typeof singlePostEnvelopeSchema>;
export type EventsEnvelope = z.infer<typeof eventsEnvelopeSchema>;
export type CategoriesEnvelope = z.infer<typeof categoriesEnvelopeSchema>;
