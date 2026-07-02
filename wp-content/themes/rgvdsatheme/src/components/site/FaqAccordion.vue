<script setup lang="ts">
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

interface FaqItem {
  question: string;
  answer: string;
}

defineProps<{
  items: FaqItem[];
}>();
</script>

<template>
  <Accordion class="faq-accordion flex flex-col gap-2.5" type="single" collapsible>
    <AccordionItem
      v-for="(item, index) in items"
      :key="item.question"
      :value="`faq-${index}`"
      class="border-2 border-ink bg-white last:border-b-2"
    >
      <AccordionTrigger
        class="items-center gap-4 bg-transparent px-5 py-4 text-base font-extrabold text-ink hover:text-brand-red-deep hover:no-underline"
      >
        <span>{{ item.question }}</span>
        <template #icon>
          <svg
            class="pointer-events-none size-4 shrink-0 text-brand-red-deep"
            aria-hidden="true"
            viewBox="0 0 16 16"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="square"
          >
            <path d="M2 8h12" />
            <path class="[[data-state=open]_&]:hidden" d="M8 2v12" />
          </svg>
        </template>
      </AccordionTrigger>
      <AccordionContent class="px-5 pb-[18px]">
        <p class="m-0 text-base leading-[1.7] text-ink">{{ item.answer }}</p>
      </AccordionContent>
    </AccordionItem>
  </Accordion>
</template>
