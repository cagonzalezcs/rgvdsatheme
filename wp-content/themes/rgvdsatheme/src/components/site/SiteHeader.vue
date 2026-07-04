<script setup lang="ts">
import {
  ref,
  computed,
  watch,
  nextTick,
  type ComponentPublicInstance,
} from "vue";
import { Menu, X } from "lucide-vue-next";
import { location } from "@/lib/location";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Drawer,
  DrawerClose,
  DrawerContent,
  DrawerDescription,
  DrawerTitle,
} from "@/components/ui/drawer";
import A11yWidget from "@/components/site/A11yWidget.vue";
import LanguageToggle, {
  type LanguageLink,
} from "@/components/site/LanguageToggle.vue";

interface NavLink {
  label: string;
  href: string;
}

const props = withDefaults(
  defineProps<{
    joinUrl?: string;
    joinLabel?: string;
    aboutLabel?: string;
    logoUrl?: string;
    homeUrl?: string;
    aboutItems?: NavLink[];
    navItems?: NavLink[];
    currentPath?: string;
    /** One entry per site language (Polylang) — drives the header switcher. */
    languages?: LanguageLink[];
  }>(),
  {
    joinUrl: "https://act.dsausa.org/donate/membership",
    joinLabel: "Join DSA",
    aboutLabel: "About",
    logoUrl: "",
    homeUrl: "/",
    languages: () => [],
    aboutItems: () => [
      { label: "About the Chapter", href: "/about/" },
      { label: "Mission & History", href: "/about/#mission" },
      { label: "Counties We Serve", href: "/about/#counties" },
      { label: "Committees", href: "/about/#committees" },
      { label: "Bylaws & Code of Conduct", href: "/about/#bylaws" },
      { label: "FAQ", href: "/about/#faq" },
    ],
    navItems: () => [
      { label: "Calendar", href: "/calendar/" },
      { label: "Blog", href: "/blog/" },
      { label: "Get Involved", href: "/get-involved/" },
    ],
    currentPath: "",
  },
);

// v2: larger sentence-case nav, rounded translucent-ink hover pill (03-DESIGN-SPEC.md § SiteHeader).
const navLinkClass =
  "rounded-[10px] px-4 py-2.5 font-display text-[1.17rem] font-bold text-white no-underline hover:bg-[rgba(28,25,23,0.18)]";

// Below lg the About▾ hover-dropdown collapses to a plain About link (05 §3a).
const flatNav = computed<NavLink[]>(() => [
  { label: props.aboutLabel, href: props.aboutItems[0]?.href ?? "/about/" },
  ...props.navItems,
]);

const isMenuOpen = ref(false);
const drawerCloseRef = ref<ComponentPublicInstance | null>(null);

// vaul-vue hardcodes a `.prevent` on openAutoFocus (to keep mobile keyboards from
// popping), so reka never moves focus into the drawer — do it ourselves.
function onDrawerOpenFocus(e: Event) {
  e.preventDefault();
  nextTick(() => (drawerCloseRef.value?.$el as HTMLElement | undefined)?.focus());
}

// Reactive current path so active state updates during client-side navigation
// (the header stays mounted across swaps). Falls back to the SSR prop first paint.
const currentPath = computed(() => location.path || props.currentPath);

/** Normalize to a comparable pathname: strip origin from absolute menu hrefs,
 * drop hash/query, and normalize the trailing slash. */
function normalizePath(href: string): string {
  let path = href;
  try {
    path = new URL(href, window.location.origin).pathname;
  } catch {
    /* relative fragment or malformed — compare as-is */
  }
  return path !== "/" ? path.replace(/\/$/, "") : path;
}

function isCurrent(href: string): boolean {
  if (currentPath.value === "") return false;
  return normalizePath(href) === normalizePath(currentPath.value);
}

// Close the mobile menu whenever a client navigation commits.
watch(
  () => location.path,
  () => {
    isMenuOpen.value = false;
  },
);
</script>

