import { createApp, type App, type Component } from "vue";

const ISLAND_SELECTOR = "[data-vue-island]";

const registry: Record<string, () => Promise<{ default: Component }>> = {
  SiteHeader: () => import("@/components/site/SiteHeader.vue"),
};

const apps = new WeakMap<HTMLElement, App>();

export function mountIslands(root: ParentNode = document): void {
  for (const element of root.querySelectorAll<HTMLElement>(ISLAND_SELECTOR)) {
    void mountIsland(element);
  }
}

export function mountIslandsAsync(root: ParentNode = document): Promise<void> {
  const elements = Array.from(
    root.querySelectorAll<HTMLElement>(ISLAND_SELECTOR),
  );
  return Promise.all(elements.map(mountIsland))
    .then(() => undefined)
    .catch(console.error);
}

export function unmountIsland(root: ParentNode = document): void {
  for (const element of root.querySelectorAll<HTMLElement>(ISLAND_SELECTOR)) {
    apps.get(element)?.unmount();
    apps.delete(element);
  }
}

async function mountIsland(element: HTMLElement): Promise<void> {
  const name = element.dataset.vueIsland ?? "";
  const loader = registry[name];

  if (!loader) {
    console.error(`[islands] no component register for "${name}"`);
    return;
  }

  if (apps.has(element)) {
    return;
  }

  let props: Record<string, unknown> = {};
  if (element.dataset.props) {
    try {
      props = JSON.parse(element.dataset.props);
    } catch (error) {
      console.error(`[islands] invalid data-props JSON on "${name}"`, error);
    }
  }

  const { default: component } = await loader();
  const app = createApp(component, props);
  app.mount(element);
  apps.set(element, app);
}
