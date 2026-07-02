<script setup lang="ts">
import ImageSlot from "@/components/site/blog/ImageSlot.vue";

defineProps<{
  photo: string | null;
  alt: string;
  quote: string;
  translation?: string;
  name: string;
  role?: string;
  lang: "en" | "es";
  accent: string;
}>();
</script>

<template>
  <div class="block-person-quote flex w-[min(74ch,100%)] flex-wrap items-start gap-6 rounded-[18px] bg-white px-8 py-7 shadow-media">
    <div class="size-24 flex-none overflow-hidden rounded-full bg-off-white shadow-subtle">
      <ImageSlot :src="photo" :alt="alt" />
    </div>
    <div class="flex flex-[1_1_300px] flex-col gap-3">
      <blockquote :lang="lang" class="m-0 text-[1.25rem] font-semibold leading-[1.6]">{{ quote }}</blockquote>
      <div v-if="translation" class="text-base leading-[1.6] text-text-muted">{{ translation }}</div>
      <div class="text-[0.85rem] font-bold uppercase tracking-[0.06em]">
        <span :style="{ color: accent }">{{ name }}</span><template v-if="role"><span class="text-text-muted"> · {{ role }}</span></template>
      </div>
    </div>
  </div>
</template>
