<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { useA11ySettings, type TextSize } from "@/composables/useA11ySettings";

const { settings, setTextSize, toggleHighContrast, toggleReduceMotion } =
  useA11ySettings();

const sizes: { value: TextSize; label: string }[] = [
  { value: "default", label: "A" },
  { value: "large", label: "A+" },
  { value: "xl", label: "A++" },
];
</script>

<template>
  <Popover>
    <PopoverTrigger
      class="a11y-widget inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-[18px] py-2 font-display text-[0.95rem] font-normal text-red transition-[box-shadow,color] hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(35,31,32,0.25)]"
      aria-label="Accessibility options"
      title="Accessibility options"
    >
      Aa
    </PopoverTrigger>
    <PopoverContent
      align="end"
      class="z-[200] w-[280px] rounded-[14px] border-cream bg-white p-[18px] font-sans text-ink shadow-popover"
    >
      <div class="flex flex-col gap-4">
        <div class="text-base font-bold">Accessibility</div>

        <div class="flex flex-col gap-2">
          <div class="text-[0.9rem] font-bold">Text size</div>
          <div class="flex gap-1.5">
            <button
              v-for="s in sizes"
              :key="s.value"
              type="button"
              class="flex-1 cursor-pointer rounded-[8px] border py-2 text-[0.95rem] font-bold"
              :class="
                settings.textSize === s.value
                  ? 'border-ink bg-ink text-white'
                  : 'border-border-control bg-white text-ink hover:bg-cream'
              "
              :aria-pressed="settings.textSize === s.value"
              @click="setTextSize(s.value)"
            >
              {{ s.label }}
            </button>
          </div>
        </div>

        <button
          type="button"
          class="flex cursor-pointer items-center justify-between gap-3 border-0 bg-transparent p-0 text-left text-[0.9rem] font-bold text-ink"
          :aria-pressed="settings.highContrast"
          @click="toggleHighContrast()"
        >
          <span>High contrast</span>
          <span
            class="rounded-full border px-3 py-1 text-[0.75rem] font-bold uppercase tracking-[0.06em]"
            :class="
              settings.highContrast
                ? 'border-red bg-brand-red text-white'
                : 'border-border-control bg-transparent text-text-muted'
            "
          >
            {{ settings.highContrast ? "On" : "Off" }}
          </span>
        </button>

        <button
          type="button"
          class="flex cursor-pointer items-center justify-between gap-3 border-0 bg-transparent p-0 text-left text-[0.9rem] font-bold text-ink"
          :aria-pressed="settings.reduceMotion"
          @click="toggleReduceMotion()"
        >
          <span>Reduce motion</span>
          <span
            class="rounded-full border px-3 py-1 text-[0.75rem] font-bold uppercase tracking-[0.06em]"
            :class="
              settings.reduceMotion
                ? 'border-red bg-brand-red text-white'
                : 'border-border-control bg-transparent text-text-muted'
            "
          >
            {{ settings.reduceMotion ? "On" : "Off" }}
          </span>
        </button>
      </div>
    </PopoverContent>
  </Popover>
</template>
