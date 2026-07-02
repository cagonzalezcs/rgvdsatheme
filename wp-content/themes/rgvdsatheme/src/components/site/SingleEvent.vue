<script setup lang="ts">
import { computed, ref } from "vue";
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import EventBlocks from "@/components/site/EventBlocks.vue";
import EventCard from "@/components/site/EventCard.vue";
import {
  categoryById,
  type EventCategory,
  hexToRgba,
  MONTH_NAMES,
  parseISODate,
  type RelatedEvent,
  setCategories,
  type SingleEventData,
  WEEKDAYS,
  MONTH_SHORTS,
} from "@/lib/events";

const props = withDefaults(
  defineProps<{
    event: SingleEventData;
    /** WP term-driven categories — replaces the registry palette when provided */
    categories?: EventCategory[];
    related?: RelatedEvent[];
    showRelated?: boolean;
    homeUrl?: string;
    calendarUrl?: string;
  }>(),
  {
    categories: undefined,
    related: () => [],
    showRelated: true,
    homeUrl: "/",
    calendarUrl: "/calendar/",
  },
);

if (props.categories && props.categories.length > 0) setCategories(props.categories);

const WEEKDAYS_LONG = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
];

const category = computed(() => categoryById(props.event.cat));
const accent = computed(() => category.value.color ?? "#1C1917");
const accentSoft = computed(() => hexToRgba(accent.value, 0.1));

const date = computed(() => parseISODate(props.event.date));
const weekdayShort = computed(() => WEEKDAYS[date.value.getDay()]);
const weekdayLong = computed(() => WEEKDAYS_LONG[date.value.getDay()]);
const monthShort = computed(() => MONTH_SHORTS[date.value.getMonth()]);
const monthLong = computed(() => MONTH_NAMES[date.value.getMonth()]);
const dayNum = computed(() => date.value.getDate());
const year = computed(() => date.value.getFullYear());

/** hero chip, e.g. "Sat, July 11, 2026" */
const heroDate = computed(
  () => `${weekdayShort.value}, ${monthLong.value} ${dayNum.value}, ${year.value}`,
);
/** rail line, e.g. "Saturday, July 11" */
const railDate = computed(() => `${weekdayLong.value}, ${monthLong.value} ${dayNum.value}`);

const isOnline = computed(() => props.event.locationType === "online");
const showAddress = computed(
  () => !isOnline.value && (props.event.venue !== "" || props.event.city !== ""),
);
const showOnline = computed(() => props.event.locationType !== "in-person");

const locationChip = computed(() => {
  if (isOnline.value) return "Online";
  return props.event.venue || props.event.city || "Location TBA";
});
const costLabel = computed(() => props.event.cost || "Free · open to the public");
const rsvpStatus = computed(() =>
  props.event.rsvpRequired ? "RSVP required" : "No RSVP needed — just show up",
);
const rsvpHref = computed(() => props.event.rsvpUrl || "#rsvp");
const rsvpLabel = computed(() => (props.event.rsvpRequired ? "RSVP now →" : "RSVP →"));

const hasContact = computed(
  () =>
    props.event.contact.name !== "" ||
    props.event.contact.email !== "" ||
    props.event.contact.phone !== "",
);

/* Copy link w/ "Copied ✓" state (ported from SinglePost) */
const copied = ref(false);
let copyTimer: ReturnType<typeof setTimeout> | undefined;
function copyLink() {
  void navigator.clipboard?.writeText(location.href).catch(() => {});
  copied.value = true;
  clearTimeout(copyTimer);
  copyTimer = setTimeout(() => (copied.value = false), 2000);
}
const copyLabel = computed(() => (copied.value ? "Copied ✓" : "Copy link"));
const mailShareUrl = computed(
  () => `mailto:?subject=${encodeURIComponent(`${props.event.title} — RGV DSA`)}`,
);
</script>

