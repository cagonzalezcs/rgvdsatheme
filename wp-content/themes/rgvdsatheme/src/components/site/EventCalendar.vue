<script setup lang="ts">
import { computed, ref, watch } from "vue";
import EventDetailDialog from "@/components/site/EventDetailDialog.vue";
import EventListView from "@/components/site/EventListView.vue";
import MonthGrid from "@/components/site/MonthGrid.vue";
import {
  type ChapterEvent,
  EVENT_CATEGORIES,
  type EventCategory,
  MONTH_NAMES,
  parseISODate,
  SAMPLE_EVENTS,
  setCategories,
} from "@/lib/events";

const props = withDefaults(
  defineProps<{
    events?: ChapterEvent[];
    /** WP term-driven categories — replaces the fixture palette when provided */
    categories?: EventCategory[];
    defaultView?: "month" | "list";
    showCategoryColors?: boolean;
    showSubscribe?: boolean;
    googleCalUrl?: string;
    icsUrl?: string;
  }>(),
  {
    events: () => SAMPLE_EVENTS,
    categories: undefined,
    defaultView: "month",
    showCategoryColors: true,
    showSubscribe: true,
    googleCalUrl: "#",
    icsUrl: "#",
  },
);

if (props.categories && props.categories.length > 0) setCategories(props.categories);

/* ---- state (view + filter survive reload via URL params) ---- */
const initialParams = new URLSearchParams(window.location.search);
const initialView = initialParams.get("view");
const initialCat = initialParams.get("category");

const view = ref<"month" | "list">(
  initialView === "month" || initialView === "list" ? initialView : props.defaultView,
);
const activeCat = ref(
  EVENT_CATEGORIES.some((c) => c.id === initialCat && c.id !== "all")
    ? (initialCat as string)
    : "all",
);
const monthOffset = ref(0);
const selectedId = ref<string | null>(null);

watch([view, activeCat], () => {
  const params = new URLSearchParams(window.location.search);
  if (view.value === props.defaultView) params.delete("view");
  else params.set("view", view.value);
  if (activeCat.value === "all") params.delete("category");
  else params.set("category", activeCat.value);
  const qs = params.toString();
  history.replaceState(null, "", qs ? `${location.pathname}?${qs}` : location.pathname);
});

/* ---- month math ---- */
const now = new Date();
const visibleMonth = computed(() => {
  const base = new Date(now.getFullYear(), now.getMonth() + monthOffset.value, 1);
  return { year: base.getFullYear(), month: base.getMonth() };
});
const monthLabel = computed(
  () => `${MONTH_NAMES[visibleMonth.value.month]} ${visibleMonth.value.year}`,
);

/* ---- filtering ---- */
const filtered = computed(() =>
  props.events.filter((e) => activeCat.value === "all" || e.cat === activeCat.value),
);
const monthEvents = computed(() =>
  filtered.value
    .filter((e) => {
      const d = parseISODate(e.date);
      return (
        d.getFullYear() === visibleMonth.value.year && d.getMonth() === visibleMonth.value.month
      );
    })
    .sort((a, b) => a.date.localeCompare(b.date)),
);

const selectedEvent = computed(
  () => props.events.find((e) => e.id === selectedId.value) ?? null,
);
</script>

