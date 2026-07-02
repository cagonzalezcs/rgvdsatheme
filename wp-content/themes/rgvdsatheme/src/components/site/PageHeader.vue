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
    class="page-header bg-brand-red px-6 py-12 text-cream md:py-14"
    data-tone="red"
  >
    <div class="mx-auto flex max-w-[1100px] flex-col gap-4">
      <nav aria-label="Breadcrumb">
        <ol class="m-0 flex list-none flex-wrap items-center gap-2 p-0 text-[0.85rem] font-bold uppercase tracking-[0.05em]">
          <li v-for="crumb in crumbs" :key="crumb.label" class="flex items-center gap-2">
            <a
              v-if="crumb.href"
              :href="crumb.href"
              class="text-cream/85 no-underline hover:underline hover:underline-offset-4"
            >
              {{ crumb.label }}
            </a>
            <span v-else>{{ crumb.label }}</span>
            <span aria-hidden="true" class="opacity-70">/</span>
          </li>
          <li aria-current="page">{{ title }}</li>
        </ol>
      </nav>
      <h1
        class="m-0 font-display text-[clamp(2rem,4.8vw,3.4rem)] font-black uppercase leading-[1.1] tracking-[-0.01em]"
      >
        {{ title }}
      </h1>
      <p v-if="lede" class="m-0 max-w-[60ch] text-[1.15rem] leading-relaxed">
        {{ lede }}
      </p>
    </div>
  </section>
</template>
