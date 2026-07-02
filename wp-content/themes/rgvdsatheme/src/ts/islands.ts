import { createApp, type Component } from "vue";

/**
 * Vue island registry.
 *
 * Twig usage:
 *   <div data-vue-island="Styleguide" data-props='{{ props_json|e("html_attr") }}'></div>
 *
 * Components load lazily so pages only ship the islands they mount.
 */
const registry: Record<string, () => Promise<{ default: Component }>> = {
  Styleguide: () => import("@/components/site/Styleguide.vue"),
  SiteHeader: () => import("@/components/site/SiteHeader.vue"),
  SiteFooter: () => import("@/components/site/SiteFooter.vue"),
  PageHeader: () => import("@/components/site/PageHeader.vue"),
  FaqAccordion: () => import("@/components/site/FaqAccordion.vue"),
  EventCalendar: () => import("@/components/site/EventCalendar.vue"),
};

export function mountIslands(root: ParentNode = document): void {
  for (const el of root.querySelectorAll<HTMLElement>("[data-vue-island]")) {
    void mountIsland(el);
  }
}

async function mountIsland(el: HTMLElement): Promise<void> {
  const name = el.dataset.vueIsland ?? "";
  const loader = registry[name];
  if (!loader) {
    console.warn(`[islands] no component registered for "${name}"`);
    return;
  }

  let props: Record<string, unknown> = {};
  if (el.dataset.props) {
    try {
      props = JSON.parse(el.dataset.props);
    } catch (error) {
      console.error(`[islands] invalid data-props JSON on "${name}"`, error);
    }
  }

  const { default: component } = await loader();
  createApp(component, props).mount(el);
}
