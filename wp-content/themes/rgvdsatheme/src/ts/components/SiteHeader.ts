/**
 * Accessible site header: mobile disclosure panel + submenu disclosures.
 * Follows the APG disclosure navigation pattern.
 * Binds only to data attributes (data-site-header, data-nav-toggle,
 * data-nav-panel, data-submenu-toggle, data-submenu).
 */

// Must match $bp-md in src/scss/global/_breakpoints.scss
const DESKTOP_MQ = "(min-width: 48em)";

const FOCUSABLE_SELECTOR =
  'a[href], button:not([hidden]), [tabindex]:not([tabindex="-1"])';

class SiteHeader {
  #header: HTMLElement;
  #toggle: HTMLButtonElement;
  #panel: HTMLElement;
  #subtoggles: HTMLButtonElement[];
  #mq: MediaQueryList;

  constructor(
    header: HTMLElement,
    toggle: HTMLButtonElement,
    panel: HTMLElement,
  ) {
    this.#header = header;
    this.#toggle = toggle;
    this.#panel = panel;
    this.#subtoggles = [
      ...header.querySelectorAll<HTMLButtonElement>("[data-submenu-toggle]"),
    ];
    this.#mq = window.matchMedia(DESKTOP_MQ);
  }

  init(): void {
    this.#header.classList.add("js");
    this.#toggle.hidden = false;
    for (const subtoggle of this.#subtoggles) {
      subtoggle.hidden = false;
    }

    this.#toggle.addEventListener("click", () => this.#onToggleClick());
    for (const subtoggle of this.#subtoggles) {
      subtoggle.addEventListener("click", () =>
        this.#onSubtoggleClick(subtoggle),
      );
    }
    this.#header.addEventListener("keydown", (event) => this.#onKeydown(event));
    this.#header.addEventListener("focusout", (event) =>
      this.#onFocusout(event),
    );
    document.addEventListener("click", (event) => this.#onDocumentClick(event));
    this.#mq.addEventListener("change", () => this.#reset());
  }

  // --- Mobile panel ---

  #isPanelOpen(): boolean {
    return this.#panel.classList.contains("is-open");
  }

  #onToggleClick(): void {
    if (this.#isPanelOpen()) {
      this.#closePanel(true);
    } else {
      this.#openPanel();
    }
  }

  #openPanel(): void {
    this.#toggle.setAttribute("aria-expanded", "true");
    this.#panel.classList.add("is-open");
    if (!this.#mq.matches) {
      document.body.classList.add("is-locked");
    }
    this.#panelFocusables()[0]?.focus();
  }

  #closePanel(returnFocus: boolean): void {
    this.#toggle.setAttribute("aria-expanded", "false");
    this.#panel.classList.remove("is-open");
    document.body.classList.remove("is-locked");
    if (returnFocus) {
      this.#toggle.focus();
    }
  }

  #panelFocusables(): HTMLElement[] {
    const candidates = [
      ...this.#panel.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR),
    ];
    return candidates.filter((el) =>
      typeof el.checkVisibility === "function"
        ? el.checkVisibility()
        : el.offsetParent !== null,
    );
  }

  // --- Submenu disclosures ---

  #openSubmenus(): HTMLElement[] {
    return [
      ...this.#header.querySelectorAll<HTMLElement>("[data-submenu].is-open"),
    ];
  }

  #subtoggleFor(submenu: HTMLElement): HTMLButtonElement | null {
    if (!submenu.id) return null;
    return this.#header.querySelector<HTMLButtonElement>(
      `[data-submenu-toggle][aria-controls="${submenu.id}"]`,
    );
  }

  #submenuFor(subtoggle: HTMLButtonElement): HTMLElement | null {
    const id = subtoggle.getAttribute("aria-controls");
    return id ? document.getElementById(id) : null;
  }

  /** Close one submenu (no descendants). */
  #closeOne(submenu: HTMLElement): void {
    submenu.classList.remove("is-open");
    this.#subtoggleFor(submenu)?.setAttribute("aria-expanded", "false");
  }

  /** Close a submenu and any open descendants. */
  #closeSubmenu(submenu: HTMLElement): void {
    for (const child of submenu.querySelectorAll<HTMLElement>(
      "[data-submenu].is-open",
    )) {
      this.#closeOne(child);
    }
    this.#closeOne(submenu);
  }

  #closeAllSubmenus(): void {
    for (const submenu of this.#openSubmenus()) {
      this.#closeOne(submenu);
    }
  }

  #onSubtoggleClick(subtoggle: HTMLButtonElement): void {
    const submenu = this.#submenuFor(subtoggle);
    if (!submenu) return;

    if (subtoggle.getAttribute("aria-expanded") === "true") {
      this.#closeSubmenu(submenu);
      return;
    }

    // Close open sibling submenus at the same level (same parent UL),
    // including their descendants.
    const parentList = submenu.closest("li")?.parentElement;
    if (parentList) {
      for (const sibling of this.#openSubmenus()) {
        if (
          sibling !== submenu &&
          sibling.closest("li")?.parentElement === parentList
        ) {
          this.#closeSubmenu(sibling);
        }
      }
    }

    submenu.classList.add("is-open");
    subtoggle.setAttribute("aria-expanded", "true");
  }

  /**
   * Submenu Esc should act on: the submenu controlled by a focused, expanded
   * subtoggle, or the innermost open submenu containing focus.
   */
  #escTargetSubmenu(active: Element | null): HTMLElement | null {
    if (
      active instanceof HTMLButtonElement &&
      active.matches('[data-submenu-toggle][aria-expanded="true"]')
    ) {
      return this.#submenuFor(active);
    }
    return active?.closest<HTMLElement>("[data-submenu].is-open") ?? null;
  }

  /** Deepest open submenu (mobile Esc fallback when focus is outside any). */
  #deepestOpenSubmenu(): HTMLElement | null {
    let best: HTMLElement | null = null;
    let bestDepth = -1;
    for (const submenu of this.#openSubmenus()) {
      let depth = 0;
      let parent = submenu.parentElement;
      while (parent && parent !== this.#header) {
        if (parent.matches("[data-submenu].is-open")) depth += 1;
        parent = parent.parentElement;
      }
      if (depth > bestDepth) {
        best = submenu;
        bestDepth = depth;
      }
    }
    return best;
  }

  // --- Keyboard ---

  #onKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape") {
      this.#onEscape();
    } else if (event.key === "Tab") {
      this.#onTab(event);
    }
  }

  #onEscape(): void {
    const active = document.activeElement;

    if (this.#mq.matches) {
      // Desktop: close innermost open submenu containing focus.
      const target = this.#escTargetSubmenu(active);
      if (target) {
        this.#closeSubmenu(target);
        this.#subtoggleFor(target)?.focus();
      }
      return;
    }

    // Mobile: only meaningful while the panel is open.
    if (!this.#isPanelOpen()) return;

    const target = this.#escTargetSubmenu(active) ?? this.#deepestOpenSubmenu();
    if (target) {
      this.#closeSubmenu(target);
      this.#subtoggleFor(target)?.focus();
    } else {
      this.#closePanel(true);
    }
  }

  /**
   * Mobile focus trap: the cycle includes the toggle button itself (it sits
   * visually above the fixed panel). Tab from the last focusable in the panel
   * wraps to the toggle; Shift+Tab from the toggle wraps to the last
   * focusable in the panel.
   */
  #onTab(event: KeyboardEvent): void {
    if (this.#mq.matches || !this.#isPanelOpen()) return;

    const active = document.activeElement;
    const last = this.#panelFocusables().at(-1) ?? null;

    if (event.shiftKey && active === this.#toggle) {
      event.preventDefault();
      (last ?? this.#toggle).focus();
    } else if (!event.shiftKey && active === last) {
      event.preventDefault();
      this.#toggle.focus();
    }
  }

  // --- Dismissal ---

  #onFocusout(event: FocusEvent): void {
    if (!this.#mq.matches) return;
    const next = event.relatedTarget;
    if (!(next instanceof Node)) return;
    if (this.#header.contains(next)) return;
    this.#closeAllSubmenus();
  }

  #onDocumentClick(event: MouseEvent): void {
    const target = event.target;
    if (target instanceof Node && this.#header.contains(target)) return;
    this.#closeAllSubmenus();
    // Defensive: the panel is full-screen on mobile, so mostly moot.
    if (!this.#mq.matches && this.#isPanelOpen()) {
      this.#closePanel(false);
    }
  }

  /** Reset all state on breakpoint change. */
  #reset(): void {
    this.#closePanel(false);
    this.#closeAllSubmenus();
  }
}

export function initSiteHeader(): void {
  const header = document.querySelector<HTMLElement>("[data-site-header]");
  if (!header) return;

  const toggle = header.querySelector<HTMLButtonElement>("[data-nav-toggle]");
  const panel = header.querySelector<HTMLElement>("[data-nav-panel]");
  if (!toggle || !panel) {
    console.warn(
      "SiteHeader: missing [data-nav-toggle] or [data-nav-panel]; skipping init.",
    );
    return;
  }

  new SiteHeader(header, toggle, panel).init();
}
