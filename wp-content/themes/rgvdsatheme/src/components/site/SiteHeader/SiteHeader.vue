<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import SiteHeaderNav from "@/components/site/SiteHeader/SiteHeaderNav.vue";
import type { SiteHeaderProps } from "@/components/site/SiteHeader/site-header.typings.ts";

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
      <div class="site-header-actions">
        <div class="site-header-actions__lang">
          <a
            href="#"
            class="site-header-actions__lang-link site-header-actions__lang-link--current"
          >
            EN
          </a>
          <a href="#" class="site-header-actions__lang-link">ES</a>
        </div>
        <Button variant="secondary">Aa</Button>
        <Button as-child variant="secondary">
          <a href="https://act.dsausa.org/donate/membership">Join DSA</a>
        </Button>
      </div>
    </div>
  </header>
</template>
