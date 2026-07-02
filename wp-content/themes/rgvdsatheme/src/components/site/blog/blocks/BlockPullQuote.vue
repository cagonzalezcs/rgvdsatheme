<script setup lang="ts">
defineProps<{
  quote: string;
  attribution?: string;
  accent: string;
}>();
</script>

<template>
  <aside
    class="block-pull-quote flex w-[min(74ch,100%)] flex-col gap-3.5 rounded-[18px] bg-white px-9 py-8 shadow-media"
    :style="{ borderLeft: `6px solid ${accent}` }"
  >
    <blockquote class="m-0 font-display text-[clamp(1.25rem,2.6vw,1.6rem)] font-bold leading-[1.4]">
      {{ quote }}
    </blockquote>
    <div
      v-if="attribution"
      class="text-[0.85rem] font-bold uppercase tracking-[0.06em]"
      :style="{ color: accent }"
    >
      — {{ attribution }}
    </div>
  </aside>
</template>
