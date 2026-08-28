<script setup lang="ts">
// Event "Getting there" block — striped map placeholder + address line.
// (Real geocoded embed is a later phase; the server drops this block entirely
// for online-only events.)
defineProps<{ address: string; accent: string }>();
</script>

<template>
  <div class="flex w-full flex-col gap-3.5">
    <h2
      class="m-0 scroll-mt-[110px] border-b-[3px] pb-3 font-display text-[1.5rem] font-extrabold tracking-[-0.01em]"
      :style="{ borderColor: accent }"
    >
      Getting there
    </h2>
    <div
      role="img"
      :aria-label="`Map to ${address}`"
      class="flex h-[clamp(220px,30vw,320px)] w-full items-center justify-center overflow-hidden rounded-[16px] bg-[repeating-linear-gradient(45deg,var(--color-stripe-a)_0_16px,var(--color-stripe-b)_16px_32px)] shadow-gallery"
    >
      <span
        class="rounded-full bg-white px-4 py-2 font-mono text-[0.85rem] font-bold text-text-muted shadow-subtle"
      >
        Map · {{ address }}
      </span>
    </div>
    <p class="m-0 text-base leading-[1.7] text-text-body">{{ address }}</p>
  </div>
</template>
