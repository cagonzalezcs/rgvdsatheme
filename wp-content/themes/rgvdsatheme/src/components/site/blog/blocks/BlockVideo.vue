<script setup lang="ts">
import { computed, ref } from "vue";

const props = defineProps<{
  url: string;
  poster?: string | null;
  caption?: string;
  transcriptUrl?: string;
}>();

const playing = ref(false);

/* YouTube / Vimeo oEmbed URLs → embeddable iframe src; anything else stays a placeholder. */
const embedUrl = computed(() => {
  const yt = props.url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{6,})/);
  if (yt) return `https://www.youtube-nocookie.com/embed/${yt[1]}?autoplay=1`;
  const vimeo = props.url.match(/vimeo\.com\/(\d+)/);
  if (vimeo) return `https://player.vimeo.com/video/${vimeo[1]}?autoplay=1`;
  return null;
});
</script>

<template>
  <figure class="block-video m-0 flex w-full flex-col">
    <iframe
      v-if="playing && embedUrl"
      :src="embedUrl"
      title="Video"
      class="aspect-video w-full border-[3px] border-ink shadow-[8px_8px_0_var(--color-ink)]"
      allow="autoplay; fullscreen; picture-in-picture"
      allowfullscreen
    ></iframe>
    <div
      v-else
      class="relative flex aspect-video items-center justify-center border-[3px] border-ink shadow-[8px_8px_0_var(--color-ink)]"
      :class="poster ? '' : 'bg-[repeating-linear-gradient(45deg,var(--color-stripe-a)_0_14px,var(--color-stripe-b)_14px_28px)]'"
    >
      <img v-if="poster" :src="poster" alt="" class="absolute inset-0 size-full object-cover" />
      <button
        type="button"
        aria-label="Play video"
        class="relative flex size-[84px] cursor-pointer items-center justify-center border-[3px] border-ink bg-brand-red text-[1.8rem] text-cream shadow-brutal transition-[box-shadow,transform] duration-100 hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-brutal-sm"
        @click="playing = true"
      >
        ▶
      </button>
      <span
        class="absolute bottom-3.5 right-3.5 border-2 border-ink bg-cream px-2 py-1 text-[0.75rem] font-extrabold tracking-[0.08em] text-ink"
      >CC</span>
    </div>
    <figcaption
      v-if="caption || transcriptUrl"
      class="flex flex-wrap justify-between gap-4 px-1 pt-3 text-[0.9rem] leading-[1.5] text-muted-on-cream"
    >
      <span>{{ caption }}</span>
      <a v-if="transcriptUrl" :href="transcriptUrl" class="font-bold text-brand-red-deep hover:text-brand-red">Read transcript</a>
    </figcaption>
  </figure>
</template>