<template>
  <header
    class="site-header sticky top-0 z-100 bg-brand-red shadow-header [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
    data-tone="red"
  >
    <!-- ============ MOBILE (base → md): logo + hamburger + drop panel ============ -->
    <div class="md:hidden">
      <div
        class="mx-auto flex min-h-14 max-w-[1220px] items-center justify-between gap-3 px-4 py-2.5"
      >
        <a
          :href="homeUrl"
          aria-label="RGV DSA home"
          class="flex flex-none items-center"
        >
          <img
            :src="logoUrl"
            alt="Rio Grande Valley Democratic Socialists of America"
            class="block h-10 w-auto"
            width="931"
            height="358"
          />
        </a>
        <button
          type="button"
          class="flex size-11 cursor-pointer items-center justify-center rounded-[10px] border-2 border-white/65 bg-transparent text-white hover:bg-[rgba(28,25,23,0.18)]"
          :aria-expanded="isMenuOpen"
          aria-label="Menu"
          @click="isMenuOpen = true"
        >
          <Menu class="size-6" />
        </button>
      </div>

      <Drawer
        v-model:open="isMenuOpen"
        direction="right"
        :should-scale-background="false"
      >
        <DrawerContent
          class="z-[160] rounded-none border-l border-pink bg-white [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
          aria-label="Menu"
          @open-auto-focus="onDrawerOpenFocus"
        >
          <DrawerTitle class="sr-only">Menu</DrawerTitle>
          <DrawerDescription class="sr-only">
            Site navigation, language and accessibility options
          </DrawerDescription>
          <div class="flex justify-end px-4 py-2.5">
            <DrawerClose
              ref="drawerCloseRef"
              class="flex size-11 cursor-pointer items-center justify-center rounded-[10px] border-2 border-hairline bg-transparent text-ink hover:bg-tint"
              aria-label="Close menu"
            >
              <X class="size-6" />
            </DrawerClose>
          </div>
          <nav aria-label="Main" class="flex flex-col overflow-y-auto">
            <a
              v-for="item in flatNav"
              :key="item.label"
              :href="item.href"
              class="border-b border-hairline px-5 py-[15px] font-display text-[1.05rem] font-bold text-ink no-underline first:border-t hover:bg-tint hover:text-red"
              :aria-current="isCurrent(item.href) ? 'page' : undefined"
            >
              {{ item.label }}
            </a>
          </nav>
          <div class="flex items-center justify-between gap-3 px-5 py-3.5">
            <LanguageToggle :languages="languages" on-light />
            <A11yWidget />
          </div>
          <div class="mt-auto px-5 pb-5 pt-1">
            <a
              :href="joinUrl"
              target="_blank"
              rel="noopener"
              class="block rounded-full bg-red px-6 py-3.5 text-center text-base font-bold text-white no-underline hover:bg-red-hover"
            >
              {{ joinLabel }}
            </a>
          </div>
        </DrawerContent>
      </Drawer>
    </div>

    <!-- ============ TABLET (md → lg): two-tier, red nav strip ============ -->
    <div class="hidden md:block lg:hidden">
      <div
        class="mx-auto flex min-h-[60px] max-w-[1220px] items-center justify-between gap-4 px-7 py-2.5"
      >
        <a
          :href="homeUrl"
          aria-label="RGV DSA home"
          class="flex flex-none items-center"
        >
          <img
            :src="logoUrl"
            alt="Rio Grande Valley Democratic Socialists of America"
            class="block h-[46px] w-auto"
            width="931"
            height="358"
          />
        </a>
        <div class="flex items-center gap-2.5">
          <LanguageToggle :languages="languages" />
          <A11yWidget />
          <a
            :href="joinUrl"
            target="_blank"
            rel="noopener"
            class="rounded-full bg-white px-5 py-2.5 text-[0.95rem] font-bold text-red no-underline hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(28,25,23,0.25)]"
          >
            {{ joinLabel }}
          </a>
        </div>
      </div>
      <nav
        aria-label="Main"
        class="flex items-center justify-center gap-1 bg-red px-4 py-0.5"
      >
        <a
          v-for="item in flatNav"
          :key="item.label"
          :href="item.href"
          class="rounded-[10px] px-[18px] py-[11px] font-display text-base font-bold text-white no-underline hover:bg-[rgba(28,25,23,0.22)]"
          :class="
            isCurrent(item.href)
              ? 'underline decoration-2 underline-offset-[6px]'
              : ''
          "
          :aria-current="isCurrent(item.href) ? 'page' : undefined"
        >
          {{ item.label }}
        </a>
      </nav>
    </div>

    <!-- ============ DESKTOP (lg+): single row, About▾ hover dropdown ============ -->
    <div
      class="mx-auto hidden min-h-[64px] max-w-[1380px] items-center justify-between gap-6 px-6 py-2.5 lg:flex"
    >
      <a
        :href="homeUrl"
        aria-label="RGV DSA home"
        class="flex flex-none items-center"
      >
        <img
          :src="logoUrl"
          alt="Rio Grande Valley Democratic Socialists of America"
          class="block h-[58px] w-auto"
          width="931"
          height="358"
        />
      </a>

      <nav aria-label="Main" class="flex items-center gap-0.5">
        <DropdownMenu>
          <DropdownMenuTrigger
            :class="`cursor-pointer border-0 bg-transparent ${navLinkClass}`"
          >
            {{ aboutLabel }}&nbsp;▾
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="start"
            class="z-[200] min-w-[256px] rounded-[14px] border-none bg-white p-2 shadow-popover"
          >
            <DropdownMenuItem
              v-for="item in aboutItems"
              :key="item.label"
              as-child
              class="rounded-[9px] px-[15px] py-[11px] font-display text-[0.95rem] font-semibold focus:bg-tint focus:text-red"
            >
              <a
                :href="item.href"
                class="block cursor-pointer text-ink no-underline hover:text-red focus:text-red"
                >{{ item.label }}</a
              >
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          :class="[
            navLinkClass,
            isCurrent(item.href)
              ? 'underline decoration-[3px] underline-offset-[6px]'
              : '',
          ]"
          :aria-current="isCurrent(item.href) ? 'page' : undefined"
        >
          {{ item.label }}
        </a>
      </nav>

      <div class="flex flex-wrap items-center gap-2.5">
        <LanguageToggle :languages="languages" />
        <A11yWidget />
        <a
          :href="joinUrl"
          target="_blank"
          rel="noopener"
          class="rounded-full bg-white px-[22px] py-2.5 text-[0.95rem] font-bold text-red no-underline hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(28,25,23,0.25)]"
        >
          {{ joinLabel }}
        </a>
      </div>
    </div>
  </header>
</template>
