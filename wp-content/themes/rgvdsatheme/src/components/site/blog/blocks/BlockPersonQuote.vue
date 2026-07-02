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
  <div class="block-person-quote flex w-[min(74ch,100%)] flex-wrap items-start gap-6 border-[3px] border-ink bg-white px-8 py-7">
    <div class="size-24 flex-none overflow-hidden rounded-full border-2 border-ink bg-muted">
      <ImageSlot :src="photo" :alt="alt" />
    </div>
    <div class="flex flex-[1_1_300px] flex-col gap-3">
      <blockquote :lang="lang" class="m-0 text-[1.25rem] font-semibold leading-[1.6]">{{ quote }}</blockquote>
      <div v-if="translation" class="text-base leading-[1.6] text-muted-on-cream">{{ translation }}</div>
      <div class="text-[0.85rem] font-extrabold uppercase tracking-[0.08em]">
        <span :style="{ color: accent }">{{ name }}</span><template v-if="role"> · {{ role }}</template>
      </div>
    </div>
  </div>
</template>
