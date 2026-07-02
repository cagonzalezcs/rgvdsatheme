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
      { label: "About the Chapter", href: "/about/#chapter" },
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

const navLinkClass =
  "px-3.5 py-2.5 font-display text-[0.95rem] font-semibold tracking-[0.02em] text-cream no-underline hover:underline hover:underline-offset-4";

function isCurrent(href: string): boolean {
  return props.currentPath !== "" && href === props.currentPath;
}
</script>

<template>
  <header
    class="site-header sticky top-0 z-100 border-b-[3px] border-ink bg-brand-red [.admin-bar_&]:top-[var(--wp-admin--admin-bar--height,32px)]"
    data-tone="red"
  >
    <div
      class="mx-auto flex min-h-[76px] max-w-[1200px] items-center justify-between gap-6 px-6"
    >
      <a :href="homeUrl" aria-label="RGV DSA home" class="flex flex-none items-center">
        <img
          :src="logoUrl"
          alt="Rio Grande Valley Democratic Socialists of America"
          class="block h-[62px] w-auto"
          width="931"
          height="358"
        />
      </a>

      <nav aria-label="Main" class="hidden items-center gap-1 lg:flex">
        <DropdownMenu>
          <DropdownMenuTrigger
            class="cursor-pointer border-0 bg-transparent px-3.5 py-2.5 font-display text-[0.95rem] font-semibold tracking-[0.02em] text-cream hover:underline hover:underline-offset-4"
          >
            About&nbsp;▾
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="min-w-[240px] py-1.5">
            <DropdownMenuItem
              v-for="item in aboutItems"
              :key="item.label"
              as-child
              class="px-[18px] py-2.5 font-display text-[0.95rem] font-semibold focus:bg-brand-red-deep focus:text-white"
            >
              <a
                :href="item.href"
                class="block cursor-pointer text-ink no-underline hover:text-white focus:text-white"
                >{{ item.label }}</a
              >
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          :class="[navLinkClass, isCurrent(item.href) ? 'underline underline-offset-4' : '']"
          :aria-current="isCurrent(item.href) ? 'page' : undefined"
        >
          {{ item.label }}
        </a>
      </nav>

      <div class="flex items-center gap-2.5">
        <div
          aria-label="Language"
          class="hidden items-center border-2 border-cream text-[0.8rem] font-extrabold tracking-[0.05em] sm:flex"
        >
          <span class="bg-cream px-2.5 py-[5px] text-brand-red">EN</span>
          <a
            v-if="esEnabled && esUrl"
            :href="esUrl"
            lang="es"
            class="px-2.5 py-[5px] text-cream no-underline hover:bg-cream hover:text-brand-red"
            >ES</a
          >
          <span v-else class="px-2.5 py-[5px] text-cream opacity-65" title="Español — próximamente"
            >ES</span
          >
        </div>

        <A11yWidget />

        <a
          :href="joinUrl"
          target="_blank"
          rel="noopener"
          class="hidden border-2 border-cream bg-cream px-5 py-2.5 text-[0.95rem] font-extrabold uppercase tracking-[0.04em] text-brand-red no-underline hover:border-brand-red-deep hover:bg-brand-red-deep hover:text-white sm:block"
        >
          Join DSA
        </a>

        <!-- Mobile menu -->
        <Sheet>
          <SheetTrigger
            class="flex cursor-pointer items-center border-2 border-cream bg-transparent p-2 text-cream hover:bg-brand-red-deep lg:hidden"
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
                class="px-2 pb-1 pt-3 font-display text-xs font-extrabold uppercase tracking-[0.12em] text-muted-on-cream"
              >
                About
              </div>
              <a
                v-for="item in aboutItems"
                :key="item.label"
                :href="item.href"
                class="px-2 py-2 font-display text-base font-semibold text-ink no-underline hover:bg-brand-red-deep hover:text-white"
              >
                {{ item.label }}
              </a>
              <div class="my-2 border-t-2 border-ink" />
              <a
                v-for="item in navItems"
                :key="item.label"
                :href="item.href"
                class="px-2 py-2 font-display text-base font-semibold text-ink no-underline hover:bg-brand-red-deep hover:text-white"
                :aria-current="isCurrent(item.href) ? 'page' : undefined"
              >
                {{ item.label }}
              </a>
              <a
                :href="joinUrl"
                target="_blank"
                rel="noopener"
                class="mt-4 border-[3px] border-ink bg-brand-red px-5 py-3 text-center text-[0.95rem] font-extrabold uppercase tracking-[0.04em] text-cream no-underline shadow-brutal hover:bg-brand-red-deep"
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
