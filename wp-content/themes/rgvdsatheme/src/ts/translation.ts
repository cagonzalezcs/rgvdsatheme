/**
 * Toggle ↔ GTranslate bridge.
 *
 * The PHP gate (inc/translation.php) decides where translation may run and
 * stamps <body data-translation-scope>; on those pages base.twig's hidden
 * gt-link makes the GTranslate plugin enqueue its base.js, which defines
 * window.doGTranslate / googleTranslateElementInit2 and hides Google's own
 * UI. This module drives that machinery from the header toggle.
 *
 * Hard-won contract with the CURRENT Google translate element (2026):
 * - If a `googtrans` cookie exists when the element initializes, Google
 *   marks the page translated (`translated-ltr`, <html lang="es">) but
 *   issues ZERO translation requests — the page silently stays English and
 *   every later combo change no-ops. The plugin's cookie-based auto-resume
 *   is therefore dead. We expire the cookie BEFORE the element can read it
 *   and re-derive the language from `rgvdsa_lang` on every load.
 * - The plugin's own doGTranslate() fires the combo change as soon as the
 *   <select> exists, but the options populate later — a change on an empty
 *   select no-ops and doGTranslate stops retrying. We drive the combo
 *   ourselves once it actually has options.
 * - EN revert must expire the cookie and reload: in-place revert leaves
 *   <font> artifacts (design D2).
 *
 * Cookie contract: `rgvdsa_lang` (useLanguagePreference) is the only
 * persistent language state; `googtrans` is transient per-load Google
 * plumbing, expired at startup and re-created by Google on combo change.
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
const COMBO_POLL_MS = 300;
const COMBO_POLL_TRIES = 40; // ~12s — element.js + language list over slow links
const RETRY_FLAG = "rgvdsa_gt_retry";

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

/** Expire every googtrans variant Google may have written (host-only, exact
 * domain, dot-domain, registrable dot-domain). */
function expireGoogTrans(): void {
  const expire = "googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
  const host = window.location.hostname;
  const registrable = host.split(".").slice(-2).join(".");
  document.cookie = expire;
  for (const domain of new Set([host, `.${host}`, `.${registrable}`])) {
    document.cookie = `${expire}; domain=${domain}`;
  }
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
 * Drive Google's hidden combo to Spanish once its language list exists.
 * (Not the plugin's doGTranslate — that fires on an empty select and gives
 * up; see module header.) Google writes the googtrans cookie itself.
 */
function driveSpanish(tries = COMBO_POLL_TRIES): void {
  const combo = document.querySelector<HTMLSelectElement>("select.goog-te-combo");
  if (combo && combo.options.length > 0) {
    combo.value = "es";
    // Bubbling, fired twice — byte-for-byte what the plugin's fire_event does;
    // a non-bubbling change is ignored by Google's delegated listener.
    combo.dispatchEvent(new Event("change", { bubbles: true }));
    combo.dispatchEvent(new Event("change", { bubbles: true }));
    verifyTranslated();
    return;
  }
  if (tries > 0) setTimeout(() => driveSpanish(tries - 1), COMBO_POLL_MS);
}

/**
 * One-shot poison guard: if Google marked the page translated but produced
 * no translated text (it read a stale cookie before we expired it — a rare
 * script-order race), a single clean reload recovers; initTranslation
 * re-drives on the fresh load.
 */
function verifyTranslated(): void {
  setTimeout(() => {
    const marked = document.documentElement.classList.contains("translated-ltr");
    const translated = document.querySelector("font") !== null;
    if (translated) {
      sessionStorage.removeItem(RETRY_FLAG);
      return;
    }
    if (marked && !sessionStorage.getItem(RETRY_FLAG)) {
      sessionStorage.setItem(RETRY_FLAG, "1");
      expireGoogTrans();
      window.location.reload();
    }
  }, 4000);
}

/**
 * Translate the current page to Spanish in place. No-op off translatable
 * pages. Falls back to a clean reload when the plugin bootstrap is absent
 * (e.g. the page arrived through an EN partial swap) — initTranslation
 * drives the fresh load from the rgvdsa_lang preference.
 */
export function activateSpanish(): void {
  if (!pageTranslatable()) return;

  if (window.googleTranslateElementInit2) {
    ensureTranslateLib();
    driveSpanish();
    return;
  }

  expireGoogTrans(); // never let the next load's element init see a cookie
  window.location.reload();
}

/**
 * Restore English: expire the googtrans variants and reload for an
 * artifact-free DOM. No-op when Google never engaged.
 */
export function restoreEnglish(): void {
  if (googTransLang() === null && !document.documentElement.classList.contains("translated-ltr")) {
    return;
  }
  expireGoogTrans();
  window.location.reload();
}

/**
 * Startup: googtrans is transient — expire it before Google's element can
 * read it (a cookie at init makes the element mark the page translated
 * without translating; see module header), then re-derive from the
 * persistent rgvdsa_lang preference by driving the combo.
 */
export function initTranslation(): void {
  if (!pageTranslatable()) return;
  expireGoogTrans();
  if (isSpanishPreferred()) {
    ensureTranslateLib();
    driveSpanish();
  }
}
