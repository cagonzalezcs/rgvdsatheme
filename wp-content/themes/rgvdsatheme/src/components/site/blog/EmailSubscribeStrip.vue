<script setup lang="ts">
/* Form action is stubbed until Phase 6 wires a real subscribe endpoint. */
withDefaults(defineProps<{ rssUrl?: string }>(), { rssUrl: "/feed/" });
</script>

<template>
  <section id="subscribe" class="email-subscribe-strip bg-ink px-6 py-14 text-cream" data-tone="ink">
    <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-8">
      <div class="flex max-w-[52ch] flex-col gap-2">
        <h2 class="m-0 font-display text-[clamp(1.4rem,3vw,1.9rem)] font-black uppercase">Get new posts by email</h2>
        <p class="m-0 text-base leading-[1.6] text-muted-on-ink">One email when we publish. No spam, no lists sold — ever.</p>
      </div>
      <form class="flex flex-wrap items-stretch" @submit.prevent>
        <input
          type="email"
          placeholder="you@example.com"
          aria-label="Email address"
          class="min-w-[240px] border-[3px] border-cream bg-transparent px-[18px] py-[13px] text-base text-cream placeholder:text-cream/60"
        />
        <button
          type="submit"
          class="cursor-pointer border-[3px] border-cream bg-cream px-[26px] py-[13px] text-[0.95rem] font-extrabold uppercase tracking-[0.05em] text-ink hover:border-brand-red-deep hover:bg-brand-red-deep hover:text-white"
        >
          Subscribe
        </button>
        <a
          :href="rssUrl"
          class="flex items-center px-[18px] text-[0.9rem] font-extrabold text-cream underline underline-offset-4 hover:text-white"
        >
          RSS
        </a>
      </form>
    </div>
  </section>
</template>
