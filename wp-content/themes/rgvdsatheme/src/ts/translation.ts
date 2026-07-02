/**
 * Toggle ↔ GTranslate bridge.
 *
 * The PHP gate (inc/translation.php) decides where translation may run and
 * stamps <body data-translation-scope>; on those pages base.twig's hidden
 * gt-link makes the GTranslate plugin enqueue its base.js, which defines
 * window.doGTranslate and hides Google's own UI. This module drives that
 * machinery from the header toggle:
 *
 * - ES: load Google's element.js (the plugin lazy-loads it only on
 *   pointerenter of its own links, which our hidden bootstrap never gets),
 *   then doGTranslate('en|es') translates in place. Google persists
 *   `googtrans=/en/es` itself, so a later full load auto-translates.
 * - EN: expire every googtrans variant and reload — doGTranslate('en|en')
 *   can leave <font> wrapper artifacts, a reload guarantees pristine DOM
 *   for island remounts (openspec translations-layer design D2).
 *
 * Cookie contract: the theme's `rgvdsa_lang` (useLanguagePreference) is
 * authoritative; `googtrans` is derived state owned by Google/this bridge.
 */
import { readLanguageCookie } from "@/composables/useLanguagePreference";

declare global {
  interface Window {
    doGTranslate?: (langPair: string) => void;
    googleTranslateElementInit2?: () => void;
    gt_translate_script?: HTMLScriptElement;
  }
}

const ELEMENT_JS_SRC =
  "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2";

/** Whether the current page is allowed to machine-translate (PHP gate). */
export function pageTranslatable(): boolean {
  return document.body.dataset.translationScope === "page";
}

export function isSpanishPreferred(): boolean {
  return readLanguageCookie() === "es";
}

/** The googtrans target language ('es'), or null when the cookie is absent. */
function googTransLang(): string | null {
  const match = document.cookie.match(/(?:^|;\s*)googtrans=([^;]*)/);
  return match ? (decodeURIComponent(match[1]).split("/")[2] ?? null) : null;
}

/**
 * Inject Google's element.js exactly like the plugin's own load_tlib(),
 * honoring the same window.gt_translate_script guard so we never double-load.
 */
function ensureTranslateLib(): void {
  if (window.gt_translate_script) return;
  const script = document.createElement("script");
  script.src = ELEMENT_JS_SRC;
  window.gt_translate_script = script;
  document.body.appendChild(script);
}

/**
 * Translate the current page to Spanish in place. No-op off translatable
 * pages. Falls back to cookie + reload when the plugin bootstrap is absent
 * (e.g. the page arrived through an EN partial swap).
 */
export function activateSpanish(): void {
  if (!pageTranslatable()) return;

  if (typeof window.doGTranslate === "function" && window.googleTranslateElementInit2) {
    ensureTranslateLib();
    window.doGTranslate("en|es"); // retries internally until the combo exists
    return;
  }

  // Plugin assets missing — persist intent and let base.js auto-translate
  // on the fresh load (it loads element.js immediately when the cookie is set).
  document.cookie = "googtrans=/en/es; path=/";
  window.location.reload();
}

/**
 * Restore English: expire every googtrans variant Google may have written
 * (host-only, exact domain, dot-domain, registrable dot-domain) and reload
 * for an artifact-free DOM. No-op when no googtrans cookie exists.
 */
export function restoreEnglish(): void {
  if (googTransLang() === null) return;

  const expire = "googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
  const host = window.location.hostname;
  const registrable = host.split(".").slice(-2).join(".");
  document.cookie = expire;
  for (const domain of new Set([host, `.${host}`, `.${registrable}`])) {
    document.cookie = `${expire}; domain=${domain}`;
  }

  window.location.reload();
}

/**
 * Startup reconciliation: the preference survives googtrans loss. When the
 * visitor prefers ES on a translatable page but Google's cookie is gone,
 * re-activate; every other combination is already handled (cookie present →
 * base.js auto-translates; page not translatable → assets absent, inert).
 */
export function initTranslation(): void {
  if (!pageTranslatable()) return;
  if (!isSpanishPreferred() || googTransLang() === "es") return;

  if (typeof window.doGTranslate === "function") {
    activateSpanish();
    return;
  }

  // Plugin base.js hasn't executed yet (both bundles are deferred; order is
  // not guaranteed). Setting the cookie is enough: base.js reads it at parse
  // time and auto-loads element.js — no reload needed on this path.
  document.cookie = "googtrans=/en/es; path=/";
}
