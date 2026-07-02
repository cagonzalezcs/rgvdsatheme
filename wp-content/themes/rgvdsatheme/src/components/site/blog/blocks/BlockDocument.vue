<script setup lang="ts">
/* Same anatomy as the Interior template's document row (views/page.twig). */
defineProps<{
  url: string;
  title: string;
  description?: string;
}>();
</script>

<template>
  <div class="block-document flex w-[min(74ch,100%)] flex-wrap items-center justify-between gap-5 border-2 border-ink bg-white px-[22px] py-[18px]">
    <div class="flex items-center gap-4">
      <span aria-hidden="true" class="border-2 border-brand-red-deep px-2.5 py-2 font-mono text-[0.8rem] font-bold text-brand-red-deep">PDF</span>
      <div class="flex flex-col gap-[3px]">
        <span class="text-[1.05rem] font-extrabold">{{ title }}</span>
        <span v-if="description" class="text-[0.85rem] text-muted-on-cream">{{ description }}</span>
      </div>
    </div>
    <a
      :href="url"
      class="whitespace-nowrap border-2 border-ink px-4 py-[9px] text-[0.85rem] font-extrabold uppercase tracking-[0.05em] text-ink no-underline hover:bg-brand-red-deep hover:text-white"
    >
      Download
    </a>
  </div>
</template>
