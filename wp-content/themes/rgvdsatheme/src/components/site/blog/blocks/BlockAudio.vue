<script setup lang="ts">
import { computed, ref } from "vue";

const props = defineProps<{
  file: string | null;
  title: string;
  duration?: string;
  transcriptUrl: string;
  accent: string;
}>();

const audioEl = ref<HTMLAudioElement | null>(null);
const playing = ref(false);
const currentSec = ref(0);
const totalSec = ref(0);

function fmt(sec: number): string {
  const m = Math.floor(sec / 60);
  const s = Math.floor(sec % 60);
  return `${m}:${String(s).padStart(2, "0")}`;
}

function toggle() {
  const el = audioEl.value;
  if (!el) return;
  if (el.paused) void el.play();
  else el.pause();
}

/* No real file yet (fixture) → show the prototype's static player state. */
const progressPct = computed(() =>
  props.file ? (totalSec.value ? (currentSec.value / totalSec.value) * 100 : 0) : 30,
);
const currentLabel = computed(() => (props.file ? fmt(currentSec.value) : "0:58"));
const totalLabel = computed(() =>
  props.file && totalSec.value ? fmt(totalSec.value) : (props.duration ?? "–:––"),
);
</script>

<template>
  <div class="block-audio flex w-[min(74ch,100%)] flex-col gap-3.5 border-[3px] border-ink bg-white px-[26px] py-[22px]">
    <!-- Audio-only: WCAG wants a transcript (the "Read transcript" link below), not a video caption track. -->
    <!-- eslint-disable-next-line vuejs-accessibility/media-has-caption -->
    <audio
      v-if="file"
      ref="audioEl"
      :src="file"
      preload="metadata"
      @play="playing = true"
      @pause="playing = false"
      @timeupdate="currentSec = audioEl?.currentTime ?? 0"
      @loadedmetadata="totalSec = audioEl?.duration ?? 0"
      @ended="playing = false"
    ></audio>
    <div class="flex flex-wrap items-center gap-[18px]">
      <button
        type="button"
        :aria-label="`${playing ? 'Pause' : 'Play'} audio: ${title}`"
        class="size-14 flex-none cursor-pointer border-[3px] border-ink bg-brand-red text-[1.1rem] text-cream shadow-[4px_4px_0_var(--color-ink)] transition-[box-shadow,transform] duration-100 hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0_var(--color-ink)]"
        @click="toggle"
      >
        {{ playing ? "⏸" : "▶" }}
      </button>
      <div class="flex flex-[1_1_260px] flex-col gap-2.5">
        <div class="text-[1.05rem] font-extrabold">{{ title }}</div>
        <div aria-hidden="true" class="relative h-3 border-2 border-ink bg-muted">
          <div class="absolute inset-y-0 left-0" :style="{ width: `${progressPct}%`, background: accent }"></div>
        </div>
        <div class="flex justify-between font-mono text-[0.8rem] text-muted-on-cream">
          <span>{{ currentLabel }}</span><span>{{ totalLabel }}</span>
        </div>
      </div>
    </div>
    <a :href="transcriptUrl" class="self-start text-[0.9rem] font-bold text-brand-red-deep hover:text-brand-red">Read transcript</a>
  </div>
</template>
