<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import EventDetailDialog from "@/components/site/EventDetailDialog.vue";
import EventListView from "@/components/site/EventListView.vue";
import MonthGrid from "@/components/site/MonthGrid.vue";
import { fetchEvents, isAbortError } from "@/lib/api";
import {
  type ChapterEvent,
  EVENT_CATEGORIES,
  MONTH_NAMES,
  parseISODate,
  setCategories,
} from "@/lib/events";

const props = withDefaults(
  defineProps<{
    /** rgvdsa/v1 base URL — the island fetches its window on mount */
    apiBase: string;
    /** Polylang language slug of the page; scopes the fetched events to it */
    lang?: string;
    defaultView?: "month" | "list";
    showCategoryColors?: boolean;
    showSubscribe?: boolean;
    googleCalUrl?: string;
    icsUrl?: string;
  }>(),
  {
    defaultView: "month",
    showCategoryColors: true,
    showSubscribe: true,
    googleCalUrl: "#",
    icsUrl: "#",
  },
);

/* ---- windowed fetch (island-data-fetch): skeleton until events land ---- */
const events = ref<ChapterEvent[]>([]);
const loading = ref(true);
const failed = ref(false);

async function loadEvents() {
  loading.value = true;
  failed.value = false;
  try {
    const envelope = await fetchEvents(props.apiBase, { lang: props.lang });
    events.value = envelope.events;
    if (envelope.categories.length > 0) setCategories(envelope.categories);
  } catch (err) {
    if (isAbortError(err)) return;
    failed.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(loadEvents);

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
  events.value.filter((e) => activeCat.value === "all" || e.cat === activeCat.value),
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
  () => events.value.find((e) => e.id === selectedId.value) ?? null,
);
</script>

<template>
  <div class="event-calendar">
    <section class="bg-white px-4 pt-10 md:px-6" data-tone="cream">
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

          <!-- Month/List toggle is md+ only; mobile is agenda-list-only (05 §4 Calendar) -->
          <div role="group" aria-label="View" class="hidden gap-1 rounded-full bg-off-white p-1 md:flex">
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

    <!-- Skeleton while the window loads -->
    <section v-if="loading" class="bg-white px-4 pb-12 pt-6 md:px-6" data-tone="cream">
      <div aria-hidden="true" class="mx-auto max-w-[1200px]">
        <div class="h-11 animate-pulse rounded-t-[12px] bg-ink/10"></div>
        <!-- mobile: stacked agenda rows -->
        <div class="flex flex-col gap-2 pt-2 md:hidden">
          <div v-for="n in 5" :key="`m${n}`" class="h-16 animate-pulse rounded-[12px] bg-tint"></div>
        </div>
        <!-- md+: month grid -->
        <div class="hidden grid-cols-7 gap-px pt-px md:grid">
          <div v-for="n in 35" :key="`g${n}`" class="h-20 animate-pulse bg-tint"></div>
        </div>
      </div>
      <p role="status" class="mx-auto mt-3.5 max-w-[1200px] text-[0.9rem] text-text-muted">Loading events…</p>
    </section>

    <!-- Error state: the calendar feed keeps working even when the API doesn't -->
    <section v-else-if="failed" class="bg-white px-4 pb-12 pt-6 md:px-6" data-tone="cream">
      <div class="mx-auto max-w-[900px]">
        <div class="flex flex-col items-center gap-2.5 rounded-[16px] border-2 border-dashed border-border-control px-8 py-14 text-center">
          <div class="font-display text-[1.25rem] font-bold">We couldn&rsquo;t load the calendar</div>
          <p class="m-0 max-w-[48ch] text-base leading-[1.6] text-text-muted">
            Try again in a moment — or subscribe with
            <a :href="icsUrl" class="font-bold text-red">iCal / Outlook</a>
            and get every event straight in your own calendar.
          </p>
          <button
            type="button"
            class="mt-2 cursor-pointer rounded-full border-2 border-red bg-transparent px-6 py-2.5 text-[0.92rem] font-bold text-red transition-colors hover:border-red-hover hover:bg-wash"
            @click="loadEvents"
          >
            Retry
          </button>
        </div>
      </div>
    </section>

    <!-- Designed empty state (island-empty-states) -->
    <section v-else-if="events.length === 0" class="bg-white px-4 pb-12 pt-6 md:px-6" data-tone="cream">
      <div class="mx-auto max-w-[900px]">
        <div class="flex flex-col items-center gap-2.5 rounded-[16px] border-2 border-dashed border-border-control px-8 py-14 text-center">
          <div class="font-display text-[1.25rem] font-bold">No events scheduled</div>
          <p class="m-0 max-w-[48ch] text-base leading-[1.6] text-text-muted">
            New meetings and actions land here first. Subscribe below and never miss one.
          </p>
        </div>
      </div>
    </section>

    <section v-else class="bg-white px-4 pb-12 pt-6 md:px-6" data-tone="cream">
      <!-- Mobile (<md): agenda list only — the month grid doesn't survive 320px (05 §4) -->
      <div class="mx-auto max-w-[900px] md:hidden">
        <EventListView
          :events="monthEvents"
          :show-category-colors="showCategoryColors"
          @select="selectedId = $event"
        />
      </div>

      <!-- Tablet+ (md): month grid or list per the toggle -->
      <div v-if="view === 'month'" class="mx-auto hidden max-w-[1200px] md:block">
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
      <div v-else class="mx-auto hidden max-w-[900px] md:block">
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

    <section v-if="showSubscribe" class="bg-ink px-4 py-14 text-white md:px-6" data-tone="ink">
      <div class="mx-auto flex max-w-[1200px] flex-col gap-6 md:flex-row md:flex-wrap md:items-center md:justify-between md:gap-8">
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
