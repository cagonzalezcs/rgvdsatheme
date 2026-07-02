<script setup lang="ts">
interface Crumb {
  label: string;
  href?: string;
}

withDefaults(
  defineProps<{
    title: string;
    lede?: string;
    crumbs?: Crumb[];
  }>(),
  {
    lede: "",
    crumbs: () => [{ label: "Home", href: "/" }],
  },
);
</script>

<template>
  <section
    class="page-header bg-brand-red px-6 pb-14 pt-12 text-white"
    data-tone="red"
  >
    <div class="mx-auto flex max-w-[1140px] flex-col items-start gap-[18px]">
      <nav aria-label="Breadcrumb">
        <ol class="m-0 flex list-none flex-wrap items-center gap-2 rounded-full bg-white px-4 py-1.5 text-[0.85rem] font-bold">
          <li v-for="crumb in crumbs" :key="crumb.label" class="flex items-center gap-2">
            <a
              v-if="crumb.href"
              :href="crumb.href"
              class="text-red no-underline hover:underline hover:underline-offset-[3px]"
            >
              {{ crumb.label }}
            </a>
            <span v-else class="text-ink">{{ crumb.label }}</span>
            <span aria-hidden="true" class="text-text-muted">/</span>
          </li>
          <li aria-current="page" class="text-ink">{{ title }}</li>
        </ol>
      </nav>
      <h1
        class="m-0 max-w-[22ch] font-display text-[clamp(2rem,4.8vw,3.4rem)] font-black leading-[1.1] tracking-[-0.01em]"
      >
        {{ title }}
      </h1>
      <p v-if="lede" class="m-0 max-w-[48ch] text-[1.5rem] leading-[1.5]">
        {{ lede }}
      </p>
    </div>
  </section>
</template>
