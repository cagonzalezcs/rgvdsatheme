// Persists the visitor's EN/ES language preference in a simple cookie.
//
// This cookie (`rgvdsa_lang`) is the authoritative language preference; the
// GTranslate bridge (src/ts/translation.ts) derives its googtrans state from
// it on pages where translation is active. The `language` ref is module-scoped
// (singleton): every LanguageToggle mounted across the responsive header slots
// reads/writes the same reactive value, and a single module-level watcher
// mirrors it to the cookie.
import { computed, ref, watch } from "vue";

export type Lang = "en" | "es";

const COOKIE_NAME = "rgvdsa_lang";
const MAX_AGE = 60 * 60 * 24 * 365; // 1 year, in seconds

/** Read the persisted preference. Exported for the GTranslate bridge. */
export function readLanguageCookie(): Lang {
  if (typeof document === "undefined") return "en";
  const match = document.cookie.match(/(?:^|;\s*)rgvdsa_lang=(en|es)\b/);
  return match ? (match[1] as Lang) : "en";
}

// Persist the preference (cookie only). Deliberately does NOT touch
// document.documentElement.lang: Google's translate element owns <html lang>
// while translating, and pre-setting it to "es" makes Google treat the (still
// English) page as already Spanish — a source==target no-op that silently
// disables translation. See src/ts/translation.ts.
function apply(lang: Lang): void {
  if (typeof document === "undefined") return;
  document.cookie = `${COOKIE_NAME}=${lang}; path=/; max-age=${MAX_AGE}; samesite=lax`;
}

const language = ref<Lang>(readLanguageCookie());

watch(language, apply);

export function useLanguagePreference() {
  const isSpanish = computed(() => language.value === "es");

  function setLanguage(lang: Lang): void {
    language.value = lang;
  }

  return { language, isSpanish, setLanguage };
}
