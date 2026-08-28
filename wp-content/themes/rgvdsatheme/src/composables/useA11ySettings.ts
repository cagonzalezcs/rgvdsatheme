import { reactive, watchEffect } from "vue";

export type TextSize = "default" | "large" | "xl";

export interface A11ySettings {
  textSize: TextSize;
  highContrast: boolean;
  reduceMotion: boolean;
}

const STORAGE_KEY = "rgv-dsa-a11y";
const STYLE_ID = "rgv-a11y-css";
const FONT_SIZES: Record<TextSize, string> = {
  default: "16px",
  large: "18px",
  xl: "20px",
};

const DEFAULTS: A11ySettings = {
  textSize: "default",
  highContrast: false,
  reduceMotion: false,
};

// Module-level singleton: several islands (header, calendar, …) share one
// settings object so a change in any widget applies everywhere at once.
let settings: A11ySettings | undefined;

function load(): A11ySettings {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) return { ...DEFAULTS, ...JSON.parse(raw) };
  } catch {
    /* corrupted storage falls back to defaults */
  }
  return { ...DEFAULTS };
}

function apply(current: A11ySettings): void {
  document.documentElement.style.fontSize = FONT_SIZES[current.textSize];

  let styleEl = document.getElementById(STYLE_ID);
  if (!styleEl) {
    styleEl = document.createElement("style");
    styleEl.id = STYLE_ID;
    document.head.appendChild(styleEl);
  }

  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  let css = "";
  if (current.reduceMotion || prefersReducedMotion) {
    css +=
      "*{animation:none !important; transition:none !important} html{scroll-behavior:auto !important}";
  }
  if (current.highContrast) {
    // White/light bands (cream, and the white CTA band tagged `orange`) go pure
    // white-on-black.
    css += '[data-tone="cream"],[data-tone="orange"]{background:#FFFFFF !important; color:#000000 !important}';
    // 06-V3-BRAND-REFRESH.md: red → #B5121B, green-dark → #3F5A23 (same values
    // as the `html.a11y-contrast` token swaps in tailwind.css).
    css += '[data-tone="red"]{background:#B5121B !important}';
    css += '[data-tone="ink"]{background:#000000 !important; color:#FFFFFF !important}';
    css += '[data-tone="green"]{background:#3F5A23 !important}';
    css += "body{background:#FFFFFF}";
  }
  styleEl.textContent = css;

  // Root-class hook for the CSS-variable swaps in tailwind.css
  // (`html.a11y-contrast { --color-red: … }`) — covers chips, pills and
  // outlines that consume the brand tokens without being whole `data-tone` bands.
  document.documentElement.classList.toggle("a11y-contrast", current.highContrast);
}

export function useA11ySettings() {
  if (!settings) {
    settings = reactive(load());
    watchEffect(() => {
      apply(settings!);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
    });
    window
      .matchMedia("(prefers-reduced-motion: reduce)")
      .addEventListener("change", () => apply(settings!));
  }

  return {
    settings,
    setTextSize: (size: TextSize) => (settings!.textSize = size),
    toggleHighContrast: () => (settings!.highContrast = !settings!.highContrast),
    toggleReduceMotion: () => (settings!.reduceMotion = !settings!.reduceMotion),
  };
}
