<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import { MenuIcon } from "@lucide/vue";
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
</script>

<template>
  <Sheet class="site-header-mobile-nav">
    <SheetTrigger as-child class="site-header-mobile-nav__trigger">
      <Button variant="outline" class="rounded-md">
        <MenuIcon />
      </Button>
    </SheetTrigger>
    <SheetContent
      class="w-full max-w-full sm:max-w-full site-header-mobile-nav__content"
    >
      <SheetHeader>
        <SheetTitle>Edit profile</SheetTitle>
        <SheetDescription>
          Make changes to your profile here. Click save when you're done.
        </SheetDescription>
      </SheetHeader>
      <div class="grid flex-1 auto-rows-min gap-6 px-4"></div>
      <SheetFooter>
        <Button type="submit"> Save changes </Button>
        <SheetClose as-child>
          <Button variant="outline"> Close </Button>
        </SheetClose>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
