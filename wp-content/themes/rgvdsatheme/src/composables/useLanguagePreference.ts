// Persists the visitor's EN/ES language preference in a simple cookie.
//
// The site is not translated yet — this only records intent so a future Spanish
// build can honor it, and so all header instances of the toggle stay in sync.
// The `language` ref is module-scoped (singleton): every LanguageToggle mounted
// across the responsive header slots reads/writes the same reactive value, and a
// single module-level watcher mirrors it to the cookie.
import { computed, ref, watch } from "vue";

export type Lang = "en" | "es";

const COOKIE_NAME = "rgvdsa_lang";
const MAX_AGE = 60 * 60 * 24 * 365; // 1 year, in seconds

function readCookie(): Lang {
  if (typeof document === "undefined") return "en";
  const match = document.cookie.match(/(?:^|;\s*)rgvdsa_lang=(en|es)\b/);
  return match ? (match[1] as Lang) : "en";
}

function writeCookie(lang: Lang): void {
  if (typeof document === "undefined") return;
  document.cookie = `${COOKIE_NAME}=${lang}; path=/; max-age=${MAX_AGE}; samesite=lax`;
}

const language = ref<Lang>(readCookie());

watch(language, (lang) => writeCookie(lang));

export function useLanguagePreference() {
  const isSpanish = computed(() => language.value === "es");

  function setLanguage(lang: Lang): void {
    language.value = lang;
  }

  return { language, isSpanish, setLanguage };
}
