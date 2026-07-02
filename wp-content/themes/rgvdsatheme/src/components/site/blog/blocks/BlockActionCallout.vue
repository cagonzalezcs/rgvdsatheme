<script setup lang="ts">
defineProps<{
  heading: string;
  body: string;
  buttons: { label: string; url: string; style: "primary" | "outline" }[];
}>();
</script>

<template>
  <aside
    class="block-action-callout flex w-full flex-col gap-[18px] border-[3px] border-ink bg-ink px-10 py-9 text-cream shadow-brutal-red"
    data-tone="ink"
  >
    <div class="max-w-[24ch] font-display text-[clamp(1.4rem,3vw,1.9rem)] font-black uppercase leading-[1.15]">
      {{ heading }}
    </div>
    <p class="m-0 max-w-[56ch] text-[1.05rem] leading-[1.7] text-muted-on-ink">{{ body }}</p>
    <div class="flex flex-wrap gap-3.5">
      <a
        v-for="btn in buttons"
        :key="btn.label"
        :href="btn.url"
        class="px-[26px] py-[13px] text-[0.95rem] font-extrabold uppercase tracking-[0.05em] no-underline"
        :class="
          btn.style === 'primary'
            ? 'border-[3px] border-cream bg-cream text-ink shadow-[5px_5px_0_var(--color-brand-red)] transition-[box-shadow,transform] duration-100 hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[3px_3px_0_var(--color-brand-red)]'
            : 'border-[3px] border-cream bg-transparent text-cream hover:border-brand-red-deep hover:bg-brand-red-deep'
        "
      >
        {{ btn.label }}
      </a>
    </div>
  </aside>
</template>
