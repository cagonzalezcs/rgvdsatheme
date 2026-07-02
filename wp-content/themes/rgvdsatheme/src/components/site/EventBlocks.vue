<script setup lang="ts">
// event_body flexible-content dispatcher (mirrors PostBlocks.vue). Prose reuses
// the blog BlockProse; the rest are event-specific. Accent + accentSoft are the
// category term color and its 10% wash, threaded into every accented element.
import BlockProse from "@/components/site/blog/blocks/BlockProse.vue";
import BlockAgenda from "@/components/site/BlockAgenda.vue";
import BlockGoodToKnow from "@/components/site/BlockGoodToKnow.vue";
import BlockA11yNote from "@/components/site/BlockA11yNote.vue";
import BlockMap from "@/components/site/BlockMap.vue";
import { type EventBlock } from "@/lib/events";

defineProps<{ blocks: EventBlock[]; accent: string; accentSoft: string }>();
</script>

<template>
  <template v-for="(block, i) in blocks" :key="i">
    <BlockProse v-if="block.type === 'prose'" :html="block.html" />
    <BlockAgenda
      v-else-if="block.type === 'agenda'"
      :items="block.items"
      :accent="accent"
      :accent-soft="accentSoft"
    />
    <BlockGoodToKnow
      v-else-if="block.type === 'good_to_know'"
      :items="block.items"
      :accent="accent"
    />
    <BlockA11yNote
      v-else-if="block.type === 'a11y_note'"
      :html="block.html"
      :accent="accent"
      :accent-soft="accentSoft"
    />
    <BlockMap v-else-if="block.type === 'map'" :address="block.address" :accent="accent" />
  </template>
</template>
