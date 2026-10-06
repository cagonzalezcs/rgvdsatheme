<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import { MenuIcon } from "@lucide/vue";
import {
  Sheet,
  SheetContent,
  SheetFooter,
  SheetTrigger,
} from "@/components/ui/sheet";
import SiteHeaderActions from "./SiteHeaderActions.vue";
</script>

<template>
  <Sheet class="site-header-mobile-nav">
    <SheetTrigger as-child class="site-header-mobile-nav__trigger">
      <Button variant="outline" class="rounded-md">
        <MenuIcon />
      </Button>
    </SheetTrigger>
    <SheetContent class="site-header-mobile-nav__content">
      <div class="grid flex-1 auto-rows-min gap-6 px-4"></div>
      <SheetFooter>
        <SiteHeaderActions />
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
