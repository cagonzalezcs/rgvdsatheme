<script setup lang="ts">
// Event accessibility/childcare note — accent-tinted callout aside.
defineProps<{ html: string; accent: string; accentSoft: string }>();
</script>

<template>
  <aside
    class="flex w-[min(74ch,100%)] flex-col gap-2 rounded-[16px] border-l-[5px] px-7 py-6"
    :style="{ background: accentSoft, borderColor: accent }"
  >
    <div class="font-display text-base font-extrabold" :style="{ color: accent }">
      Accessibility &amp; childcare
    </div>
    <!-- eslint-disable-next-line vue/no-v-html — WP wysiwyg content, kses-sanitized server-side -->
    <div class="prose-rgv text-base leading-[1.7] text-text-body" v-html="html"></div>
  </aside>
</template>
