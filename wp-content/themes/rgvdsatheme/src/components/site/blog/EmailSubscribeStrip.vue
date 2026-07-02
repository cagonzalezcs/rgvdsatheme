<script setup lang="ts">
import { ref } from "vue";

/* No custom endpoint: submit hands off to the chapter's Action Network form. */
const props = withDefaults(
  defineProps<{ rssUrl?: string; newsletterUrl?: string }>(),
  {
    rssUrl: "/feed/",
    newsletterUrl: "https://actionnetwork.org/forms/dsa-rgv-newsletter-sign-up",
  },
);

const email = ref("");

function onSubmit() {
  const target = new URL(props.newsletterUrl);
  if (email.value) target.searchParams.set("email", email.value);
  window.location.href = target.toString();
}
</script>

<template>
  <section id="subscribe" class="email-subscribe-strip bg-ink px-6 py-14 text-white" data-tone="ink">
    <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-8">
      <div class="flex max-w-[52ch] flex-col gap-2">
        <h2 class="m-0 font-display text-[clamp(1.4rem,3vw,1.9rem)] font-extrabold tracking-[-0.01em]">Get new posts by email</h2>
        <p class="m-0 text-base leading-[1.6] text-muted-on-ink">One email when we publish. No spam, no lists sold — ever.</p>
      </div>
      <form class="flex flex-wrap items-center gap-2.5" @submit.prevent="onSubmit">
        <input
          v-model="email"
          type="email"
          placeholder="you@example.com"
          aria-label="Email address"
          class="min-w-[240px] rounded-full border-2 border-[#57534e] bg-transparent px-[22px] py-[13px] text-base text-white placeholder:text-white/60"
        />
        <button
          type="submit"
          class="cursor-pointer rounded-full border-none bg-white px-7 py-[14px] text-[0.95rem] font-bold text-ink hover:bg-pink"
        >
          Subscribe
        </button>
        <a
          :href="rssUrl"
          class="flex items-center px-3 text-[0.95rem] font-bold text-white underline underline-offset-4 hover:text-pink"
        >
          RSS
        </a>
      </form>
    </div>
  </section>
</template>
