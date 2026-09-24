<script setup lang="ts">
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";
import type { MenuItem } from "@/components/site/SiteHeader/site-header.typings.ts";

const props = defineProps<{
  menuItems: MenuItem[];
}>();

function flatMenuItemChildren(menuItem: MenuItem): MenuItem[] {
  let menuItemChildren: MenuItem[] = [];

  menuItem.children.forEach((menuItemChild) => {
    menuItemChildren.push(menuItemChild);

    if (menuItemChild.children?.length > 0) {
      menuItemChildren = menuItemChildren.concat(
        flatMenuItemChildren(menuItemChild),
      );
    }
  });

  return menuItemChildren;
}
</script>

<template>
  <NavigationMenu class="site-header-nav">
    <NavigationMenuList class="site-header-nav__list">
      <NavigationMenuItem
        v-for="menuItem in props.menuItems"
        :key="menuItem.id"
        class="site-header-nav__item"
      >
        <NavigationMenuTrigger
          v-if="menuItem.children.length > 0"
          class="site-header-nav__trigger"
        >
          {{ menuItem.title }}
        </NavigationMenuTrigger>
        <NavigationMenuLink
          v-else
          :href="menuItem.url"
          :target="menuItem.target"
          class="site-header-nav__link"
        >
          {{ menuItem.title }}
        </NavigationMenuLink>
        <NavigationMenuContent
          v-if="menuItem.children.length > 0"
          class="site-header-nav__submenu"
        >
          <NavigationMenuLink
            v-for="menuItemChild in flatMenuItemChildren(menuItem)"
            :key="menuItemChild.id"
            :href="menuItem.url"
            :target="menuItem.target"
            class="site-header-nav__submenu-link"
          >
            {{ menuItemChild.title }}
          </NavigationMenuLink>
        </NavigationMenuContent>
      </NavigationMenuItem>
    </NavigationMenuList>
  </NavigationMenu>
</template>
