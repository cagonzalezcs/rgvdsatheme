<script setup lang="ts">
defineProps<{ html: string }>();
</script>

<template>
  <!-- eslint-disable-next-line vue/no-v-html — WP wysiwyg content -->
  <div class="block-prose prose-rgv prose-post w-[min(66ch,100%)]" v-html="html"></div>
</template>
