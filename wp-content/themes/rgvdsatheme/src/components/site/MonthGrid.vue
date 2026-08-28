<script setup lang="ts">
import { computed } from "vue";
import {
  type ChapterEvent,
  categoryById,
  WEEKDAYS,
} from "@/lib/events";

const props = defineProps<{
  year: number;
  month: number; // 0-based
  events: ChapterEvent[]; // already category-filtered
  showCategoryColors: boolean;
}>();

const emit = defineEmits<{ select: [id: string] }>();

interface DayCell {
  key: string;
  num: number;
  inMonth: boolean;
  isToday: boolean;
  events: ChapterEvent[];
}

const cells = computed<DayCell[]>(() => {
  const byDate: Record<string, ChapterEvent[]> = {};
  for (const ev of props.events) (byDate[ev.date] ??= []).push(ev);

  const firstDow = new Date(props.year, props.month, 1).getDay();
  const daysInMonth = new Date(props.year, props.month + 1, 0).getDate();
  const total = Math.ceil((firstDow + daysInMonth) / 7) * 7;
  const today = new Date();

  const out: DayCell[] = [];
  for (let i = 0; i < total; i++) {
    const d = new Date(props.year, props.month, i - firstDow + 1);
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    out.push({
      key,
      num: d.getDate(),
      inMonth: d.getMonth() === props.month,
      isToday:
        d.getFullYear() === today.getFullYear() &&
        d.getMonth() === today.getMonth() &&
        d.getDate() === today.getDate(),
      events: byDate[key] ?? [],
    });
  }
  return out;
});

function chipColor(ev: ChapterEvent): string {
  return props.showCategoryColors ? (categoryById(ev.cat).color ?? "#231F20") : "#231F20";
}
</script>

<template>
  <div class="month-grid overflow-hidden rounded-[16px] bg-hairline shadow-[0_4px_18px_rgba(28,25,23,0.12)]">
    <div class="grid grid-cols-7 gap-px bg-ink">
      <div
        v-for="wd in WEEKDAYS"
        :key="wd"
        class="bg-ink px-3 py-3 font-display text-[0.8rem] font-bold uppercase tracking-[0.08em] text-white"
      >
        {{ wd }}
      </div>
    </div>
    <div class="grid grid-cols-7 gap-px bg-hairline">
      <div
        v-for="day in cells"
        :key="day.key"
        class="flex min-h-[86px] min-w-0 flex-col gap-1.5 p-1.5 lg:min-h-[112px] lg:p-2"
        :class="day.inMonth ? 'bg-white' : 'bg-[#f5f3ef]'"
      >
        <div class="flex justify-end">
          <span
            v-if="day.isToday"
            class="rounded-full bg-brand-red px-[9px] py-0.5 font-display text-[0.85rem] font-bold text-white"
          >
            {{ day.num }}
          </span>
          <span
            v-else
            class="font-display text-[0.85rem] font-bold"
            :class="day.inMonth ? 'text-ink' : 'text-text-faint'"
          >
            {{ day.num }}
          </span>
        </div>
        <div class="flex flex-col gap-1">
          <button
            v-for="ev in day.events"
            :key="ev.id"
            type="button"
            :title="`${ev.title} — ${ev.time}`"
            :style="{ background: chipColor(ev) }"
            class="block w-full cursor-pointer truncate rounded-[6px] border-none px-[7px] py-1 text-left text-[0.68rem] font-bold leading-[1.25] text-white hover:outline-2 hover:outline-offset-1 hover:outline-ink lg:text-[0.74rem]"
            @click="emit('select', ev.id)"
          >
            {{ ev.title }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
