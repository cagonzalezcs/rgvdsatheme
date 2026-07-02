<script setup lang="ts">
import { computed } from "vue";
import {
  type ChapterEvent,
  categoryById,
  MONTH_SHORTS,
  parseISODate,
  WEEKDAYS,
} from "@/lib/events";

const props = defineProps<{ event: ChapterEvent }>();

const date = computed(() => parseISODate(props.event.date));
const category = computed(() => categoryById(props.event.cat));
</script>

<template>
  <div
    class="block-event-embed grid w-[min(74ch,100%)] items-center gap-5 border-[3px] border-ink bg-white px-6 py-5 shadow-brutal-md [grid-template-columns:72px_1fr_auto]"
  >
    <div
      aria-hidden="true"
      class="flex flex-col items-center border-2 border-ink px-1 py-2 text-center text-cream"
      :style="{ background: category.color ?? undefined }"
    >
      <span class="font-display text-[0.7rem] font-extrabold tracking-[0.1em]">{{ WEEKDAYS[date.getDay()].toUpperCase() }}</span>
      <span class="font-display text-[1.4rem] font-black leading-[1.1]">{{ date.getDate() }}</span>
      <span class="font-display text-[0.7rem] font-extrabold tracking-[0.1em]">{{ MONTH_SHORTS[date.getMonth()].toUpperCase() }}</span>
    </div>
    <div class="flex min-w-0 flex-col gap-1">
      <span
        class="text-[0.75rem] font-extrabold uppercase tracking-[0.08em]"
        :style="{ color: category.color ?? undefined }"
      >
        Upcoming event · {{ category.label }}
      </span>
      <span class="text-[1.1rem] font-extrabold">{{ event.title }}</span>
      <span class="text-[0.9rem] text-muted-on-cream">{{ event.time }} · {{ event.location }}</span>
    </div>
    <a
      :href="event.rsvpUrl ?? '/calendar/'"
      class="whitespace-nowrap border-2 border-ink px-4 py-[9px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline hover:bg-brand-red-deep hover:text-white"
    >
      RSVP
    </a>
  </div>
</template>
