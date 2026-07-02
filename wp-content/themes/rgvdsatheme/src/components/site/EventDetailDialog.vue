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
      class="event-detail-dialog max-h-[85vh] gap-0 overflow-auto p-0 sm:max-w-[520px]"
      :show-close-button="false"
      aria-label="Event details"
    >
      <div
        class="flex items-center justify-between gap-4 border-b-[3px] border-ink px-5 py-3.5 text-white"
        :style="{ background: color }"
      >
        <span class="font-display text-[0.78rem] font-extrabold uppercase tracking-[0.08em]">
          {{ categoryById(event.cat).label }}
        </span>
        <DialogClose
          class="cursor-pointer border-2 border-current bg-transparent px-2.5 py-0.5 font-extrabold text-inherit hover:border-ink hover:bg-ink hover:text-cream"
          aria-label="Close"
        >
          ✕
        </DialogClose>
      </div>
      <div class="flex flex-col gap-4 px-7 pb-[30px] pt-[26px]">
        <DialogTitle class="m-0 font-display text-2xl font-black uppercase leading-[1.2]">
          {{ event.title }}
        </DialogTitle>
        <div class="flex flex-col gap-2 text-[0.98rem] font-semibold">
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">📅</span><span>{{ dateLine }}</span></div>
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">🕐</span><span>{{ event.time }}</span></div>
          <div class="flex gap-2.5"><span aria-hidden="true" class="flex-none">📍</span><span>{{ event.location }}</span></div>
        </div>
        <DialogDescription class="m-0 text-base leading-[1.7] text-foreground">
          {{ event.desc }}
        </DialogDescription>
        <div class="mt-1.5 flex flex-wrap gap-2.5">
          <a
            :href="event.rsvpUrl ?? '#'"
            class="border-2 border-ink bg-brand-red px-5 py-[11px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-cream no-underline hover:bg-brand-red-deep"
          >
            RSVP
          </a>
          <a
            href="#"
            class="border-2 border-ink bg-transparent px-5 py-[11px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline hover:border-brand-red-deep hover:bg-brand-red-deep hover:text-white"
          >
            Add to calendar
          </a>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
