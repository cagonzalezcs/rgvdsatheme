<script setup lang="ts">
import { computed } from "vue";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  type ChapterEvent,
  categoryById,
  MONTH_NAMES,
  parseISODate,
  WEEKDAYS,
} from "@/lib/events";

const props = defineProps<{
  event: ChapterEvent | null;
  showCategoryColors: boolean;
}>();

const emit = defineEmits<{ close: [] }>();

const color = computed(() =>
  props.event && props.showCategoryColors
    ? (categoryById(props.event.cat).color ?? "#1C1917")
    : "#1C1917",
);

const dateLine = computed(() => {
  if (!props.event) return "";
  const d = parseISODate(props.event.date);
  return `${WEEKDAYS[d.getDay()]}, ${MONTH_NAMES[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
});
</script>

<template>
  <Dialog :open="!!event" @update:open="(open) => !open && emit('close')">
    <DialogContent
      v-if="event"
      class="event-detail-dialog max-h-[85vh] gap-0 overflow-auto rounded-[18px] border-none p-0 shadow-[0_24px_60px_rgba(28,25,23,0.35)] sm:max-w-[520px]"
      :show-close-button="false"
      aria-label="Event details"
    >
      <div
        class="flex items-center justify-between gap-4 px-5 py-3.5 text-white"
        :style="{ background: color }"
      >
        <span class="font-display text-[0.78rem] font-bold uppercase tracking-[0.08em]">
          {{ categoryById(event.cat).label }}
        </span>
        <DialogClose
          class="flex size-8 cursor-pointer items-center justify-center rounded-full border-none bg-white/20 font-bold text-inherit hover:bg-[rgba(28,25,23,0.4)]"
          aria-label="Close"
        >
          ✕
        </DialogClose>
      </div>
      <div class="flex flex-col gap-4 px-7 pb-[30px] pt-[26px]">
        <DialogTitle class="m-0 font-display text-2xl font-extrabold normal-case leading-[1.25] tracking-[-0.01em]">
          {{ event.title }}
        </DialogTitle>
        <div class="flex flex-col gap-2 text-[0.98rem] font-semibold text-text-body">
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">📅</span><span>{{ dateLine }}</span></div>
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">🕐</span><span>{{ event.time }}</span></div>
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">📍</span><span>{{ event.location }}</span></div>
        </div>
        <DialogDescription class="m-0 text-base leading-[1.7] text-text-body">
          {{ event.desc }}
        </DialogDescription>
        <div class="mt-1.5 flex flex-wrap gap-2.5">
          <!-- Primary action navigates to the full Single Event page — the modal
               is an optional fast preview, not the RSVP endpoint (04 §3d). -->
          <a
            :href="event.url ?? '/calendar/'"
            class="rounded-full bg-red px-6 py-[11px] text-[0.92rem] font-bold text-white no-underline transition-colors hover:bg-red-hover"
          >
            View event
          </a>
          <a
            v-if="event.gcalUrl"
            :href="event.gcalUrl"
            class="rounded-full border-2 border-red px-[22px] py-[9px] text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
          >
            Add to calendar
          </a>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
