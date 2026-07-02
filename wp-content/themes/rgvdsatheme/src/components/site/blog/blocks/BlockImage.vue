<script setup lang="ts">
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import { type PostImage } from "@/lib/posts";

withDefaults(
  defineProps<{
    image: PostImage;
    /** widen past the article measure (980px → the 1140px wrapper) at lg+ */
    breakout?: boolean;
    /** term color of the post's category */
    accent: string;
  }>(),
  { breakout: false },
);
</script>

<template>
  <!-- breakout: negative margins push 80px past the article on each side at
       lg+ (980px article → 1140px wrapper), capped so it never overflows the
       viewport's 24px page gutters. The flex parent keeps it centered. -->
  <figure
    class="block-image m-0 flex w-full flex-col"
    :class="
      breakout
        ? 'lg:-mx-20 lg:w-[calc(100%+10rem)] lg:max-w-[calc(100vw-3rem)]'
        : ''
    "
  >
    <div class="h-[clamp(240px,38vw,440px)] overflow-hidden rounded-[18px] bg-white shadow-media">
      <ImageSlot :src="image.src" :alt="image.alt" label="Photo" />
    </div>
    <figcaption v-if="image.caption || image.credit" class="px-1 pt-3 text-[0.9rem] leading-[1.5] text-text-muted">
      {{ image.caption }}
      <span v-if="image.credit" class="text-text-faint">{{ image.credit }}</span>
    </figcaption>
  </figure>
</template>
