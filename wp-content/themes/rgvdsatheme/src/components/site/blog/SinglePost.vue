<script setup lang="ts">
import { computed, ref } from "vue";
import CategoryTag from "@/components/site/blog/CategoryTag.vue";
import ImageSlot from "@/components/site/blog/ImageSlot.vue";
import PostBlocks from "@/components/site/blog/PostBlocks.vue";
import PostCard from "@/components/site/blog/PostCard.vue";
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
    /** overrides the post's own byline_mode (per-post ACF select in Phase 6) */
    bylineMode?: "named" | "committee";
    showMetaRail?: boolean;
    blogUrl?: string;
    homeUrl?: string;
  }>(),
  {
    post: () => SAMPLE_SINGLE,
    posts: () => SAMPLE_POSTS,
    bylineMode: undefined,
    showMetaRail: false,
    blogUrl: "/blog/",
    homeUrl: "/",
  },
);

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
    <section class="bg-brand-red px-6 pb-[140px] pt-12 text-cream" data-tone="red">
      <div class="mx-auto flex max-w-[880px] flex-col gap-5">
        <nav aria-label="Breadcrumb">
          <ol class="m-0 flex list-none flex-wrap items-center gap-2.5 p-0 text-[0.85rem] font-bold uppercase tracking-[0.06em]">
            <li class="flex items-center gap-2.5">
              <a :href="homeUrl" class="text-cream/85 no-underline hover:text-cream hover:underline hover:underline-offset-4">Home</a>
              <span aria-hidden="true" class="opacity-60">/</span>
            </li>
            <li class="flex items-center gap-2.5">
              <a :href="blogUrl" class="text-cream/85 no-underline hover:text-cream hover:underline hover:underline-offset-4">Blog</a>
              <span aria-hidden="true" class="opacity-60">/</span>
            </li>
            <li aria-current="page">{{ post.title }}</li>
          </ol>
        </nav>
        <CategoryTag :cat-id="post.cat" :href="categoryUrl" />
        <h1 class="m-0 max-w-[24ch] font-display text-[clamp(2rem,4.6vw,3.3rem)] font-black uppercase leading-[1.1] [text-wrap:balance]">
          {{ post.title }}
        </h1>
        <p class="m-0 max-w-[58ch] text-[1.25rem] leading-[1.6]">{{ post.dek }}</p>
        <div class="flex flex-wrap items-center gap-3.5 text-[0.95rem] font-semibold">
          <div v-if="isNamed" class="flex items-center gap-3">
            <div class="size-[52px] flex-none overflow-hidden rounded-full border-2 border-ink bg-cream">
              <ImageSlot :src="post.authorAvatar" :alt="post.author" />
            </div>
            <span>By <strong>{{ post.author }}</strong> · {{ post.committee }}</span>
          </div>
          <span v-else>By the <strong>{{ post.committee }}</strong></span>
          <span aria-hidden="true" class="opacity-60">·</span>
          <span>{{ post.date }}</span>
          <span aria-hidden="true" class="opacity-60">·</span>
          <span>{{ post.readMinutes }} min read</span>
          <!-- i18n stub — affordance only until Spanish translations exist -->
          <a href="#main" lang="es" class="ml-1 font-extrabold text-cream underline underline-offset-4">Léelo en español →</a>
        </div>
      </div>
    </section>

    <!-- Article -->
    <section class="bg-cream px-6 pb-20" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] items-start gap-14">
        <article class="mx-auto flex min-w-0 max-w-[980px] flex-[1_1_auto] flex-col items-center gap-8">
          <!-- Featured image, pulled up over the red band -->
          <figure class="m-0 -mt-[100px] flex w-full flex-col">
            <div class="h-[clamp(280px,44vw,500px)] border-[3px] border-ink bg-cream shadow-[8px_8px_0_var(--color-ink)]">
              <ImageSlot :src="post.featuredImage.src" :alt="post.featuredImage.alt" label="Featured photo" />
            </div>
            <figcaption class="px-1 pt-3 text-[0.9rem] leading-[1.5] text-muted-on-cream">
              {{ post.featuredImage.caption }}
              <span v-if="post.featuredImage.credit" class="text-muted-2">{{ post.featuredImage.credit }}</span>
            </figcaption>
          </figure>

          <!-- post_blocks flexible-content stack -->
          <PostBlocks :blocks="post.blocks" :accent="accent" />

          <!-- End matter: tags + share -->
          <div class="mt-2 flex w-[min(74ch,100%)] flex-wrap items-center justify-between gap-5 border-t-[3px] border-ink pt-6">
            <div class="flex flex-wrap items-center gap-2.5">
              <CategoryTag :cat-id="post.cat" :href="categoryUrl" />
              <a
                v-for="tag in post.tags"
                :key="tag"
                :href="blogUrl"
                class="border-2 border-border-muted px-3.5 py-1.5 text-[0.8rem] font-bold uppercase tracking-[0.06em] text-ink no-underline hover:border-ink"
              >
                {{ tag }}
              </a>
            </div>
            <div class="flex items-center gap-2.5">
              <button
                type="button"
                class="cursor-pointer border-2 border-ink bg-white px-4 py-[9px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-ink hover:bg-brand-red-deep hover:text-white"
                @click="copyLink"
              >
                {{ copyLabel }}
              </button>
              <a
                :href="mailShareUrl"
                class="border-2 border-ink bg-white px-4 py-[9px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline hover:bg-brand-red-deep hover:text-white"
              >
                Email it
              </a>
            </div>
          </div>

          <!-- End matter: author card -->
          <div class="flex w-[min(74ch,100%)] flex-wrap items-center gap-[22px] border-[3px] border-ink bg-white px-[30px] py-[26px]">
            <div v-if="isNamed" class="size-[72px] flex-none overflow-hidden rounded-full border-2 border-ink bg-muted">
              <ImageSlot :src="post.authorAvatar" :alt="post.author" />
            </div>
            <div
              v-else
              aria-hidden="true"
              class="flex size-[72px] flex-none items-center justify-center border-2 border-ink font-display text-[1.3rem] font-black text-cream"
              :style="{ background: accent }"
            >
              {{ committeeInitials }}
            </div>
            <div class="flex flex-[1_1_300px] flex-col gap-1.5">
              <div class="text-[0.8rem] font-extrabold uppercase tracking-[0.1em] text-brand-red">About the author</div>
              <div class="text-[1.1rem] font-extrabold">{{ authorName }}</div>
              <p class="m-0 text-[0.95rem] leading-[1.6] text-muted-on-cream">{{ authorBio }}</p>
              <a
                href="/get-involved/#committees"
                class="mt-1 self-start text-[0.9rem] font-extrabold text-brand-red-deep no-underline hover:text-brand-red"
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
          <div class="flex flex-col gap-2.5 border-l-[3px] pl-4" :style="{ borderColor: accent }">
            <div class="font-display text-[0.85rem] font-extrabold uppercase tracking-[0.08em]">Posted in</div>
            <CategoryTag :cat-id="post.cat" :href="categoryUrl" size="sm" />
          </div>
          <div class="flex flex-col gap-2.5 border-l-[3px] border-ink pl-4">
            <div class="font-display text-[0.85rem] font-extrabold uppercase tracking-[0.08em]">Share</div>
            <button
              type="button"
              class="cursor-pointer border-none bg-transparent p-0 text-left text-[0.9rem] font-bold text-ink hover:text-brand-red"
              @click="copyLink"
            >
              {{ copyLabel }}
            </button>
            <a :href="mailShareUrl" class="text-[0.9rem] font-bold text-ink no-underline hover:text-brand-red">Email this post</a>
          </div>
          <div class="flex flex-col gap-2.5 bg-ink p-5 text-cream" data-tone="ink">
            <div class="font-display text-[0.9rem] font-extrabold uppercase tracking-[0.04em]">Get posts by email</div>
            <p class="m-0 text-[0.85rem] leading-[1.6] text-muted-on-ink">One email when we publish. Nothing else.</p>
            <a
              :href="`${blogUrl}#subscribe`"
              class="self-start bg-cream px-3.5 py-2 text-[0.8rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline hover:bg-brand-red-deep hover:text-white"
            >
              Subscribe
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- Read Next -->
    <section class="bg-cream px-6 pb-24" data-tone="cream">
      <div class="mx-auto flex max-w-[1140px] flex-col gap-7">
        <div class="flex flex-wrap items-baseline justify-between gap-4 border-b-[3px] border-ink pb-3.5">
          <h2 class="m-0 font-display text-[clamp(1.5rem,3.2vw,2.2rem)] font-black uppercase leading-[1.12]">Read next</h2>
          <a
            :href="blogUrl"
            class="text-base font-extrabold uppercase tracking-[0.05em] text-brand-red no-underline hover:underline hover:underline-offset-4"
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
