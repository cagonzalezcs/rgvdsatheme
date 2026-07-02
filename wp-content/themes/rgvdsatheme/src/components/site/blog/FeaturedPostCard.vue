<script setup lang="ts">
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import { type BlogPost } from "@/lib/posts";

defineProps<{ post: BlogPost }>();
</script>

<template>
  <article
    class="featured-post-card grid overflow-hidden rounded-[20px] bg-white shadow-featured md:[grid-template-columns:minmax(300px,1.1fr)_minmax(300px,1fr)]"
  >
    <div class="min-h-[240px] md:min-h-[340px]" data-post-image>
      <ImageSlot :src="post.image?.src" :alt="post.image?.alt" label="Featured photo" />
    </div>
    <div class="flex flex-col justify-center gap-4 px-6 py-8 md:px-10 md:py-9">
      <div class="flex flex-wrap items-center gap-3">
        <span class="text-[0.8rem] font-bold uppercase tracking-[0.14em] text-red">★ Featured</span>
        <CategoryTag :cat-id="post.cat" size="sm" :href="`?category=${post.cat}`" />
      </div>
      <a
        :href="post.url"
        data-blog-link
        class="font-display text-[clamp(1.5rem,2.8vw,2rem)] font-extrabold leading-[1.2] tracking-[-0.01em] text-ink no-underline [text-wrap:balance] hover:text-red"
      >
        {{ post.title }}
      </a>
      <p class="m-0 text-[1.05rem] leading-[1.65] text-text-muted">{{ post.dek ?? post.excerpt }}</p>
      <div class="text-[0.9rem] font-semibold text-text-muted">
        <template v-if="post.bylineMode === 'committee'">By the {{ post.committee }}</template>
        <template v-else>By {{ post.author }}</template>
        · {{ post.date }}<template v-if="post.readMinutes"> · {{ post.readMinutes }} min read</template>
      </div>
      <a
        :href="post.url"
        data-blog-link
        class="mt-1 self-start rounded-full bg-red px-[26px] py-[11px] text-[0.92rem] font-bold text-white no-underline transition-colors hover:bg-red-hover"
      >
        Read the post
      </a>
    </div>
  </article>
</template>
