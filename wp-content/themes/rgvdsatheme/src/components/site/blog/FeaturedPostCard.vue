<script setup lang="ts">
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import { type BlogPost } from "@/lib/posts";

defineProps<{ post: BlogPost }>();
</script>

<template>
  <article
    class="featured-post-card grid border-[3px] border-ink bg-white shadow-brutal-lg md:[grid-template-columns:minmax(300px,1.1fr)_minmax(300px,1fr)]"
  >
    <div class="min-h-[240px] border-b-[3px] border-ink md:min-h-[340px] md:border-b-0 md:border-r-[3px]">
      <ImageSlot label="Featured photo" />
    </div>
    <div class="flex flex-col justify-center gap-4 px-6 py-8 md:px-10 md:py-9">
      <div class="flex flex-wrap items-center gap-3">
        <span class="text-[0.8rem] font-extrabold uppercase tracking-[0.14em] text-brand-red">★ Featured</span>
        <CategoryTag :cat-id="post.cat" size="sm" :href="`?category=${post.cat}`" />
      </div>
      <a
        :href="post.url"
        class="font-display text-[clamp(1.5rem,2.8vw,2.1rem)] font-black uppercase leading-[1.15] text-ink no-underline [text-wrap:balance] hover:text-brand-red-deep"
      >
        {{ post.title }}
      </a>
      <p class="m-0 text-[1.05rem] leading-[1.65] text-muted-on-cream">{{ post.dek ?? post.excerpt }}</p>
      <div class="text-[0.9rem] font-semibold text-muted-on-cream">
        <template v-if="post.bylineMode === 'committee'">By the {{ post.committee }}</template>
        <template v-else>By {{ post.author }}</template>
        · {{ post.date }}<template v-if="post.readMinutes"> · {{ post.readMinutes }} min read</template>
      </div>
      <a
        :href="post.url"
        class="mt-1 self-start border-[3px] border-ink bg-cream px-[22px] py-[11px] text-[0.9rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline shadow-[4px_4px_0_var(--color-ink)] transition-[box-shadow,transform] duration-100 hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0_var(--color-ink)]"
      >
        Read the post
      </a>
    </div>
  </article>
</template>
