<script setup lang="ts">
import { computed } from "vue";
import { postCategoryById } from "@/lib/posts";

const props = withDefaults(
  defineProps<{
    catId: string;
    href?: string;
    size?: "sm" | "md";
  }>(),
  { href: "", size: "md" },
);

const category = computed(() => postCategoryById(props.catId));
const sizeClass = computed(() =>
  props.size === "sm" ? "px-3 py-1 text-[0.72rem]" : "px-3.5 py-1.5 text-[0.8rem]",
);
</script>

<template>
  <component
    :is="href ? 'a' : 'span'"
    :href="href || undefined"
    class="category-tag inline-block self-start rounded-full bg-[var(--tag-bg)] font-bold uppercase tracking-[0.06em] text-white no-underline"
    :class="[sizeClass, href ? 'hover:opacity-90' : '']"
    :style="{ '--tag-bg': category.color ?? 'var(--color-ink)' }"
  >
    {{ category.label }}
  </component>
</template>
