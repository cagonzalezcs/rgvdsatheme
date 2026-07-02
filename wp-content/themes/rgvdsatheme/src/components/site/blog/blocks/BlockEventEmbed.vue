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
    class="block-event-embed grid w-[min(74ch,100%)] items-center gap-5 rounded-[16px] bg-white px-6 py-5 shadow-gallery [grid-template-columns:72px_1fr_auto]"
  >
    <div
      aria-hidden="true"
      class="flex flex-col items-center rounded-[12px] px-1 py-2 text-center text-white"
      :style="{ background: category.color ?? undefined }"
    >
      <span class="font-display text-[0.7rem] font-bold tracking-[0.1em]">{{ WEEKDAYS[date.getDay()].toUpperCase() }}</span>
      <span class="font-display text-[1.4rem] font-extrabold leading-[1.1]">{{ date.getDate() }}</span>
      <span class="font-display text-[0.7rem] font-bold tracking-[0.1em]">{{ MONTH_SHORTS[date.getMonth()].toUpperCase() }}</span>
    </div>
    <div class="flex min-w-0 flex-col gap-1">
      <span
        class="text-[0.75rem] font-bold uppercase tracking-[0.06em]"
        :style="{ color: category.color ?? undefined }"
      >
        Upcoming event · {{ category.label }}
      </span>
      <span class="text-[1.1rem] font-bold">{{ event.title }}</span>
      <span class="text-[0.9rem] text-text-muted">{{ event.time }} · {{ event.location }}</span>
    </div>
    <a
      :href="event.rsvpUrl ?? '/calendar/'"
      class="whitespace-nowrap rounded-full border-2 border-red px-5 py-2 text-[0.9rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
    >
      RSVP
    </a>
  </div>
</template>
