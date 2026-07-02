<script setup lang="ts">
import {
  type ChapterEvent,
  categoryById,
  MONTH_SHORTS,
  parseISODate,
  WEEKDAYS,
} from "@/lib/events";

const props = defineProps<{
  events: ChapterEvent[]; // filtered to the visible month, date-sorted
  showCategoryColors: boolean;
}>();

const emit = defineEmits<{ select: [id: string] }>();

function color(ev: ChapterEvent): string {
  return props.showCategoryColors ? (categoryById(ev.cat).color ?? "#1C1917") : "#1C1917";
}
</script>

<template>
  <div class="event-list-view flex flex-col gap-3.5">
    <button
      v-for="ev in events"
      :key="ev.id"
      type="button"
      class="flex cursor-pointer items-stretch gap-5 rounded-[16px] bg-white px-5 py-4 text-left shadow-card transition-[box-shadow,transform] duration-100 hover:-translate-y-0.5 hover:shadow-card-hover"
      @click="emit('select', ev.id)"
    >
      <span
        class="flex w-[72px] flex-none flex-col items-center justify-center gap-0.5 rounded-[12px] px-1.5 py-2.5 text-white"
        :style="{ background: color(ev) }"
        aria-hidden="true"
      >
        <span class="font-display text-[0.7rem] font-bold uppercase tracking-[0.1em]">{{ WEEKDAYS[parseISODate(ev.date).getDay()] }}</span>
        <span class="font-display text-[1.7rem] font-extrabold leading-none">{{ parseISODate(ev.date).getDate() }}</span>
        <span class="font-display text-[0.7rem] font-bold uppercase tracking-[0.1em]">{{ MONTH_SHORTS[parseISODate(ev.date).getMonth()] }}</span>
      </span>
      <span class="flex min-w-0 flex-1 flex-col gap-1.5">
        <span
          class="self-start rounded-full border-2 px-3 py-0.5 font-display text-[0.7rem] font-bold uppercase tracking-[0.08em]"
          :style="{ borderColor: color(ev), color: color(ev) }"
        >
          {{ categoryById(ev.cat).label }}
        </span>
        <span class="font-display text-[1.15rem] font-bold leading-[1.3]">{{ ev.title }}</span>
        <span class="text-[0.92rem] font-semibold text-text-muted">{{ ev.time }} · {{ ev.location }}</span>
      </span>
      <span aria-hidden="true" class="flex-none self-center text-[1.2rem] font-bold text-red">→</span>
    </button>

    <div
      v-if="events.length === 0"
      class="rounded-[14px] border-2 border-dashed border-border-control p-10 text-center font-semibold text-text-muted"
    >
      No events in this category this month — try another month or clear the filter.
    </div>
  </div>
</template>
