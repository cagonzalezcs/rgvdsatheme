import { reactive } from "vue";

export interface ChapterEvent {
  id: string;
  /** ISO yyyy-mm-dd */
  date: string;
  /** display string, e.g. "7:00–8:30 PM" */
  time: string;
  cat: "chapter" | "poled" | "mutual" | "labor" | "electoral" | "social";
  title: string;
  location: string;
  desc: string;
  rsvpUrl?: string;
  /** Google Calendar render?action=TEMPLATE URL */
  gcalUrl?: string;
}

export interface EventCategory {
  id: string;
  label: string;
  /** null = the "All events" pseudo-category (no swatch) */
  color: string | null;
}

/* Default term colors match the --color-cat-* tokens in tailwind.css; WP term
 * meta overrides them at island mount via setCategories(). */
const DEFAULT_CATEGORIES: EventCategory[] = [
  { id: "chapter", label: "Chapter-Wide", color: "#E9252E" },
  { id: "poled", label: "Political Education", color: "#3A5BA0" },
  { id: "mutual", label: "Mutual Aid", color: "#1F7A48" },
  { id: "labor", label: "Labor", color: "#A3641C" },
  { id: "electoral", label: "Electoral", color: "#7C4396" },
  { id: "social", label: "Social", color: "#0E7C86" },
];

/* Reactive category store. Index 0 is always the "All events" pseudo-category
 * (owned by the store); the rest default to the fixture palette until an
 * island passes WP-driven categories. */
export const EVENT_CATEGORIES: EventCategory[] = reactive([
  { id: "all", label: "All events", color: null },
  ...DEFAULT_CATEGORIES,
]);

/** Replace the six real categories (the store keeps its own "all" pseudo). */
export function setCategories(cats: EventCategory[]): void {
  const real = cats.filter((c) => c.id !== "all");
  if (real.length === 0) return;
  EVENT_CATEGORIES.splice(1, EVENT_CATEGORIES.length - 1, ...real);
}

export function categoryById(id: string): EventCategory {
  return EVENT_CATEGORIES.find((c) => c.id === id) ?? EVENT_CATEGORIES[0];
}

/** Local-time date from ISO yyyy-mm-dd (avoids UTC shift of new Date(iso)). */
export function parseISODate(iso: string): Date {
  const [y, m, d] = iso.split("-").map(Number);
  return new Date(y, m - 1, d);
}

export const WEEKDAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
export const MONTH_NAMES = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];
export const MONTH_SHORTS = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
  "Sep",
  "Oct",
  "Nov",
  "Dec",
];

