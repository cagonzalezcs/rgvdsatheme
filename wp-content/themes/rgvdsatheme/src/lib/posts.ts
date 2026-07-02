import { reactive, watchSyncEffect } from "vue";

import {
  type ChapterEvent,
  EVENT_CATEGORIES,
  type EventCategory,
  SAMPLE_EVENTS,
} from "@/lib/events";

export type PostCat = ChapterEvent["cat"];

export interface BlogPost {
  id: string;
  title: string;
  slug: string;
  cat: PostCat;
  /** display date, e.g. "Jun 14, 2026" */
  date: string;
  excerpt: string;
  dek?: string;
  bylineMode: "named" | "committee";
  author?: string;
  committee?: string;
  featured?: boolean;
  readMinutes?: number;
  url: string;
  /** featured/card image (null src = striped placeholder) */
  image?: { src: string | null; alt: string } | null;
}

/* Post categories share the event taxonomy colors; only the "all" label
 * differs. Kept in sync with the reactive store in lib/events.ts so
 * setCategories() updates blog chips too. */
export const POST_CATEGORIES: EventCategory[] = reactive([]);
watchSyncEffect(() => {
  POST_CATEGORIES.splice(
    0,
    POST_CATEGORIES.length,
    ...EVENT_CATEGORIES.map((c) => (c.id === "all" ? { ...c, label: "All posts" } : c)),
  );
});

export function postCategoryById(id: string): EventCategory {
  return POST_CATEGORIES.find((c) => c.id === id) ?? POST_CATEGORIES[0];
}

export interface PostImage {
  src: string | null;
  alt: string;
  caption?: string;
  credit?: string;
}

/* post_blocks — one member per ACF flexible-content layout (registered in Phase 6). */
export type PostBlock =
  | { type: "prose"; html: string }
  | { type: "image"; image: PostImage; breakout?: boolean }
  | { type: "pull_quote"; quote: string; attribution?: string }
  | { type: "gallery"; layout: "essay" | "grid"; images: PostImage[] }
  | {
      type: "person_quote";
      photo: string | null;
      alt: string;
      quote: string;
      translation?: string;
      name: string;
      role?: string;
      lang: "en" | "es";
    }
  | {
      type: "video";
      url: string;
      poster?: string | null;
      caption?: string;
      transcriptUrl?: string;
    }
  | {
      type: "audio";
      file: string | null;
      title: string;
      duration?: string;
      transcriptUrl: string;
    }
  | { type: "document"; url: string; title: string; description?: string }
  | { type: "event_embed"; event: ChapterEvent }
  | {
      type: "action_callout";
      heading: string;
      body: string;
      buttons: { label: string; url: string; style: "primary" | "outline" }[];
    };

export interface SinglePostData {
  title: string;
  dek: string;
  cat: PostCat;
  date: string;
  readMinutes: number;
  bylineMode: "named" | "committee";
  author: string;
  authorAvatar: string | null;
  committee: string;
  authorBio: string;
  committeeBio: string;
  featuredImage: PostImage;
  blocks: PostBlock[];
  tags: string[];
}

/* Prototype fixture (Blog.dc.html) — all copy is deliberately lorem ipsum;
 * layouts are real, the chapter writes the posts (Phase 6). */
