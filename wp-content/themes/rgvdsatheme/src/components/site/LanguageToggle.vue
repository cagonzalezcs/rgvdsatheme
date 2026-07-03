<script setup lang="ts">
// EN/ES language switcher — a segmented pill per the design handoff (03-DESIGN-SPEC.md
// §SiteHeader; "RGV DSA Home.dc.html" langStyle). Under Polylang each language is a
// real URL (English at "/", Spanish at "/es/…"), so each segment is an <a> linking to
// the current page's translation (Polylang falls back to the language home when the
// page has no translation). The active language is a deep-red filled pill marked
// aria-current; navigating is the entire behavior — no cookie, no machine translation.
export interface LanguageLink {
  code: string;
  label: string;
  name: string;
  active: boolean;
  url: string;
}

withDefaults(
  defineProps<{
    /** One entry per site language, from the server (see inc/i18n.php). */
    languages?: LanguageLink[];
    /** Off-white container for the mobile drop panel (white surface) vs white on the red bar. */
    onLight?: boolean;
  }>(),
  {
    languages: () => [],
    onLight: false,
  },
);

const segmentClass =
  "cursor-pointer rounded-full border-0 px-3 py-1 text-[0.8rem] font-bold tracking-[0.04em] no-underline";
</script>

<template>
  <div
    v-if="languages.length > 1"
    role="group"
    aria-label="Language"
    :class="[
      'notranslate flex items-center gap-0.5 rounded-full p-[3px]',
      onLight ? 'bg-off-white' : 'bg-white',
    ]"
  >
    <a
      v-for="lang in languages"
      :key="lang.code"
      :href="lang.url"
      :lang="lang.code"
      :title="lang.name"
      :aria-current="lang.active ? 'true' : undefined"
      :class="[segmentClass, lang.active ? 'bg-red text-white' : 'bg-transparent text-red']"
    >
      {{ lang.label }}
    </a>
  </div>
</template>
