<script setup lang="ts">
import { computed, ref } from "vue";
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import PostBlocks from "@/components/site/blog/PostBlocks.vue";
import PostCard from "@/components/site/blog/PostCard.vue";
import { type EventCategory, setCategories } from "@/lib/events";
import {
  type BlogPost,
  postCategoryById,
  SAMPLE_POSTS,
  SAMPLE_SINGLE,
  type SinglePostData,
} from "@/lib/posts";

const props = withDefaults(
  defineProps<{
    post?: SinglePostData;
    /** pool the Read Next query draws from (same category, latest 3) */
    posts?: BlogPost[];
    /** WP term-driven categories — replaces the fixture palette when provided */
    categories?: EventCategory[];
    /** overrides the post's own byline_mode (per-post ACF select in Phase 6) */
    bylineMode?: "named" | "committee";
    showMetaRail?: boolean;
    blogUrl?: string;
    homeUrl?: string;
  }>(),
  {
    post: () => SAMPLE_SINGLE,
    posts: () => SAMPLE_POSTS,
    categories: undefined,
    bylineMode: undefined,
    showMetaRail: false,
    blogUrl: "/blog/",
    homeUrl: "/",
  },
);

if (props.categories && props.categories.length > 0) setCategories(props.categories);

const mode = computed(() => props.bylineMode ?? props.post.bylineMode);
const isNamed = computed(() => mode.value !== "committee");
const category = computed(() => postCategoryById(props.post.cat));
const accent = computed(() => category.value.color ?? "#1C1917");
const categoryUrl = computed(() => `${props.blogUrl}?category=${props.post.cat}`);

const authorName = computed(() =>
  isNamed.value ? props.post.author : `The ${props.post.committee}`,
);
const authorBio = computed(() =>
  isNamed.value ? props.post.authorBio : props.post.committeeBio,
);
const committeeInitials = computed(() =>
  props.post.committee
    .split(" ")
    .filter((w) => w !== "Committee")
    .map((w) => w[0])
    .slice(0, 2)
    .join(""),
);

/* Read Next: same category, latest 3, excluding current — padded with other
 * recent posts when the category has fewer than 3. */
const readNext = computed(() => {
  const rest = props.posts.filter((p) => !p.featured);
  const sameCat = rest.filter((p) => p.cat === props.post.cat);
  const others = rest.filter((p) => p.cat !== props.post.cat);
  return [...sameCat, ...others].slice(0, 3);
});

/* Copy link w/ "Copied ✓" state */
const copied = ref(false);
let copyTimer: ReturnType<typeof setTimeout> | undefined;
function copyLink() {
  void navigator.clipboard?.writeText(location.href).catch(() => {});
  copied.value = true;
  clearTimeout(copyTimer);
  copyTimer = setTimeout(() => (copied.value = false), 2000);
}
const copyLabel = computed(() => (copied.value ? "Copied ✓" : "Copy link"));
const mailShareUrl = computed(
  () => `mailto:?subject=${encodeURIComponent(`${props.post.title} — RGV DSA`)}`,
);
</script>

