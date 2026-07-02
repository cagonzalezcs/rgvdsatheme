<script setup lang="ts">
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import { type PostImage } from "@/lib/posts";

defineProps<{
  image: PostImage;
  /** term color of the post's category */
  accent: string;
}>();
</script>

<template>
  <figure class="block-image m-0 flex w-full flex-col">
    <div
      class="h-[clamp(240px,38vw,440px)] border-[3px] border-ink bg-cream"
      :style="{ boxShadow: `8px 8px 0 ${accent}` }"
    >
      <ImageSlot :src="image.src" :alt="image.alt" label="Photo" />
    </div>
    <figcaption v-if="image.caption || image.credit" class="px-1 pt-3 text-[0.9rem] leading-[1.5] text-muted-on-cream">
      {{ image.caption }}
      <span v-if="image.credit" class="text-muted-2">{{ image.credit }}</span>
    </figcaption>
  </figure>
</template>
