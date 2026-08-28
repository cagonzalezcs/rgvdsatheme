<script setup lang="ts">
import {
  ref,
  computed,
  nextTick,
  type ComponentPublicInstance,
} from "vue";
import { Menu, X } from "lucide-vue-next";
import { location } from "@/lib/location";
import { menu } from "@/lib/menu";
import { languageState, setLanguages } from "@/lib/languages";
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

// v3 skin (06-V3-BRAND-REFRESH.md § Header): Bowlby One 1.06rem white nav
// links on the adjusted #DC1520 bar, radius-10 translucent-ink hover pill.
// Bowlby runs visually heavy, so sizes sit ~15% under their Manifold equivalents.
const navLinkClass =
  "rounded-[10px] px-3.5 py-2.5 font-display text-[1.06rem] font-normal text-white no-underline hover:bg-[rgba(35,31,32,0.18)]";

// Pill buttons: Bowlby One, radius 999, red on white, 3px ink ring on hover.
const pillClass =
  "rounded-full bg-white font-display text-[0.95rem] font-normal text-red no-underline transition-[box-shadow,color] hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(35,31,32,0.25)]";

// Below lg the About▾ hover-dropdown collapses to a plain About link (05 §3a).
const flatNav = computed<NavLink[]>(() => [
  { label: props.aboutLabel, href: props.aboutItems[0]?.href ?? "/about/" },
  ...props.navItems,
]);

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

// Switcher URLs are the current page's translations. The header stays mounted
// across client navigations, so the mount-time `languages` prop would freeze at
// the entry page's URLs; ts/navigation.ts refreshes the reactive store on every
// commit. Seed it from the SSR prop so the first paint (pre-nav) is correct.
if (!languageState.list.length) setLanguages(props.languages);
const currentLanguages = computed(() =>
  languageState.list.length ? languageState.list : props.languages,
);

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
</script>

<template>
  <header
    class="site-header sticky top-0 z-100 bg-brand-red font-sans shadow-header [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
    data-tone="red"
  >
    <!-- ============ MOBILE (base → md): logo + hamburger + drop panel ============ -->
    <div class="md:hidden">
      <div
        class="mx-auto flex min-h-14 max-w-[1320px] items-center justify-between gap-3 px-4 py-2"
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
            width="213"
            height="89"
          />
        </a>
        <button
          type="button"
          class="flex size-11 cursor-pointer items-center justify-center rounded-[10px] border-2 border-white/65 bg-transparent text-white hover:bg-[rgba(35,31,32,0.18)]"
          :aria-expanded="menu.open"
          aria-label="Menu"
          @click="menu.open = true"
        >
          <Menu class="size-6" />
        </button>
      </div>

      <Drawer
        v-model:open="menu.open"
        direction="right"
        :should-scale-background="false"
      >
        <DrawerContent
          class="z-[160] rounded-none border-l border-cream bg-white font-sans text-ink [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
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
              class="flex size-11 cursor-pointer items-center justify-center rounded-[10px] border-2 border-cream bg-transparent text-ink hover:bg-cream"
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
              class="border-b border-cream px-5 py-[15px] font-display text-[1.05rem] font-normal text-ink no-underline first:border-t hover:bg-cream hover:text-red"
              :aria-current="isCurrent(item.href) ? 'page' : undefined"
            >
              {{ item.label }}
            </a>
          </nav>
          <div class="flex items-center justify-between gap-3 px-5 py-3.5">
            <LanguageToggle :languages="currentLanguages" on-light />
            <A11yWidget />
          </div>
          <div class="mt-auto px-5 pb-5 pt-1">
            <a
              :href="joinUrl"
              target="_blank"
              rel="noopener"
              class="block rounded-full bg-brand-red px-6 py-3.5 text-center font-display text-base font-normal text-white no-underline hover:bg-brand-red-deep"
            >
              {{ joinLabel }}
            </a>
          </div>
        </DrawerContent>
      </Drawer>
    </div>

    <!-- ============ TABLET (md → lg): two-tier, deep-red nav strip ============ -->
    <div class="hidden md:block lg:hidden">
      <div
        class="mx-auto flex min-h-[64px] max-w-[1320px] items-center justify-between gap-4 px-6 py-2"
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
            width="213"
            height="89"
          />
        </a>
        <div class="flex items-center gap-2.5">
          <LanguageToggle :languages="currentLanguages" />
          <A11yWidget />
          <a
            :href="joinUrl"
            target="_blank"
            rel="noopener"
            :class="`${pillClass} px-5 py-2.5`"
          >
            {{ joinLabel }}
          </a>
        </div>
      </div>
      <nav
        aria-label="Main"
        class="flex items-center justify-center gap-1 bg-brand-red-deep px-4 py-0.5"
      >
        <a
          v-for="item in flatNav"
          :key="item.label"
          :href="item.href"
          class="rounded-[10px] px-[18px] py-[11px] font-display text-base font-normal text-white no-underline hover:bg-[rgba(35,31,32,0.22)]"
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
      class="mx-auto hidden min-h-[76px] max-w-[1320px] items-center justify-between gap-6 px-6 py-2 lg:flex"
    >
      <a
        :href="homeUrl"
        aria-label="RGV DSA home"
        class="flex flex-none items-center"
      >
        <img
          :src="logoUrl"
          alt="Rio Grande Valley Democratic Socialists of America"
          class="block h-[57px] w-auto"
          width="213"
          height="89"
        />
      </a>

      <nav aria-label="Main" class="flex items-center gap-[18px]">
        <DropdownMenu>
          <DropdownMenuTrigger
            :class="`cursor-pointer border-0 bg-transparent ${navLinkClass}`"
          >
            {{ aboutLabel }}&nbsp;▾
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="start"
            class="z-[200] min-w-[256px] rounded-[14px] border-none bg-white p-2 font-sans shadow-popover"
          >
            <DropdownMenuItem
              v-for="item in aboutItems"
              :key="item.label"
              as-child
              class="rounded-[9px] px-[15px] py-[11px] text-base font-semibold text-ink focus:bg-cream focus:text-red"
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

      <div class="flex flex-wrap items-center gap-3">
        <LanguageToggle :languages="currentLanguages" />
        <A11yWidget />
        <a
          :href="joinUrl"
          target="_blank"
          rel="noopener"
          :class="`${pillClass} px-[22px] py-2.5`"
        >
          {{ joinLabel }}
        </a>
      </div>
    </div>
  </header>
</template>
