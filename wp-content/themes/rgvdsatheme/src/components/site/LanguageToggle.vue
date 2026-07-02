<script setup lang="ts">
// EN/ES language toggle — a real ShadCN Switch that records the visitor's language
// preference in a cookie (see useLanguagePreference). The site is not translated to
// Spanish yet, so flipping to ES only stores the preference; the "próximamente"
// tooltip on the ES label signals that Spanish is coming. The shared module-level
// preference keeps all three responsive header instances in sync.
import { Switch } from "@/components/ui/switch";
import { useLanguagePreference } from "@/composables/useLanguagePreference";

// esEnabled / esUrl are kept as component API for the future-navigation hook in
// onToggle, even though the current behavior is store-only.
withDefaults(
  defineProps<{
    esEnabled?: boolean;
    esUrl?: string;
  }>(),
  {
    esEnabled: false,
    esUrl: "",
  },
);

const { isSpanish, setLanguage } = useLanguagePreference();

function onToggle(next: boolean): void {
  setLanguage(next ? "es" : "en");
  // Future hook: once a Spanish site exists, opt-in could navigate instead of
  // store-only, e.g. `if (next && esEnabled && esUrl) location.href = esUrl;`.
}
</script>

<template>
  <div
    role="group"
    aria-label="Language"
    class="flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[0.8rem] font-bold tracking-[0.04em]"
  >
    <span :class="isSpanish ? 'text-red/55' : 'text-red'">EN</span>
    <Switch
      :model-value="isSpanish"
      aria-label="Switch site language to Spanish"
      class="border-red data-[state=checked]:bg-red data-[state=unchecked]:bg-white"
      @update:model-value="onToggle"
    />
    <span
      lang="es"
      title="Español — próximamente"
      :class="isSpanish ? 'text-red' : 'text-red/55'"
      >ES</span
    >
  </div>
</template>
