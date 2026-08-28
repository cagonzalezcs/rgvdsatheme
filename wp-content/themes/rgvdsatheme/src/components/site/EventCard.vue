<script setup lang="ts">
// Related-event card for the "More upcoming events" band. Carries its own
// permalink; date block + tag tint from the event's category term color.
import { computed } from "vue";
import {
  categoryById,
  MONTH_SHORTS,
  parseISODate,
  type RelatedEvent,
  WEEKDAYS,
} from "@/lib/events";

const props = defineProps<{ event: RelatedEvent }>();

const category = computed(() => categoryById(props.event.cat));
const date = computed(() => parseISODate(props.event.date));
</script>

<template>
  <a
    :href="event.url"
    class="flex items-stretch gap-[18px] rounded-[16px] bg-white p-5 text-ink no-underline shadow-card transition-[box-shadow,transform] duration-150 hover:-translate-y-0.5 hover:shadow-card-hover"
  >
    <div
      aria-hidden="true"
      class="flex flex-none flex-col items-center justify-center gap-px rounded-[12px] px-2.5 py-2 text-center text-white"
      :style="{ background: category.color ?? '#231F20' }"
    >
      <span class="font-display text-[0.66rem] font-bold uppercase tracking-[0.1em]">{{ WEEKDAYS[date.getDay()] }}</span>
      <span class="font-display text-[1.5rem] font-extrabold leading-none">{{ date.getDate() }}</span>
      <span class="font-display text-[0.66rem] font-bold uppercase tracking-[0.1em]">{{ MONTH_SHORTS[date.getMonth()] }}</span>
    </div>
    <div class="flex min-w-0 flex-col justify-center gap-[5px]">
      <span
        class="self-start rounded-full px-3 py-1 text-[0.72rem] font-bold uppercase tracking-[0.06em] text-white"
        :style="{ background: category.color ?? '#231F20' }"
      >
        {{ category.label }}
      </span>
      <span class="font-display text-[1.05rem] font-bold leading-[1.3]">{{ event.title }}</span>
      <span class="text-[0.88rem] font-semibold text-text-muted">{{ event.time }} · {{ event.location }}</span>
    </div>
  </a>
</template>
