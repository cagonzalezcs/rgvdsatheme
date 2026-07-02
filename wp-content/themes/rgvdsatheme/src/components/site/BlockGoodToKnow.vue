<script setup lang="ts">
// Event "Good to know" block — a simple bulleted list of practical notes.
defineProps<{ items: string[]; accent: string }>();
</script>

<template>
  <div class="flex w-[min(66ch,100%)] flex-col gap-3.5">
    <h2
      class="m-0 scroll-mt-[110px] border-b-[3px] pb-3 font-display text-[1.5rem] font-extrabold tracking-[-0.01em]"
      :style="{ borderColor: accent }"
    >
      Good to know
    </h2>
    <ul
      class="m-0 flex list-disc flex-col gap-2.5 pl-[22px] text-[1.05rem] leading-[1.6] text-text-strong"
    >
      <li v-for="(item, i) in items" :key="i">{{ item }}</li>
    </ul>
  </div>
</template>
