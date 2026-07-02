<script setup lang="ts">
defineProps<{
  heading: string;
  body: string;
  buttons: { label: string; url: string; style: "primary" | "outline" }[];
}>();
</script>

<template>
  <aside
    class="block-action-callout flex w-full flex-col gap-[18px] rounded-[20px] bg-ink px-10 py-9 text-white"
    data-tone="ink"
  >
    <div class="max-w-[24ch] font-display text-[clamp(1.4rem,3vw,1.9rem)] font-extrabold leading-[1.2] tracking-[-0.01em]">
      {{ heading }}
    </div>
    <p class="m-0 max-w-[56ch] text-[1.05rem] leading-[1.7] text-muted-on-ink">{{ body }}</p>
    <div class="flex flex-wrap gap-3.5">
      <a
        v-for="btn in buttons"
        :key="btn.label"
        :href="btn.url"
        class="rounded-full px-[26px] py-[13px] text-[0.95rem] font-bold no-underline transition-colors"
        :class="
          btn.style === 'primary'
            ? 'bg-white text-ink hover:bg-pink'
            : 'border-2 border-[#57534e] bg-transparent text-white hover:border-white'
        "
      >
        {{ btn.label }}
      </a>
    </div>
  </aside>
</template>
