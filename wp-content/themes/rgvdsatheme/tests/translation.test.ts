// @vitest-environment happy-dom

/* Bridge contract tests for src/ts/translation.ts (openspec translations-layer).
 * Pins our side of the GTranslate contract: googtrans is transient per-load
 * state (expired at startup — a cookie present at Google-element init makes
 * it mark the page translated WITHOUT translating), the combo is driven only
 * once its options exist, the gt_translate_script guard is honored, and EN
 * revert is expire + reload. */
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
  activateSpanish,
  initTranslation,
  isSpanishPreferred,
  pageTranslatable,
  restoreEnglish,
} from "@/ts/translation";

function clearCookies(): void {
  for (const pair of document.cookie.split(";")) {
    const name = pair.split("=")[0]?.trim();
    if (name) document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
  }
}

function setScope(scope: "page" | "none"): void {
  document.body.dataset.translationScope = scope;
}

/** Google's hidden language <select>, as the element renders it. */
function mountCombo(withOptions = true): HTMLSelectElement {
  const combo = document.createElement("select");
  combo.className = "goog-te-combo";
  if (withOptions) {
    for (const value of ["", "es", "fr"]) {
      const opt = document.createElement("option");
      opt.value = value;
      combo.appendChild(opt);
    }
  }
  document.body.appendChild(combo);
  return combo;
}

let reloadSpy: ReturnType<typeof vi.fn>;

beforeEach(() => {
  vi.useFakeTimers();
  clearCookies();
  setScope("page");
  document.documentElement.className = "";
  reloadSpy = vi.fn();
  // Location's props are getters, so spread loses them — stub what's used.
  vi.stubGlobal("location", { hostname: "rgvdsa.test", reload: reloadSpy });
});

afterEach(() => {
  vi.useRealTimers();
  vi.unstubAllGlobals();
  delete window.doGTranslate;
  delete window.googleTranslateElementInit2;
  delete window.gt_translate_script;
  document.querySelectorAll("script, select").forEach((el) => el.remove());
  sessionStorage.clear();
});

describe("pageTranslatable / isSpanishPreferred", () => {
  it("reads the body scope attribute", () => {
    expect(pageTranslatable()).toBe(true);
    setScope("none");
    expect(pageTranslatable()).toBe(false);
  });

  it("reads the rgvdsa_lang cookie", () => {
    expect(isSpanishPreferred()).toBe(false);
    document.cookie = "rgvdsa_lang=es; path=/";
    expect(isSpanishPreferred()).toBe(true);
  });
});

describe("activateSpanish", () => {
  it("no-ops on non-translatable pages", () => {
    setScope("none");
    window.googleTranslateElementInit2 = () => {};
    const combo = mountCombo();
    activateSpanish();
    expect(combo.value).toBe("");
    expect(reloadSpy).not.toHaveBeenCalled();
  });

  it("drives the combo to es when the plugin bootstrap is present", () => {
    window.googleTranslateElementInit2 = () => {};
    const combo = mountCombo();
    const changed = vi.fn();
    combo.addEventListener("change", changed);
    activateSpanish();
    expect(combo.value).toBe("es");
    expect(changed).toHaveBeenCalled();
    expect(reloadSpy).not.toHaveBeenCalled();
  });

  it("waits for combo options before driving (empty select must not be fired)", () => {
    window.googleTranslateElementInit2 = () => {};
    const combo = mountCombo(false);
    activateSpanish();
    expect(combo.value).toBe("");
    // options arrive later (Google populates async)
    for (const value of ["", "es"]) {
      const opt = document.createElement("option");
      opt.value = value;
      combo.appendChild(opt);
    }
    vi.advanceTimersByTime(1000);
    expect(combo.value).toBe("es");
  });

  it("injects element.js once, honoring the gt_translate_script guard", () => {
    window.googleTranslateElementInit2 = () => {};
    mountCombo();
    activateSpanish();
    const first = window.gt_translate_script;
    expect(first?.src).toContain("translate.google.com/translate_a/element.js");
    activateSpanish();
    expect(window.gt_translate_script).toBe(first);
    expect(document.querySelectorAll("script").length).toBe(1);
  });

  it("falls back to expire + reload when plugin assets are absent", () => {
    document.cookie = "googtrans=/en/es; path=/";
    activateSpanish();
    expect(document.cookie).not.toContain("googtrans=/en/es");
    expect(reloadSpy).toHaveBeenCalled();
  });

  it("reloads once via the poison guard when Google marks but never translates", () => {
    window.googleTranslateElementInit2 = () => {};
    mountCombo();
    activateSpanish();
    document.documentElement.classList.add("translated-ltr"); // marked, no <font> produced
    vi.advanceTimersByTime(5000);
    expect(reloadSpy).toHaveBeenCalledTimes(1);
    expect(sessionStorage.getItem("rgvdsa_gt_retry")).toBe("1");
    // second pass must not loop
    activateSpanish();
    vi.advanceTimersByTime(5000);
    expect(reloadSpy).toHaveBeenCalledTimes(1);
  });
});

describe("restoreEnglish", () => {
  it("no-ops when Google never engaged", () => {
    restoreEnglish();
    expect(reloadSpy).not.toHaveBeenCalled();
  });

  it("expires googtrans and reloads", () => {
    document.cookie = "googtrans=/en/es; path=/";
    restoreEnglish();
    expect(document.cookie).not.toContain("googtrans=/en/es");
    expect(reloadSpy).toHaveBeenCalled();
  });
});

describe("initTranslation (startup)", () => {
  it("always expires a stale googtrans on translatable pages (poisons element init)", () => {
    document.cookie = "googtrans=/en/es; path=/";
    initTranslation();
    expect(document.cookie).not.toContain("googtrans=/en/es");
    expect(reloadSpy).not.toHaveBeenCalled();
  });

  it("drives Spanish from the persistent preference", () => {
    document.cookie = "rgvdsa_lang=es; path=/";
    const combo = mountCombo();
    initTranslation();
    vi.advanceTimersByTime(1000);
    expect(combo.value).toBe("es");
    expect(window.gt_translate_script).toBeTruthy();
  });

  it("does nothing further when EN is preferred", () => {
    mountCombo();
    initTranslation();
    vi.advanceTimersByTime(2000);
    expect(window.gt_translate_script).toBeUndefined();
  });

  it("no-ops entirely off translatable pages", () => {
    setScope("none");
    document.cookie = "rgvdsa_lang=es; path=/";
    document.cookie = "googtrans=/en/es; path=/";
    initTranslation();
    expect(document.cookie).toContain("googtrans=/en/es"); // untouched — not our page
  });
});
