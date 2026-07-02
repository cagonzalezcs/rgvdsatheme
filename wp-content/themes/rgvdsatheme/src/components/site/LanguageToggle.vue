<script setup lang="ts">
// EN/ES language toggle — a segmented pill per the design handoff (03-DESIGN-SPEC.md
// §SiteHeader; "RGV DSA Home.dc.html" langStyle). Two <button> segments; the active
// language is a deep-red filled pill, the other is muted red text. Records the visitor's
// preference in a cookie and flips <html lang> (see useLanguagePreference). On
// translation-active pages (esEnabled, currently home only — inc/translation.php) it
// also drives live GTranslate machine translation via src/ts/translation.ts; elsewhere
// it stays a preference recorder and the ES tooltip states the scope. The shared
// module-level preference keeps all three responsive header instances in sync.
import { useLanguagePreference, type Lang } from "@/composables/useLanguagePreference";
import { activateSpanish, restoreEnglish } from "@/ts/translation";

const props = withDefaults(
  defineProps<{
    // True on pages where flipping ES translates live (fed from translation.active).
    esEnabled?: boolean;
    // Kept for API stability (SiteHeader passes it); unused under cookie-based gtranslate.
    esUrl?: string;
    // Off-white container for the mobile drop panel (white surface) vs white on the red bar.
    onLight?: boolean;
  }>(),
  {
    esEnabled: false,
    esUrl: "",
    onLight: false,
  },
);

const { isSpanish, setLanguage } = useLanguagePreference();

function onSelect(lang: Lang): void {
  setLanguage(lang);
  if (!props.esEnabled) return;
  if (lang === "es") activateSpanish();
  else restoreEnglish();
}

const segmentClass =
  "cursor-pointer rounded-full border-0 px-3 py-1 text-[0.8rem] font-bold tracking-[0.04em]";
</script>

<template>
  <div
    role="group"
    aria-label="Language"
    :class="[
      'flex items-center gap-0.5 rounded-full p-[3px]',
      onLight ? 'bg-off-white' : 'bg-white',
    ]"
  >
    <button
      type="button"
      :aria-pressed="!isSpanish"
      :class="[segmentClass, isSpanish ? 'bg-transparent text-red' : 'bg-red text-white']"
      @click="onSelect('en')"
    >
      EN
    </button>
    <button
      type="button"
      lang="es"
      :title="esEnabled ? 'Español' : 'Español — disponible en la página de inicio'"
      :aria-pressed="isSpanish"
      :class="[segmentClass, isSpanish ? 'bg-red text-white' : 'bg-transparent text-red']"
      @click="onSelect('es')"
    >
      ES
    </button>
  </div>
</template>
