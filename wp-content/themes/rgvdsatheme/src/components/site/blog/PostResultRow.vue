<script setup lang="ts">
import { computed } from "vue";
import { type BlogPost, postCategoryById } from "@/lib/posts";

const props = defineProps<{ post: BlogPost }>();

const category = computed(() => postCategoryById(props.post.cat));
</script>

<template>
  <a
    :href="post.url"
    class="post-result-row grid items-center gap-5 border-[3px] border-ink bg-white px-6 py-5 text-ink no-underline transition-[box-shadow,transform] duration-100 [grid-template-columns:auto_1fr_auto] hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-brutal-md"
  >
    <span
      aria-hidden="true"
      class="h-11 w-3.5 border-2 border-ink"
      :style="{ background: category.color ?? undefined }"
    ></span>
    <span class="flex min-w-0 flex-col gap-[5px]">
      <span
        class="text-[0.78rem] font-extrabold uppercase tracking-[0.08em]"
        :style="{ color: category.color ?? undefined }"
      >
        {{ category.label }} · {{ post.date }}
      </span>
      <span class="font-display text-[1.15rem] font-extrabold leading-[1.3]">{{ post.title }}</span>
      <span class="text-[0.95rem] leading-[1.55] text-muted-on-cream">{{ post.excerpt }}</span>
    </span>
    <span aria-hidden="true" class="text-[1.1rem] font-extrabold text-ink">→</span>
  </a>
</template>
