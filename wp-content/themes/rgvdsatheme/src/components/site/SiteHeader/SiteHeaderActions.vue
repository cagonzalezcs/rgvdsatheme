<script lang="ts" setup>
import { Button } from "@/components/ui/button";
</script>

<template>
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
</template>