<template>
  <div class="event-calendar">
    <section class="bg-white px-6 pt-10" data-tone="cream">
      <div class="mx-auto flex max-w-[1200px] flex-col gap-5">
        <div class="flex flex-wrap items-center justify-between gap-5">
          <div class="flex items-stretch overflow-hidden rounded-[12px] bg-white shadow-card">
            <button
              type="button"
              aria-label="Previous month"
              class="cursor-pointer bg-transparent px-[18px] py-2.5 text-[1.1rem] font-bold text-red hover:bg-wash"
              @click="monthOffset--"
            >
              ←
            </button>
            <div
              aria-live="polite"
              class="flex min-w-[210px] items-center justify-center px-[26px] py-2.5 font-display text-[1.1rem] font-bold"
            >
              {{ monthLabel }}
            </div>
            <button
              type="button"
              aria-label="Next month"
              class="cursor-pointer bg-transparent px-[18px] py-2.5 text-[1.1rem] font-bold text-red hover:bg-wash"
              @click="monthOffset++"
            >
              →
            </button>
          </div>

          <div role="group" aria-label="View" class="flex gap-1 rounded-full bg-off-white p-1">
            <button
              type="button"
              :aria-pressed="view === 'month'"
              class="cursor-pointer rounded-full px-6 py-2.5 font-display text-[0.85rem] font-bold"
              :class="view === 'month' ? 'bg-ink text-white' : 'text-ink'"
              @click="view = 'month'"
            >
              Month
            </button>
            <button
              type="button"
              :aria-pressed="view === 'list'"
              class="cursor-pointer rounded-full px-6 py-2.5 font-display text-[0.85rem] font-bold"
              :class="view === 'list' ? 'bg-ink text-white' : 'text-ink'"
              @click="view = 'list'"
            >
              List
            </button>
          </div>
        </div>

        <div aria-label="Filter by category" class="flex flex-wrap items-center gap-2">
          <span class="mr-1.5 font-display text-[0.82rem] font-bold uppercase tracking-[0.06em] text-text-muted">Filter:</span>
          <button
            v-for="cat in EVENT_CATEGORIES"
            :key="cat.id"
            type="button"
            :aria-pressed="activeCat === cat.id"
            class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-3.5 py-2 text-[0.85rem] font-bold"
            :class="activeCat === cat.id ? 'border-ink bg-ink text-white' : 'border-border-control bg-white text-ink'"
            @click="activeCat = cat.id"
          >
            <span
              v-if="cat.color && showCategoryColors"
              aria-hidden="true"
              class="inline-block size-2.5 flex-none rounded-full"
              :style="{ background: cat.color }"
            ></span>
            <span>{{ cat.label }}</span>
          </button>
        </div>
      </div>
    </section>

    <section v-if="view === 'month'" class="bg-white px-6 pb-12 pt-6" data-tone="cream">
      <div class="mx-auto max-w-[1200px]">
        <MonthGrid
          :year="visibleMonth.year"
          :month="visibleMonth.month"
          :events="filtered"
          :show-category-colors="showCategoryColors"
          @select="selectedId = $event"
        />
        <p class="mt-3.5 text-[0.9rem] text-text-muted">
          Select an event for details, location, and how to RSVP.
        </p>
      </div>
    </section>

    <section v-else class="bg-white px-6 pb-12 pt-6" data-tone="cream">
      <div class="mx-auto max-w-[900px]">
        <EventListView
          :events="monthEvents"
          :show-category-colors="showCategoryColors"
          @select="selectedId = $event"
        />
      </div>
    </section>

    <EventDetailDialog
      :event="selectedEvent"
      :show-category-colors="showCategoryColors"
      @close="selectedId = null"
    />

    <section v-if="showSubscribe" class="bg-ink px-6 py-14 text-white" data-tone="ink">
      <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-8">
        <div class="flex max-w-[56ch] flex-col gap-2">
          <h2 class="m-0 font-display text-[1.6rem] font-extrabold tracking-[-0.01em]">Never miss a meeting</h2>
          <p class="m-0 text-base leading-[1.65] text-muted-on-ink">
            Subscribe to the chapter calendar and events land straight in your own. We also announce everything in the members&rsquo; WhatsApp.
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            :href="googleCalUrl"
            class="rounded-full bg-white px-[26px] py-[13px] text-[0.95rem] font-bold text-ink no-underline transition-colors hover:bg-pink"
          >
            Google Calendar
          </a>
          <a
            :href="icsUrl"
            class="rounded-full border-2 border-[#57534e] bg-transparent px-6 py-[11px] text-[0.95rem] font-bold text-white no-underline transition-colors hover:border-white"
          >
            iCal / Outlook
          </a>
        </div>
      </div>
    </section>
  </div>
</template>
