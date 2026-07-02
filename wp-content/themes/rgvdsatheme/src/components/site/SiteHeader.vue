<script setup lang="ts">
import { Menu } from "lucide-vue-next";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import A11yWidget from "@/components/site/A11yWidget.vue";

interface NavLink {
  label: string;
  href: string;
}

const props = withDefaults(
  defineProps<{
    joinUrl?: string;
    logoUrl?: string;
    homeUrl?: string;
    aboutItems?: NavLink[];
    navItems?: NavLink[];
    currentPath?: string;
    /** Spanish site toggle — a real link only when enabled and a URL exists */
    esEnabled?: boolean;
    esUrl?: string;
  }>(),
  {
    joinUrl: "https://act.dsausa.org/donate/membership",
    logoUrl: "",
    homeUrl: "/",
    esEnabled: false,
    esUrl: "",
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
      { label: "Get Involved", href: "/get-involved/" },
    ],
    currentPath: "",
  },
);

// v2: larger sentence-case nav, rounded translucent-ink hover pill (03-DESIGN-SPEC.md § SiteHeader).
const navLinkClass =
  "rounded-[10px] px-4 py-2.5 font-display text-[1.17rem] font-bold text-white no-underline hover:bg-[rgba(28,25,23,0.18)]";

function isCurrent(href: string): boolean {
  return props.currentPath !== "" && href === props.currentPath;
}
</script>

<template>
  <header
    class="site-header sticky top-0 z-100 bg-brand-red shadow-header [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
    data-tone="red"
  >
    <div
      class="mx-auto flex min-h-[64px] max-w-[1220px] flex-wrap items-center justify-between gap-6 px-6 py-2.5"
    >
      <a :href="homeUrl" aria-label="RGV DSA home" class="flex flex-none items-center">
        <img
          :src="logoUrl"
          alt="Rio Grande Valley Democratic Socialists of America"
          class="block h-[58px] w-auto"
          width="931"
          height="358"
        />
      </a>

      <nav aria-label="Main" class="hidden items-center gap-0.5 lg:flex">
        <DropdownMenu>
          <DropdownMenuTrigger
            :class="`cursor-pointer border-0 bg-transparent ${navLinkClass}`"
          >
            About&nbsp;▾
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="start"
            class="min-w-[256px] rounded-[14px] border-none bg-white p-2 shadow-popover"
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
          :class="[navLinkClass, isCurrent(item.href) ? 'underline decoration-[3px] underline-offset-[6px]' : '']"
          :aria-current="isCurrent(item.href) ? 'page' : undefined"
        >
          {{ item.label }}
        </a>
      </nav>

      <div class="flex flex-wrap items-center gap-2.5">
        <div
          role="group"
          aria-label="Language"
          class="hidden items-center gap-0.5 rounded-full bg-white p-[3px] text-[0.8rem] font-bold tracking-[0.04em] sm:flex"
        >
          <span class="rounded-full bg-red px-3 py-1 text-white">EN</span>
          <a
            v-if="esEnabled && esUrl"
            :href="esUrl"
            lang="es"
            class="rounded-full px-3 py-1 text-red no-underline hover:bg-tint"
            >ES</a
          >
          <span
            v-else
            lang="es"
            class="rounded-full px-3 py-1 text-red/55"
            title="Español — próximamente"
            >ES</span
          >
        </div>

        <A11yWidget />

        <a
          :href="joinUrl"
          target="_blank"
          rel="noopener"
          class="hidden rounded-full bg-white px-[22px] py-2.5 text-[0.95rem] font-bold text-red no-underline hover:text-red-hover hover:shadow-[0_0_0_3px_rgba(28,25,23,0.25)] sm:block"
        >
          Join DSA
        </a>

        <!-- Mobile menu -->
        <Sheet>
          <SheetTrigger
            class="flex cursor-pointer items-center rounded-[10px] bg-transparent p-2 text-white hover:bg-[rgba(28,25,23,0.18)] lg:hidden"
            aria-label="Open menu"
          >
            <Menu class="size-5" />
          </SheetTrigger>
          <SheetContent side="right" class="w-80">
            <SheetHeader>
              <SheetTitle>Menu</SheetTitle>
            </SheetHeader>
            <nav aria-label="Mobile" class="flex flex-col gap-1 px-4">
              <div
                class="px-2 pb-1 pt-3 font-display text-xs font-bold uppercase tracking-[0.12em] text-text-muted"
              >
                About
              </div>
              <a
                v-for="item in aboutItems"
                :key="item.label"
                :href="item.href"
                class="rounded-[10px] px-2 py-2 font-display text-base font-semibold text-ink no-underline hover:bg-tint hover:text-red"
              >
                {{ item.label }}
              </a>
              <div class="my-2 border-t border-hairline" />
              <a
                v-for="item in navItems"
                :key="item.label"
                :href="item.href"
                class="rounded-[10px] px-2 py-2 font-display text-base font-semibold text-ink no-underline hover:bg-tint hover:text-red"
                :aria-current="isCurrent(item.href) ? 'page' : undefined"
              >
                {{ item.label }}
              </a>
              <a
                :href="joinUrl"
                target="_blank"
                rel="noopener"
                class="mt-4 rounded-full bg-red px-5 py-3 text-center text-[0.95rem] font-bold text-white no-underline hover:bg-red-hover"
              >
                Join DSA
              </a>
            </nav>
          </SheetContent>
        </Sheet>
      </div>
    </div>
  </header>
</template>