export const SAMPLE_POSTS: BlogPost[] = [
  {
    id: "p1",
    title: "Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod",
    slug: "lorem-ipsum-dolor",
    cat: "mutual",
    date: "Jun 14, 2026",
    excerpt:
      "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
    dek: "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
    bylineMode: "named",
    author: "Author Name",
    committee: "Mutual Aid Committee",
    featured: true,
    readMinutes: 6,
    url: "#",
  },
  {
    id: "p2",
    title: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem",
    slug: "sed-ut-perspiciatis",
    cat: "poled",
    date: "Jun 28, 2026",
    excerpt:
      "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.",
    bylineMode: "named",
    author: "Author Name",
    url: "#",
  },
  {
    id: "p3",
    title: "Nemo enim ipsam voluptatem quia voluptas sit aspernatur",
    slug: "nemo-enim-ipsam",
    cat: "labor",
    date: "Jun 21, 2026",
    excerpt:
      "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
    bylineMode: "committee",
    committee: "Labor Committee",
    url: "#",
  },
  {
    id: "p4",
    title: "Ut enim ad minima veniam quis nostrum",
    slug: "ut-enim-ad-minima",
    cat: "chapter",
    date: "Jun 8, 2026",
    excerpt:
      "Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae.",
    bylineMode: "named",
    author: "Author Name",
    url: "#",
  },
  {
    id: "p5",
    title: "Quis autem vel eum iure reprehenderit",
    slug: "quis-autem-vel-eum",
    cat: "electoral",
    date: "May 30, 2026",
    excerpt:
      "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium.",
    bylineMode: "committee",
    committee: "Electoral Committee",
    url: "#",
  },
  {
    id: "p6",
    title: "Neque porro quisquam est qui dolorem",
    slug: "neque-porro-quisquam",
    cat: "social",
    date: "May 22, 2026",
    excerpt:
      "Et harum quidem rerum facilis est et expedita distinctio nam libero tempore.",
    bylineMode: "named",
    author: "Author Name",
    url: "#",
  },
  {
    id: "p7",
    title: "Temporibus autem quibusdam et aut officiis debitis",
    slug: "temporibus-autem",
    cat: "labor",
    date: "May 16, 2026",
    excerpt:
      "Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus.",
    bylineMode: "named",
    author: "Author Name",
    url: "#",
  },
  {
    id: "p8",
    title: "Nam libero tempore cum soluta nobis",
    slug: "nam-libero-tempore",
    cat: "poled",
    date: "May 9, 2026",
    excerpt:
      "Omnis voluptas assumenda est, omnis dolor repellendus maiores alias consequatur.",
    bylineMode: "committee",
    committee: "Political Education Committee",
    url: "#",
  },
  {
    id: "p9",
    title: "At vero eos et accusamus et iusto odio",
    slug: "at-vero-eos",
    cat: "mutual",
    date: "May 2, 2026",
    excerpt:
      "Quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.",
    bylineMode: "named",
    author: "Author Name",
    url: "#",
  },
];

/* Prototype fixture (Blog Post.dc.html) — every block type once, in prototype order. */
export const SAMPLE_SINGLE: SinglePostData = {
  title: "Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod",
  dek: "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
  cat: "mutual",
  date: "June 14, 2026",
  readMinutes: 6,
  bylineMode: "named",
  author: "Author Name",
  authorAvatar: null,
  committee: "Mutual Aid Committee",
  authorBio:
    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
  committeeBio:
    "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Posts are written collectively.",
  featuredImage: {
    src: null,
    alt: "Featured photo",
    caption: "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
    credit: "Photo: RGV DSA",
  },
  blocks: [
    {
      type: "prose",
      html: "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>",
    },
    {
      type: "image",
      image: {
        src: null,
        alt: "Photo",
        caption: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.",
      },
    },
    {
      type: "prose",
      html: "<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>",
    },
    {
      type: "pull_quote",
      quote:
        "“Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.”",
      attribution: "Attribution line",
    },
    {
      type: "gallery",
      layout: "essay",
      images: [
        { src: null, alt: "Wide photo", caption: "Lorem ipsum dolor sit amet." },
        { src: null, alt: "Photo", caption: "Consectetur adipiscing elit." },
        { src: null, alt: "Photo", caption: "Sed do eiusmod tempor." },
      ],
    },
    {
      type: "person_quote",
      photo: null,
      alt: "Portrait",
      quote: "“Lorem ipsum dolor sit amet, consectetur adipiscing elit.”",
      translation: "“Translation of the quote appears here.”",
      name: "Person Name",
      role: "Role or affiliation",
      lang: "es",
    },
    {
      type: "video",
      url: "",
      caption: "Watch: lorem ipsum dolor sit amet consectetur. Captioned in English and Spanish.",
      transcriptUrl: "#",
    },
    {
      type: "prose",
      html: "<h2>Lorem ipsum dolor sit amet</h2><p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt, neque porro quisquam est qui dolorem ipsum quia dolor sit amet.</p>",
    },
    {
      type: "audio",
      file: null,
      title: "Listen: lorem ipsum audio title",
      duration: "3:12",
      transcriptUrl: "#",
    },
    {
      type: "document",
      url: "#",
      title: "Lorem ipsum document title",
      description: "Bilingual · 2 pages · 340 KB",
    },
    {
      type: "event_embed",
      event: SAMPLE_EVENTS.find((e) => e.id === "e6") ?? SAMPLE_EVENTS[0],
    },
    {
      type: "action_callout",
      heading: "Lorem ipsum dolor sit amet",
      body: "Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.",
      buttons: [
        { label: "Primary action", url: "/get-involved/", style: "primary" },
        { label: "Secondary action", url: "#", style: "outline" },
      ],
    },
  ],
  tags: ["tag one", "tag two"],
};
