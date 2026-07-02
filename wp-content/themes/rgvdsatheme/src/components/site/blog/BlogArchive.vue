<script setup lang="ts">
import { useDebounceFn } from "@vueuse/core";
import { computed, onMounted, ref, watch } from "vue";
import EmailSubscribeStrip from "@/components/site/blog/EmailSubscribeStrip.vue";
import FeaturedPostCard from "@/components/site/blog/FeaturedPostCard.vue";
import PostCard from "@/components/site/blog/PostCard.vue";
import PostResultRow from "@/components/site/blog/PostResultRow.vue";
import { fetchPosts, isAbortError } from "@/lib/api";
import { type EventCategory, setCategories } from "@/lib/events";
import { POST_CATEGORIES, postCategoryById } from "@/lib/posts";
import type { BlogPost, PostsEnvelope } from "@/lib/schemas";

const props = withDefaults(
  defineProps<{
    /** server-rendered first browse page (no fetch, no flash) */
    initialPosts: BlogPost[];
    /** honest corpus size for the browse/empty state */
    initialTotal: number;
    /** rgvdsa/v1 base URL */
    apiBase: string;
    /** WP term-driven categories — replaces the registry palette */
    categories?: EventCategory[];
    /** server-paged archive URLs (crawl path; island intercepts clicks) */
    pagination?: { newerUrl?: string; olderUrl?: string };
    showSubscribe?: boolean;
    /** Action Network newsletter form URL (from Chapter Settings) */
    newsletterUrl?: string;
  }>(),
  {
    categories: undefined,
    pagination: undefined,
    showSubscribe: true,
    newsletterUrl: undefined,
  },
);

if (props.categories && props.categories.length > 0) setCategories(props.categories);

/* ---- state (search + filter + page survive reload via URL params) ---- */
const initialParams = new URLSearchParams(window.location.search);
const initialCat = initialParams.get("category");

const query = ref(initialParams.get("s") ?? "");
const activeCat = ref(
  POST_CATEGORIES.some((c) => c.id === initialCat && c.id !== "all")
    ? (initialCat as string)
    : "all",
);
const page = ref(Math.max(1, Number.parseInt(initialParams.get("paged") ?? "1", 10) || 1));

watch([query, activeCat, page], () => {
  const params = new URLSearchParams(window.location.search);
  if (query.value.trim() === "") params.delete("s");
  else params.set("s", query.value.trim());
  if (activeCat.value === "all") params.delete("category");
  else params.set("category", activeCat.value);
  if (page.value <= 1) params.delete("paged");
  else params.set("paged", String(page.value));
  const qs = params.toString();
  history.replaceState(null, "", qs ? `${location.pathname}?${qs}` : location.pathname);
});

/* ---- server fetch (island-data-fetch): debounced, abortable, honest ---- */
const isDefaultState = computed(
  () => query.value.trim() === "" && activeCat.value === "all" && page.value === 1,
);

const fetched = ref<PostsEnvelope | null>(null);
const loading = ref(false);
const failed = ref(false);
let controller: AbortController | null = null;

async function runFetch() {
  controller?.abort();
  const ctl = new AbortController();
  controller = ctl;
  loading.value = true;
  failed.value = false;
  try {
    const envelope = await fetchPosts(
      props.apiBase,
      { s: query.value, category: activeCat.value, page: page.value },
      ctl.signal,
    );
    if (ctl !== controller) return;
    fetched.value = envelope;
  } catch (err) {
    if (isAbortError(err) || ctl !== controller) return;
    failed.value = true;
  } finally {
    if (ctl === controller) loading.value = false;
  }
}

function syncState() {
  if (isDefaultState.value) {
    // Back to the embedded browse page — no fetch needed.
    controller?.abort();
    fetched.value = null;
    loading.value = false;
    failed.value = false;
    return;
  }
  void runFetch();
}

const debouncedSync = useDebounceFn(syncState, 300);

watch(query, () => {
  page.value = 1;
  debouncedSync();
});
watch(activeCat, () => {
  page.value = 1;
  syncState();
});
watch(page, syncState);

onMounted(() => {
  // A shared/reloaded URL with filters fetches that exact state instead of
  // trusting the embedded browse props.
  if (!isDefaultState.value) void runFetch();
});

/* ---- browse vs filter/search layout ---- */
const isBrowsing = computed(() => isDefaultState.value);

const results = computed(() => fetched.value?.posts ?? []);
const total = computed(() => fetched.value?.total ?? 0);
const totalPages = computed(() => fetched.value?.totalPages ?? 1);

const resultLine = computed(() => {
  const n = total.value;
  const catLabel = activeCat.value === "all" ? "All posts" : postCategoryById(activeCat.value).label;
  const q = query.value.trim();
  const pageSuffix = totalPages.value > 1 ? ` · page ${fetched.value?.page ?? page.value} of ${totalPages.value}` : "";
  return `${n} ${n === 1 ? "post" : "posts"} · ${catLabel}${q ? ` · “${q}”` : ""}${pageSuffix}`;
});

function clearFilters() {
  query.value = "";
  activeCat.value = "all";
  page.value = 1;
}

