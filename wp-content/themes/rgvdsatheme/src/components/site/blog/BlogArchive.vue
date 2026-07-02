<script setup lang="ts">
import { computed, ref, watch } from "vue";
import EmailSubscribeStrip from "@/components/site/blog/EmailSubscribeStrip.vue";
import FeaturedPostCard from "@/components/site/blog/FeaturedPostCard.vue";
import PostCard from "@/components/site/blog/PostCard.vue";
import PostResultRow from "@/components/site/blog/PostResultRow.vue";
import { type EventCategory, setCategories } from "@/lib/events";
import {
  type BlogPost,
  POST_CATEGORIES,
  postCategoryById,
  SAMPLE_POSTS,
} from "@/lib/posts";

const props = withDefaults(
  defineProps<{
    posts?: BlogPost[];
    /** WP term-driven categories — replaces the fixture palette when provided */
    categories?: EventCategory[];
    /** WP_Query paging — turns the static pager into real links when provided */
    pagination?: { newerUrl?: string; olderUrl?: string };
    showSubscribe?: boolean;
    /** Action Network newsletter form URL (from Chapter Settings) */
    newsletterUrl?: string;
  }>(),
  {
    posts: () => SAMPLE_POSTS,
    categories: undefined,
    pagination: undefined,
    showSubscribe: true,
    newsletterUrl: undefined,
  },
);

if (props.categories && props.categories.length > 0) setCategories(props.categories);

/* ---- state (search + filter survive reload via URL params: ?s= & ?category=) ---- */
const initialParams = new URLSearchParams(window.location.search);
const initialCat = initialParams.get("category");

const query = ref(initialParams.get("s") ?? "");
const activeCat = ref(
  POST_CATEGORIES.some((c) => c.id === initialCat && c.id !== "all")
    ? (initialCat as string)
    : "all",
);

watch([query, activeCat], () => {
  const params = new URLSearchParams(window.location.search);
  if (query.value.trim() === "") params.delete("s");
  else params.set("s", query.value.trim());
  if (activeCat.value === "all") params.delete("category");
  else params.set("category", activeCat.value);
  const qs = params.toString();
  history.replaceState(null, "", qs ? `${location.pathname}?${qs}` : location.pathname);
});

/* ---- browse vs filter/search layout ---- */
const isBrowsing = computed(() => activeCat.value === "all" && query.value.trim() === "");

/* WP already applied the URL's ?s= server-side (matching full body text);
 * re-matching those posts against title/excerpt here would drop body-only
 * hits. Only queries typed after load re-filter client-side — real
 * server-side fetch lands with the rest-data-layer change. */
const serverQuery =
  props.posts === SAMPLE_POSTS ? null : (initialParams.get("s") ?? "").trim().toLowerCase();

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase();
  return props.posts.filter((p) => {
    const okCat = activeCat.value === "all" || p.cat === activeCat.value;
    if (!okCat) return false;
    if (q === "" || q === serverQuery) return true;
    const hay = `${p.title} ${p.excerpt} ${postCategoryById(p.cat).label}`.toLowerCase();
    return hay.includes(q);
  });
});

const resultLine = computed(() => {
  const n = filtered.value.length;
  const catLabel = activeCat.value === "all" ? "All posts" : postCategoryById(activeCat.value).label;
  const q = query.value.trim();
  return `${n} ${n === 1 ? "post" : "posts"} · ${catLabel}${q ? ` · “${q}”` : ""}`;
});

function clearFilters() {
  query.value = "";
  activeCat.value = "all";
}

/* ---- browse-state data ---- */
const featuredPost = computed(() => props.posts.find((p) => p.featured) ?? props.posts[0]);
const GRID_SPANS = [3, 3, 2, 2, 2, 2, 2, 2];
const gridPosts = computed(() =>
  props.posts
    .filter((p) => p.id !== featuredPost.value?.id)
    .map((p, i) => ({ post: p, span: GRID_SPANS[i] ?? 2 })),
);
</script>