/* Prototype fixture (Calendar.dc.html) — becomes the WP seed in Phase 6. */
export const SAMPLE_EVENTS: ChapterEvent[] = [
  {
    id: "e1",
    date: "2026-07-02",
    time: "7:00–8:30 PM",
    cat: "poled",
    title: "Night School: What Is Democratic Socialism?",
    location: "McAllen Public Library, Community Room B",
    desc: "First session of our summer night school. No reading required — just bring your questions. We cover what democratic socialism is (and isn’t), and what it looks like here in the Valley.",
  },
  {
    id: "e2",
    date: "2026-07-07",
    time: "6:30–8:00 PM",
    cat: "mutual",
    title: "Community Fridge Restock & Cleanup",
    location: "Community fridge at 10th & Pecan, Edinburg",
    desc: "Help us restock, clean, and inventory the community fridge. Bring shelf-stable goods if you can — but hands are what we need most.",
  },
  {
    id: "e3",
    date: "2026-07-09",
    time: "7:00–8:00 PM",
    cat: "labor",
    title: "Know Your Rights at Work",
    location: "Online (Zoom)",
    desc: "A workshop on your rights on the job in Texas: concerted activity, retaliation, and what to document. Led by members of the Labor committee with guest organizers.",
  },
  {
    id: "e4",
    date: "2026-07-11",
    time: "2:00–4:00 PM",
    cat: "chapter",
    title: "July General Meeting",
    location: "Brownsville — Market Square Hall (+ Zoom)",
    desc: "Our monthly all-member meeting. Committee report-backs, votes on new business, and planning for the fall. Open to visitors — come see how the chapter works.",
  },
  {
    id: "e5",
    date: "2026-07-15",
    time: "7:00–8:00 PM",
    cat: "chapter",
    title: "RGV-DSA 101 (New Member Orientation)",
    location: "Online (Zoom)",
    desc: "New or curious? This one’s for you. A friendly intro to the chapter: who we are, what we’re working on, and how to plug in at whatever capacity you have.",
  },
  {
    id: "e6",
    date: "2026-07-18",
    time: "9:00 AM–12:00 PM",
    cat: "mutual",
    title: "Brake Light Clinic",
    location: "Parking lot, 500 W Ferguson Ave, Pharr",
    desc: "Free brake light replacement for anyone who pulls up — a broken light shouldn’t mean a traffic stop. Volunteers get a quick training at 8:30 AM. Tools and bulbs provided.",
  },
  {
    id: "e7",
    date: "2026-07-21",
    time: "7:00–9:00 PM",
    cat: "electoral",
    title: "Candidate Endorsement Forum",
    location: "Harlingen — Casa de Amistad",
    desc: "Hear from candidates seeking the chapter’s endorsement ahead of the fall elections. Members vote on endorsements at the August general meeting.",
  },
  {
    id: "e8",
    date: "2026-07-23",
    time: "7:30–9:00 PM",
    cat: "poled",
    title: "Reading Circle: A People’s Guide to Capitalism",
    location: "Weslaco — Cafecito on Texas Blvd",
    desc: "Chapters 1–2. New readers welcome; we always start with a recap. Copies available to borrow from the chapter library.",
  },
  {
    id: "e9",
    date: "2026-07-25",
    time: "6:00–9:00 PM",
    cat: "social",
    title: "Paleta Social",
    location: "Archer Park, McAllen",
    desc: "No agenda, no sign-in sheet — just paletas, lawn games, and comrades. Families welcome. First round of paletas is on the chapter.",
  },
  {
    id: "e10",
    date: "2026-07-28",
    time: "7:00–8:30 PM",
    cat: "labor",
    title: "Picket Support Training",
    location: "Online (Zoom)",
    desc: "How to show up well for striking workers: picket line etiquette, marshaling basics, and what support locals actually ask for.",
  },
  {
    id: "e11",
    date: "2026-08-01",
    time: "2:00–4:00 PM",
    cat: "chapter",
    title: "August General Meeting",
    location: "McAllen — Lark Community Center (+ Zoom)",
    desc: "Monthly all-member meeting. Endorsement votes from the July forum are on the agenda — members in good standing can vote.",
  },
  {
    id: "e12",
    date: "2026-08-04",
    time: "6:30–8:00 PM",
    cat: "mutual",
    title: "School Supply Distro Prep",
    location: "Alamo — member’s garage (address in WhatsApp)",
    desc: "Sorting and packing backpacks for the back-to-school distribution on the 15th. Snacks provided.",
  },
  {
    id: "e13",
    date: "2026-08-08",
    time: "10:00 AM–1:00 PM",
    cat: "electoral",
    title: "Voter Registration Drive",
    location: "Flea market, N 23rd St, Edinburg",
    desc: "Tabling and registering voters ahead of the October deadline. Volunteer deputy registrars will be on site — come learn how it’s done.",
  },
  {
    id: "e14",
    date: "2026-08-13",
    time: "7:00–8:30 PM",
    cat: "poled",
    title: "Night School: Socialism & the Border",
    location: "McAllen Public Library, Community Room B",
    desc: "Session two of summer night school: a Valley-centered look at labor, migration, and the border economy.",
  },
];
