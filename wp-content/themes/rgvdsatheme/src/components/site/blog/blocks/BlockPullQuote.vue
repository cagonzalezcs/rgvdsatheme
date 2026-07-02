<script setup lang="ts">
defineProps<{
  quote: string;
  attribution?: string;
  accent: string;
}>();
</script>

<template>
  <aside
    class="block-pull-quote flex w-[min(74ch,100%)] flex-col gap-3.5 border-[3px] border-ink bg-white px-9 py-8"
    :style="{ boxShadow: `8px 8px 0 ${accent}` }"
  >
    <blockquote class="m-0 font-display text-[clamp(1.25rem,2.6vw,1.6rem)] font-extrabold leading-[1.4]">
      {{ quote }}
    </blockquote>
    <div
      v-if="attribution"
      class="text-[0.85rem] font-extrabold uppercase tracking-[0.08em]"
      :style="{ color: accent }"
    >
      — {{ attribution }}
    </div>
  </aside>
</template>