<template>
  <div class="blog-archive">
    <!-- Toolbar: search + category filter -->
    <section class="bg-white px-6 pt-10" data-tone="cream">
      <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-4">
        <div role="search" class="flex max-w-[460px] flex-[1_1_320px] items-stretch overflow-hidden rounded-full border border-border-control bg-white shadow-subtle">
          <input
            v-model="query"
            type="search"
            placeholder="Search posts…"
            aria-label="Search blog posts"
            class="min-w-0 flex-1 border-none bg-transparent px-5 py-3 text-base text-ink outline-offset-[-3px]"
          />
          <span
            aria-hidden="true"
            class="flex w-[52px] items-center justify-center bg-ink text-[1.1rem] font-bold text-white"
          >⌕</span>
        </div>
        <div aria-label="Filter by category" class="flex flex-wrap items-center gap-2">
          <span class="mr-1 text-[0.82rem] font-bold uppercase tracking-[0.06em] text-text-muted">Filter:</span>
          <button
            v-for="cat in POST_CATEGORIES"
            :key="cat.id"
            type="button"
            :aria-pressed="activeCat === cat.id"
            class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-3.5 py-2 text-[0.85rem] font-bold"
            :class="activeCat === cat.id ? 'border-ink bg-ink text-white' : 'border-border-control bg-white text-ink'"
            @click="activeCat = cat.id"
          >
            <span
              v-if="cat.color"
              aria-hidden="true"
              class="inline-block size-2.5 flex-none rounded-full"
              :style="{ background: cat.color }"
            ></span>
            {{ cat.label }}
          </button>
        </div>
      </div>
    </section>

    <!-- Browse state: featured + editorial grid -->
    <template v-if="isBrowsing">
      <section v-if="featuredPost" class="bg-white px-6 pt-8" data-tone="cream">
        <div class="mx-auto max-w-[1200px]">
          <FeaturedPostCard :post="featuredPost" />
        </div>
      </section>

      <section class="bg-white px-6 pb-12 pt-10" data-tone="cream">
        <div class="mx-auto flex max-w-[1200px] flex-col">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-6">
            <div
              v-for="{ post, span } in gridPosts"
              :key="post.id"
              class="flex"
              :class="span === 3 ? 'md:col-span-3' : 'md:col-span-2'"
            >
              <PostCard :post="post" :variant="span === 3 ? 'grid-lg' : 'grid'" />
            </div>
          </div>
          <!-- Pagination: real WP_Query links when provided, static fallback otherwise -->
          <div v-if="pagination" class="flex justify-center gap-3.5 pt-8">
            <a
              v-if="pagination.newerUrl"
              :href="pagination.newerUrl"
              class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
            >← Newer</a>
            <span
              v-else
              aria-disabled="true"
              class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
            >← Newer</span>
            <a
              v-if="pagination.olderUrl"
              :href="pagination.olderUrl"
              class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
            >Older posts →</a>
            <span
              v-else
              aria-disabled="true"
              class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
            >Older posts →</span>
          </div>
          <div v-else class="flex justify-center gap-3.5 pt-8">
            <span
              aria-disabled="true"
              class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
            >← Newer</span>
            <a
              href="#main"
              class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
            >Older posts →</a>
          </div>
        </div>
      </section>
    </template>

    <!-- Filter/search state: uniform result rows -->
    <section v-else class="bg-white px-6 pb-16 pt-8" data-tone="cream">
      <div class="mx-auto flex max-w-[920px] flex-col gap-[18px]">
        <div class="flex flex-wrap items-baseline justify-between gap-4 border-b-[3px] border-brand-red pb-3">
          <div role="status" class="font-display text-[1.15rem] font-bold">
            {{ resultLine }}
          </div>
          <button
            type="button"
            class="cursor-pointer border-none bg-transparent p-0 text-[0.9rem] font-bold text-red underline underline-offset-4 hover:text-red-hover"
            @click="clearFilters"
          >
            Clear filters ✕
          </button>
        </div>

        <div v-if="filtered.length > 0" class="flex flex-col gap-3.5">
          <PostResultRow v-for="post in filtered" :key="post.id" :post="post" />
        </div>

        <div
          v-else
          class="flex flex-col items-center gap-2.5 rounded-[16px] border-2 border-dashed border-border-control px-8 py-12 text-center"
        >
          <div class="font-display text-[1.2rem] font-bold">No posts match</div>
          <p class="m-0 max-w-[44ch] text-base leading-[1.6] text-text-muted">
            Try another word, or browse by category.
          </p>
        </div>
      </div>
    </section>

    <EmailSubscribeStrip v-if="showSubscribe" :newsletter-url="newsletterUrl" />
  </div>
</template>
