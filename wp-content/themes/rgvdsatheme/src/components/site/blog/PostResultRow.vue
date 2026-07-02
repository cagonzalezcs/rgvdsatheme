<script setup lang="ts">
import { computed } from "vue";
import { type BlogPost, postCategoryById } from "@/lib/posts";

const props = defineProps<{ post: BlogPost }>();

const category = computed(() => postCategoryById(props.post.cat));
</script>

<template>
  <a
    :href="post.url"
    data-blog-link
    class="post-result-row grid items-center gap-5 rounded-[16px] bg-white px-6 py-5 text-ink no-underline shadow-card transition-[box-shadow,transform] duration-150 [grid-template-columns:auto_1fr] hover:-translate-y-0.5 hover:shadow-card-hover md:[grid-template-columns:auto_1fr_auto]"
  >
    <span
      aria-hidden="true"
      class="h-12 w-3 rounded-full"
      :style="{ background: category.color ?? undefined }"
    ></span>
    <span class="flex min-w-0 flex-col gap-[5px]">
      <span
        class="text-[0.78rem] font-bold uppercase tracking-[0.06em]"
        :style="{ color: category.color ?? undefined }"
      >
        {{ category.label }} · {{ post.date }}
      </span>
      <span class="font-display text-[1.15rem] font-bold leading-[1.3]">{{ post.title }}</span>
      <span class="text-[0.95rem] leading-[1.55] text-text-muted">{{ post.excerpt }}</span>
    </span>
    <span aria-hidden="true" class="hidden text-[1.1rem] font-bold text-red md:block">→</span>
  </a>
</template>
