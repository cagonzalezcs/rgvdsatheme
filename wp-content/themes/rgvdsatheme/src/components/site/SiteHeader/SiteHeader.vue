<script lang="ts" setup>
import SiteHeaderNav from "@/components/site/SiteHeader/SiteHeaderNav.vue";
import type { SiteHeaderProps } from "@/components/site/SiteHeader/site-header.typings.ts";
import SiteHeaderActions from "@/components/site/SiteHeader/SiteHeaderActions.vue";
import SiteHeaderMobileNav from "@/components/site/SiteHeader/SiteHeaderMobileNav.vue";

const props = defineProps<SiteHeaderProps>();
</script>

<template>
  <header class="site-header">
    <div class="site-header__wrapper">
      <a href="/" class="site-header__logo">
        <img
          src="/static/images/temp/logo-header.svg"
          alt="Rio Grande Valley Democratic Socialists of America"
        />
      </a>
      <SiteHeaderNav :menu-items="props.menuItems" />
      <SiteHeaderActions />
      <SiteHeaderMobileNav />
    </div>
  </header>
</template>