<template>
  <div class="single-event">
    <!-- ============ EVENT HERO (red band) ============ -->
    <section class="bg-brand-red px-4 pb-[130px] pt-11 text-white md:px-6 md:pb-[150px]" data-tone="red">
      <div class="mx-auto flex max-w-[1140px] flex-col items-start gap-5">
        <nav aria-label="Breadcrumb">
          <ol class="m-0 flex list-none flex-wrap items-center gap-2 rounded-full bg-white px-4 py-1.5 text-[0.85rem] font-bold">
            <li class="flex items-center gap-2">
              <a :href="homeUrl" class="text-red no-underline hover:underline hover:underline-offset-[3px]">Home</a>
              <span aria-hidden="true" class="text-text-muted">/</span>
            </li>
            <li class="flex items-center gap-2">
              <a :href="calendarUrl" class="text-red no-underline hover:underline hover:underline-offset-[3px]">Calendar</a>
              <span aria-hidden="true" class="text-text-muted">/</span>
            </li>
            <li aria-current="page" class="text-ink">{{ event.title }}</li>
          </ol>
        </nav>

        <CategoryTag :cat-id="event.cat" :href="calendarUrl" />

        <h1 class="m-0 max-w-[24ch] font-display text-[clamp(2rem,4.6vw,3.3rem)] font-black leading-[1.12] tracking-[-0.01em] [text-wrap:balance]">
          {{ event.title }}
        </h1>

        <p v-if="event.summary" class="m-0 max-w-[52ch] text-[1.05rem] leading-[1.5] md:text-[1.3rem] lg:text-[1.5rem]">
          {{ event.summary }}
        </p>

        <div class="mt-0.5 flex flex-wrap items-center gap-2.5">
          <span class="flex items-center gap-2 rounded-full bg-[rgba(28,25,23,0.85)] px-[18px] py-2.5 text-[0.95rem] font-bold">
            <span aria-hidden="true">📅</span>{{ heroDate }}
          </span>
          <span v-if="event.time" class="flex items-center gap-2 rounded-full bg-[rgba(28,25,23,0.85)] px-[18px] py-2.5 text-[0.95rem] font-bold">
            <span aria-hidden="true">🕐</span>{{ event.time }}
          </span>
          <span class="flex items-center gap-2 rounded-full bg-[rgba(28,25,23,0.85)] px-[18px] py-2.5 text-[0.95rem] font-bold">
            <span aria-hidden="true">📍</span>{{ locationChip }}
          </span>
          <a :href="rsvpHref" class="rounded-full bg-white px-[22px] py-2.5 text-[0.95rem] font-bold text-red no-underline hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(28,25,23,0.25)]">RSVP →</a>
        </div>
      </div>
    </section>

    <!-- ============ CONTENT + DETAILS RAIL ============ -->
    <section class="bg-white px-4 pb-20 md:px-6" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] flex-col gap-14 lg:flex-row lg:items-start">
        <!-- Main column (first in DOM → renders above the rail on mobile/tablet) -->
        <article class="flex min-w-0 flex-col gap-8 lg:flex-1">
          <!-- Featured image, pulled up over the red band -->
          <figure class="m-0 -mt-16 flex w-full flex-col md:-mt-[84px] lg:-mt-[108px]">
            <div class="overflow-hidden rounded-[18px] bg-white shadow-[0_16px_44px_rgba(28,25,23,0.24)]">
              <div class="h-[clamp(260px,40vw,460px)]">
                <ImageSlot :src="event.featuredImage.src" :alt="event.featuredImage.alt" label="Event photo or flyer" />
              </div>
            </div>
            <figcaption v-if="event.featuredImage.caption" class="px-1 pt-3 text-[0.9rem] leading-[1.5] text-text-muted">
              {{ event.featuredImage.caption }}
              <span v-if="event.featuredImage.credit" class="text-text-faint">{{ event.featuredImage.credit }}</span>
            </figcaption>
          </figure>

          <EventBlocks :blocks="event.blocks" :accent="accent" :accent-soft="accentSoft" />
        </article>

        <!-- Details rail: full-width below the body on mobile/tablet; sticky beside at lg -->
        <aside
          id="rsvp"
          aria-label="Event details"
          class="flex w-full flex-col gap-5 scroll-mt-[110px] lg:w-[340px] lg:flex-none lg:-mt-[108px] lg:sticky lg:top-[calc(110px+var(--wp-admin--admin-bar--height,0px))]"
        >
          <!-- Details card -->
          <div class="overflow-hidden rounded-[18px] bg-white shadow-card-hover-lg">
            <div class="flex items-center justify-between gap-3 px-5 py-3.5 text-white" :style="{ background: accent }">
              <span class="font-display text-[1.05rem] font-extrabold">Event details</span>
              <span class="rounded-full bg-white/20 px-2.5 py-1 text-[0.72rem] font-bold uppercase tracking-[0.06em]">{{ category.label }}</span>
            </div>

            <div class="flex flex-col gap-4 p-5">
              <!-- Date block -->
              <div class="flex items-center gap-3.5">
                <div class="flex w-[66px] flex-none flex-col items-center justify-center gap-px rounded-[12px] px-1.5 py-2 text-center text-white" :style="{ background: accent }">
                  <span class="font-display text-[0.68rem] font-bold uppercase tracking-[0.1em]">{{ weekdayShort }}</span>
                  <span class="font-display text-[1.7rem] font-extrabold leading-none">{{ dayNum }}</span>
                  <span class="font-display text-[0.68rem] font-bold uppercase tracking-[0.1em]">{{ monthShort }}</span>
                </div>
                <div class="flex flex-col gap-0.5">
                  <span class="font-display text-[1.05rem] font-bold leading-[1.3]">{{ railDate }}</span>
                  <span class="text-[0.9rem] font-semibold text-text-muted">{{ year }}</span>
                </div>
              </div>

              <div class="h-px bg-hairline"></div>

              <!-- Time -->
              <div v-if="event.time" class="flex items-start gap-3">
                <span aria-hidden="true" class="flex-none text-[1.05rem] leading-[1.4]">🕐</span>
                <div class="flex min-w-0 flex-col gap-px">
                  <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">Time</span>
                  <span class="text-[0.98rem] font-semibold text-text-strong">{{ event.time }}</span>
                  <span v-if="event.doorsTime" class="text-[0.85rem] text-text-faint">Doors open {{ event.doorsTime }}</span>
                </div>
              </div>

              <!-- Location -->
              <div v-if="showAddress" class="flex items-start gap-3">
                <span aria-hidden="true" class="flex-none text-[1.05rem] leading-[1.4]">📍</span>
                <div class="flex min-w-0 flex-col gap-px">
                  <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">Location</span>
                  <span v-if="event.venue" class="text-[0.98rem] font-semibold text-text-strong">{{ event.venue }}</span>
                  <span v-if="event.city" class="text-[0.85rem] leading-[1.4] text-text-faint">{{ event.city }}</span>
                  <a v-if="event.directionsUrl" :href="event.directionsUrl" target="_blank" rel="noopener" class="mt-0.5 text-[0.85rem] font-bold text-red no-underline hover:underline hover:underline-offset-[3px]">Get directions →</a>
                </div>
              </div>

              <!-- Online -->
              <div v-if="showOnline" class="flex items-start gap-3">
                <span aria-hidden="true" class="flex-none text-[1.05rem] leading-[1.4]">💻</span>
                <div class="flex min-w-0 flex-col gap-px">
                  <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">Online</span>
                  <span class="text-[0.98rem] font-semibold text-text-strong">Join link shared on RSVP</span>
                  <a v-if="event.rsvpUrl" :href="event.rsvpUrl" target="_blank" rel="noopener" class="mt-0.5 text-[0.85rem] font-bold text-red no-underline hover:underline hover:underline-offset-[3px]">Get the link →</a>
                </div>
              </div>

              <!-- Cost -->
              <div class="flex items-start gap-3">
                <span aria-hidden="true" class="flex-none text-[1.05rem] leading-[1.4]">🎟️</span>
                <div class="flex min-w-0 flex-col gap-px">
                  <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">Cost</span>
                  <span class="text-[0.98rem] font-semibold text-text-strong">{{ costLabel }}</span>
                </div>
              </div>

              <!-- RSVP status -->
              <div class="flex items-start gap-3">
                <span aria-hidden="true" class="flex-none text-[1.05rem] leading-[1.4]">✅</span>
                <div class="flex min-w-0 flex-col gap-px">
                  <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">RSVP</span>
                  <span class="text-[0.98rem] font-semibold text-text-strong">{{ rsvpStatus }}</span>
                </div>
              </div>

              <a
                :href="rsvpHref"
                class="rounded-full px-6 py-3 text-center font-display text-[0.98rem] font-bold text-white no-underline transition hover:brightness-105 hover:shadow-[0_8px_22px_rgba(28,25,23,0.28)]"
                :style="{ background: accent }"
              >
                {{ rsvpLabel }}
              </a>

              <!-- Add to calendar (iCal button hidden when no per-event endpoint) -->
              <div v-if="event.gcalUrl || event.icsUrl" class="flex flex-col gap-2">
                <span class="font-display text-[0.72rem] font-bold uppercase tracking-[0.08em] text-text-muted">Add to calendar</span>
                <div class="flex flex-wrap gap-2">
                  <a v-if="event.gcalUrl" :href="event.gcalUrl" target="_blank" rel="noopener" class="flex-1 whitespace-nowrap rounded-full border-2 border-red px-3.5 py-2 text-center text-[0.85rem] font-bold text-red no-underline hover:border-red-hover hover:bg-wash">Google</a>
                  <a v-if="event.icsUrl" :href="event.icsUrl" class="flex-1 whitespace-nowrap rounded-full border-2 border-red px-3.5 py-2 text-center text-[0.85rem] font-bold text-red no-underline hover:border-red-hover hover:bg-wash">iCal</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact card -->
          <div v-if="hasContact" class="flex flex-col gap-3 rounded-[16px] bg-off-white px-5 py-[18px]">
            <div class="font-display text-[0.82rem] font-bold uppercase tracking-[0.08em] text-text-muted">Questions? Contact</div>
            <div class="flex flex-col gap-1.5">
              <span v-if="event.contact.name" class="text-[0.98rem] font-bold text-text-strong">{{ event.contact.name }}</span>
              <a v-if="event.contact.email" :href="`mailto:${event.contact.email}`" class="text-[0.92rem] font-semibold text-red no-underline hover:underline hover:underline-offset-[3px]">{{ event.contact.email }}</a>
              <a v-if="event.contact.phone" :href="`tel:${event.contact.phone.replace(/[^0-9+]/g, '')}`" class="text-[0.92rem] font-semibold text-red no-underline hover:underline hover:underline-offset-[3px]">{{ event.contact.phone }}</a>
            </div>
          </div>

          <!-- Share card -->
          <div class="flex flex-col gap-2.5 rounded-[16px] bg-off-white px-5 py-[18px]">
            <div class="font-display text-[0.82rem] font-bold uppercase tracking-[0.08em] text-text-muted">Share this event</div>
            <button type="button" class="cursor-pointer border-0 bg-transparent p-0 text-left text-[0.9rem] font-bold text-red hover:underline hover:underline-offset-[3px]" @click="copyLink">
              {{ copyLabel }}
            </button>
            <a :href="mailShareUrl" class="text-[0.9rem] font-bold text-red no-underline hover:underline hover:underline-offset-[3px]">Email this event</a>
          </div>
        </aside>
      </div>
    </section>

    <!-- ============ MORE UPCOMING EVENTS ============ -->
    <section v-if="showRelated && related.length > 0" class="bg-off-white px-4 py-16 md:px-6 md:py-20" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] flex-col gap-7">
        <div class="flex flex-wrap items-baseline justify-between gap-4">
          <h2 class="m-0 font-display text-[clamp(1.5rem,3.2vw,2.2rem)] font-extrabold leading-[1.15] tracking-[-0.01em]">More upcoming events</h2>
          <a :href="calendarUrl" class="text-base font-bold text-red no-underline hover:underline hover:underline-offset-4">Full calendar →</a>
        </div>
        <div class="grid gap-6 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
          <EventCard v-for="ev in related" :key="ev.id" :event="ev" />
        </div>
      </div>
    </section>
  </div>
</template>