/** Real server-paged href (crawl/middle-click path); clicks stay on-island. */
function pagedUrl(n: number): string {
  const base = location.pathname.replace(/page\/\d+\/?$/, "");
  const params = new URLSearchParams(window.location.search);
  params.delete("paged");
  const qs = params.toString();
  return `${n <= 1 ? base : `${base}page/${n}/`}${qs ? `?${qs}` : ""}`;
}

/* ---- browse-state data (embedded first page) ---- */
const featuredPost = computed(
  () => props.initialPosts.find((p) => p.featured) ?? props.initialPosts[0],
);
const GRID_SPANS = [3, 3, 2, 2, 2, 2, 2, 2];
const gridPosts = computed(() =>
  props.initialPosts
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

    <!-- Browse state: featured + editorial grid (embedded, no fetch) -->
    <template v-if="isBrowsing">
      <template v-if="initialPosts.length > 0">
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
            <!-- Real server-paged links; the island intercepts and fetches -->
            <div v-if="pagination?.olderUrl || pagination?.newerUrl" class="flex justify-center gap-3.5 pt-8">
              <span
                aria-disabled="true"
                class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
              >← Newer</span>
              <a
                v-if="pagination?.olderUrl"
                :href="pagination.olderUrl"
                class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
                @click.prevent="page = 2"
              >Older posts →</a>
            </div>
          </div>
        </section>
      </template>

      <!-- Designed empty state (island-empty-states): no posts yet -->
      <section v-else class="bg-white px-6 pb-16 pt-10" data-tone="cream">
        <div class="mx-auto max-w-[920px]">
          <div class="flex flex-col items-center gap-2.5 rounded-[16px] border-2 border-dashed border-border-control px-8 py-16 text-center">
            <div class="font-display text-[1.3rem] font-bold">No posts yet</div>
            <p class="m-0 max-w-[48ch] text-base leading-[1.6] text-text-muted">
              The chapter blog is warming up. Check back soon — or subscribe below and we&rsquo;ll send the first post straight to you.
            </p>
          </div>
        </div>
      </section>
    </template>

    <!-- Filter/search/paged state: server-fetched result rows -->
    <section v-else class="bg-white px-6 pb-16 pt-8" data-tone="cream">
      <div class="mx-auto flex max-w-[920px] flex-col gap-[18px]">
        <div class="flex flex-wrap items-baseline justify-between gap-4 border-b-[3px] border-brand-red pb-3">
          <div role="status" aria-live="polite" class="font-display text-[1.15rem] font-bold">
            <template v-if="loading">Searching…</template>
            <template v-else-if="!failed">{{ resultLine }}</template>
          </div>
          <button
            type="button"
            class="cursor-pointer border-none bg-transparent p-0 text-[0.9rem] font-bold text-red underline underline-offset-4 hover:text-red-hover"
            @click="clearFilters"
          >
            Clear filters ✕
          </button>
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" aria-hidden="true" class="flex flex-col gap-3.5">
          <div v-for="n in 4" :key="n" class="h-[92px] animate-pulse rounded-[14px] bg-tint"></div>
        </div>

        <!-- Error state -->
        <div
          v-else-if="failed"
          class="flex flex-col items-center gap-2.5 rounded-[16px] border-2 border-dashed border-border-control px-8 py-12 text-center"
        >
          <div class="font-display text-[1.2rem] font-bold">Something went wrong</div>
          <p class="m-0 max-w-[44ch] text-base leading-[1.6] text-text-muted">
            We couldn&rsquo;t load posts just now. Give it another try.
          </p>
          <button
            type="button"
            class="mt-2 cursor-pointer rounded-full border-2 border-red bg-transparent px-6 py-2.5 text-[0.92rem] font-bold text-red transition-colors hover:border-red-hover hover:bg-wash"
            @click="runFetch"
          >
            Retry
          </button>
        </div>

        <template v-else>
          <div v-if="results.length > 0" class="flex flex-col gap-3.5">
            <PostResultRow v-for="post in results" :key="post.id" :post="post" />
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

          <!-- Envelope-driven pagination: real hrefs, island-handled clicks -->
          <div v-if="totalPages > 1" class="flex justify-center gap-3.5 pt-4">
            <a
              v-if="page > 1"
              :href="pagedUrl(page - 1)"
              class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
              @click.prevent="page = page - 1"
            >← Newer</a>
            <span
              v-else
              aria-disabled="true"
              class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
            >← Newer</span>
            <a
              v-if="page < totalPages"
              :href="pagedUrl(page + 1)"
              class="rounded-full border-2 border-red px-6 py-2.5 text-[0.92rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
              @click.prevent="page = page + 1"
            >Older posts →</a>
            <span
              v-else
              aria-disabled="true"
              class="rounded-full border-2 border-border-control px-6 py-2.5 text-[0.92rem] font-bold text-text-faint"
            >Older posts →</span>
          </div>
        </template>
      </div>
    </section>

    <EmailSubscribeStrip v-if="showSubscribe" :newsletter-url="newsletterUrl" />
  </div>
</template>