<template>
  <div class="single-post">
    <!-- Hero (red band) -->
    <section class="bg-brand-red px-6 pb-[140px] pt-12 text-white" data-tone="red">
      <div class="mx-auto flex max-w-[880px] flex-col items-start gap-5">
        <nav aria-label="Breadcrumb">
          <ol class="m-0 flex list-none flex-wrap items-center gap-2 rounded-full bg-white px-4 py-1.5 text-[0.85rem] font-bold">
            <li class="flex items-center gap-2">
              <a :href="homeUrl" class="text-red no-underline hover:underline hover:underline-offset-[3px]">Home</a>
              <span aria-hidden="true" class="text-text-muted">/</span>
            </li>
            <li class="flex items-center gap-2">
              <a :href="blogUrl" class="text-red no-underline hover:underline hover:underline-offset-[3px]">Blog</a>
              <span aria-hidden="true" class="text-text-muted">/</span>
            </li>
            <li aria-current="page" class="text-ink">{{ post.title }}</li>
          </ol>
        </nav>
        <CategoryTag :cat-id="post.cat" :href="categoryUrl" />
        <h1 class="m-0 max-w-[24ch] font-display text-[clamp(2rem,4.6vw,3.3rem)] font-black leading-[1.12] tracking-[-0.01em] [text-wrap:balance]">
          {{ post.title }}
        </h1>
        <p class="m-0 max-w-[48ch] text-[1.5rem] leading-[1.5]">{{ post.dek }}</p>
        <div class="flex flex-wrap items-center gap-3 text-[0.92rem] font-semibold">
          <div v-if="isNamed" class="flex items-center gap-2.5 rounded-full bg-white py-1.5 pl-1.5 pr-[18px] text-ink">
            <div class="size-10 flex-none overflow-hidden rounded-full bg-off-white">
              <ImageSlot :src="post.authorAvatar" :alt="post.author" />
            </div>
            <span>By <strong>{{ post.author }}</strong> · {{ post.committee }}</span>
          </div>
          <span v-else class="rounded-full bg-white px-[18px] py-2.5 text-ink">By the <strong>{{ post.committee }}</strong></span>
          <span class="rounded-full bg-[rgba(28,25,23,0.85)] px-[18px] py-2.5">{{ post.date }} · {{ post.readMinutes }} min read</span>
          <!-- i18n stub — affordance only until Spanish translations exist -->
          <a href="#main" lang="es" class="rounded-full bg-[rgba(28,25,23,0.85)] px-[18px] py-2.5 font-bold text-white underline underline-offset-[3px] hover:bg-ink">Léelo en español →</a>
        </div>
      </div>
    </section>

    <!-- Article -->
    <section class="bg-white px-6 pb-20" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] items-start gap-14">
        <article class="mx-auto flex min-w-0 max-w-[980px] flex-[1_1_auto] flex-col items-center gap-8">
          <!-- Featured image, pulled up over the red band -->
          <figure class="m-0 -mt-[100px] flex w-full flex-col">
            <div class="h-[clamp(280px,44vw,500px)] overflow-hidden rounded-[18px] bg-white shadow-[0_16px_44px_rgba(28,25,23,0.24)]">
              <ImageSlot :src="post.featuredImage.src" :alt="post.featuredImage.alt" label="Featured photo" />
            </div>
            <figcaption class="px-1 pt-3 text-[0.9rem] leading-[1.5] text-text-muted">
              {{ post.featuredImage.caption }}
              <span v-if="post.featuredImage.credit" class="text-text-faint">{{ post.featuredImage.credit }}</span>
            </figcaption>
          </figure>

          <!-- post_blocks flexible-content stack -->
          <PostBlocks :blocks="post.blocks" :accent="accent" />

          <!-- End matter: tags + share -->
          <div class="mt-2 flex w-[min(74ch,100%)] flex-wrap items-center justify-between gap-5 border-t-[3px] border-brand-red pt-6">
            <div class="flex flex-wrap items-center gap-2.5">
              <CategoryTag :cat-id="post.cat" :href="categoryUrl" />
              <a
                v-for="tag in post.tags"
                :key="tag"
                :href="blogUrl"
                class="rounded-full border border-border-control px-4 py-1.5 text-[0.8rem] font-bold uppercase tracking-[0.04em] text-ink no-underline hover:border-ink"
              >
                {{ tag }}
              </a>
            </div>
            <div class="flex items-center gap-2.5">
              <button
                type="button"
                class="cursor-pointer rounded-full border-2 border-red bg-transparent px-[18px] py-2 text-[0.9rem] font-bold text-red transition-colors hover:border-red-hover hover:bg-wash"
                @click="copyLink"
              >
                {{ copyLabel }}
              </button>
              <a
                :href="mailShareUrl"
                class="rounded-full border-2 border-red bg-transparent px-[18px] py-2 text-[0.9rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
              >
                Email it
              </a>
            </div>
          </div>

          <!-- End matter: author card -->
          <div class="flex w-[min(74ch,100%)] flex-wrap items-center gap-[22px] rounded-[18px] bg-off-white px-[30px] py-[26px]">
            <div v-if="isNamed" class="size-[72px] flex-none overflow-hidden rounded-full bg-white shadow-subtle">
              <ImageSlot :src="post.authorAvatar" :alt="post.author" />
            </div>
            <div
              v-else
              aria-hidden="true"
              class="flex size-[72px] flex-none items-center justify-center rounded-full font-display text-[1.3rem] font-extrabold text-white"
              :style="{ background: accent }"
            >
              {{ committeeInitials }}
            </div>
            <div class="flex flex-[1_1_300px] flex-col gap-1.5">
              <div class="text-[0.8rem] font-bold uppercase tracking-[0.1em] text-red">About the author</div>
              <div class="text-[1.1rem] font-bold">{{ authorName }}</div>
              <p class="m-0 text-[0.95rem] leading-[1.6] text-text-muted">{{ authorBio }}</p>
              <a
                href="/get-involved/#committees"
                class="mt-1 self-start text-[0.9rem] font-bold text-red no-underline hover:underline hover:underline-offset-[3px]"
              >
                More about the {{ post.committee }} →
              </a>
            </div>
          </div>
        </article>

        <!-- Optional sticky meta rail -->
        <aside
          v-if="showMetaRail"
          aria-label="Post details"
          class="sticky top-[calc(100px+var(--wp-admin--admin-bar--height,0px))] mt-10 flex w-[280px] flex-none flex-col gap-6 max-lg:hidden"
        >
          <div class="flex flex-col gap-2.5 rounded-[14px] bg-off-white px-[18px] py-4">
            <div class="font-display text-[0.82rem] font-bold uppercase tracking-[0.08em] text-text-muted">Posted in</div>
            <CategoryTag :cat-id="post.cat" :href="categoryUrl" size="sm" />
          </div>
          <div class="flex flex-col gap-2.5 rounded-[14px] bg-off-white px-[18px] py-4">
            <div class="font-display text-[0.82rem] font-bold uppercase tracking-[0.08em] text-text-muted">Share</div>
            <button
              type="button"
              class="cursor-pointer border-none bg-transparent p-0 text-left text-[0.9rem] font-bold text-red hover:underline hover:underline-offset-[3px]"
              @click="copyLink"
            >
              {{ copyLabel }}
            </button>
            <a :href="mailShareUrl" class="text-[0.9rem] font-bold text-red no-underline hover:underline hover:underline-offset-[3px]">Email this post</a>
          </div>
          <div class="flex flex-col gap-2.5 rounded-[16px] bg-ink p-5 text-white" data-tone="ink">
            <div class="font-display text-[0.9rem] font-bold">Get posts by email</div>
            <p class="m-0 text-[0.85rem] leading-[1.6] text-muted-on-ink">One email when we publish. Nothing else.</p>
            <a
              :href="`${blogUrl}#subscribe`"
              class="self-start rounded-full bg-white px-4 py-2 text-[0.82rem] font-bold text-ink no-underline hover:bg-pink"
            >
              Subscribe
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- Read Next -->
    <section class="bg-off-white px-6 pb-24 pt-16" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] flex-col gap-7">
        <div class="flex flex-wrap items-baseline justify-between gap-4">
          <h2 class="m-0 font-display text-[clamp(1.5rem,3.2vw,2.2rem)] font-extrabold leading-[1.15] tracking-[-0.01em]">Read next</h2>
          <a
            :href="blogUrl"
            class="text-base font-bold text-red no-underline hover:underline hover:underline-offset-4"
          >
            All posts →
          </a>
        </div>
        <div class="grid gap-6 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
          <PostCard v-for="p in readNext" :key="p.id" :post="p" variant="compact" />
        </div>
      </div>
    </section>
  </div>
</template>
