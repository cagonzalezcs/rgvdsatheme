<script setup lang="ts">
import { computed } from "vue";
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import { type BlogPost } from "@/lib/posts";

const props = withDefaults(
  defineProps<{
    post: BlogPost;
    /** grid-lg = span-3 card (bigger title + excerpt); compact = Read Next card */
    variant?: "grid-lg" | "grid" | "compact";
  }>(),
  { variant: "grid" },
);

const titleClass = computed(
  () =>
    ({
      "grid-lg": "text-[1.3rem]",
      grid: "text-[1.05rem]",
      compact: "text-[1.1rem]",
    })[props.variant],
);
</script>

<template>
  <a
    :href="post.url"
    data-blog-link
    class="post-card flex flex-1 flex-col overflow-hidden rounded-[16px] bg-white text-ink no-underline shadow-card transition-[box-shadow,transform] duration-150 hover:-translate-y-0.5 hover:shadow-card-hover"
  >
    <div class="flex aspect-video items-start" aria-hidden="true">
      <div class="relative size-full" data-post-image>
        <ImageSlot class="absolute inset-0" :src="post.image?.src" :alt="post.image?.alt" />
        <CategoryTag :cat-id="post.cat" size="sm" class="absolute left-3 top-3" />
      </div>
    </div>
    <div
      class="flex flex-col gap-2.5"
      :class="variant === 'compact' ? 'px-5 pb-[22px] pt-[18px]' : 'px-[22px] pb-6 pt-5'"
    >
      <span class="text-[0.8rem] font-semibold text-text-muted">{{ post.date }}</span>
      <span class="font-display font-bold leading-[1.3]" :class="titleClass">{{ post.title }}</span>
      <span v-if="variant === 'grid-lg'" class="text-[0.98rem] leading-[1.6] text-text-muted">{{ post.excerpt }}</span>
    </div>
  </a>
</template>
