<script setup lang="ts">
// Event agenda block — numbered steps, badges tinted with the category accent.
defineProps<{
  items: { title: string; desc?: string }[];
  accent: string;
  accentSoft: string;
}>();
</script>

<template>
  <div class="flex w-[min(66ch,100%)] flex-col gap-4">
    <h2
      class="m-0 scroll-mt-[110px] border-b-[3px] pb-3 font-display text-[1.5rem] font-extrabold tracking-[-0.01em]"
      :style="{ borderColor: accent }"
    >
      Agenda
    </h2>
    <ol class="m-0 flex list-none flex-col gap-3 p-0">
      <li v-for="(item, i) in items" :key="i" class="flex items-start gap-4">
        <span
          aria-hidden="true"
          class="flex size-[30px] flex-none items-center justify-center rounded-full font-display text-[0.9rem] font-extrabold"
          :style="{ background: accentSoft, color: accent }"
        >
          {{ i + 1 }}
        </span>
        <span class="pt-0.5 text-[1.05rem] leading-[1.6] text-text-strong">
          <strong>{{ item.title }}</strong><template v-if="item.desc"> — {{ item.desc }}</template>
        </span>
      </li>
    </ol>
  </div>
</template>
