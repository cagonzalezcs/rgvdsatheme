<script setup lang="ts">
/* Same anatomy as the Interior template's document row (views/page.twig). */
defineProps<{
  url: string;
  title: string;
  description?: string;
}>();
</script>

<template>
  <div class="block-document flex w-[min(74ch,100%)] flex-wrap items-center justify-between gap-5 rounded-[14px] bg-white px-[22px] py-[18px] shadow-card">
    <div class="flex items-center gap-4">
      <span aria-hidden="true" class="rounded-[10px] border-2 border-red px-2.5 py-2 font-mono text-[0.8rem] font-bold text-red">PDF</span>
      <div class="flex flex-col gap-[3px]">
        <span class="text-[1.05rem] font-bold">{{ title }}</span>
        <span v-if="description" class="text-[0.85rem] text-text-muted">{{ description }}</span>
      </div>
    </div>
    <a
      :href="url"
      class="whitespace-nowrap rounded-full border-2 border-red px-5 py-2 text-[0.9rem] font-bold text-red no-underline transition-colors hover:border-red-hover hover:bg-wash"
    >
      Download
    </a>
  </div>
</template>
