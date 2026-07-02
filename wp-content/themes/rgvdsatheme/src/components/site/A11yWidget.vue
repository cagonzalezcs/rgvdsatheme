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
      class="a11y-widget cursor-pointer border-2 border-cream bg-cream px-3 py-[5px] text-base font-extrabold text-brand-red-deep hover:border-brand-red-deep hover:bg-brand-red-deep hover:text-white"
      aria-label="Accessibility options"
      title="Accessibility options"
    >
      Aa
    </PopoverTrigger>
    <PopoverContent align="end" class="w-[280px] p-[18px]">
      <div class="flex flex-col gap-4">
        <div class="font-display text-[0.95rem] font-extrabold uppercase tracking-[0.04em]">
          Accessibility
        </div>

        <div class="flex flex-col gap-2">
          <div class="text-[0.85rem] font-bold">Text size</div>
          <div class="flex gap-1.5">
            <button
              v-for="s in sizes"
              :key="s.value"
              type="button"
              class="flex-1 cursor-pointer border-2 border-ink py-2 text-[0.95rem] font-extrabold"
              :class="
                settings.textSize === s.value
                  ? 'bg-ink text-cream'
                  : 'bg-transparent text-ink hover:bg-divider-cream'
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
          class="flex cursor-pointer items-center justify-between gap-3 border-0 bg-transparent p-0 text-left text-[0.95rem] font-bold text-ink"
          :aria-pressed="settings.highContrast"
          @click="toggleHighContrast()"
        >
          <span>High contrast</span>
          <span
            class="border-2 px-2.5 py-0.5 text-[0.8rem] font-extrabold uppercase"
            :class="
              settings.highContrast
                ? 'border-ink bg-brand-red-deep text-cream'
                : 'border-border-muted bg-transparent text-muted-2'
            "
          >
            {{ settings.highContrast ? "On" : "Off" }}
          </span>
        </button>

        <button
          type="button"
          class="flex cursor-pointer items-center justify-between gap-3 border-0 bg-transparent p-0 text-left text-[0.95rem] font-bold text-ink"
          :aria-pressed="settings.reduceMotion"
          @click="toggleReduceMotion()"
        >
          <span>Reduce motion</span>
          <span
            class="border-2 px-2.5 py-0.5 text-[0.8rem] font-extrabold uppercase"
            :class="
              settings.reduceMotion
                ? 'border-ink bg-brand-red-deep text-cream'
                : 'border-border-muted bg-transparent text-muted-2'
            "
          >
            {{ settings.reduceMotion ? "On" : "Off" }}
          </span>
        </button>
      </div>
    </PopoverContent>
  </Popover>
</template>
