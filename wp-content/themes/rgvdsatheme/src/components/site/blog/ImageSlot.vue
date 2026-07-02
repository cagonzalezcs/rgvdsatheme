<script setup lang="ts">
withDefaults(
  defineProps<{
    src?: string | null;
    alt?: string;
    /** mono label shown on the striped placeholder */
    label?: string;
  }>(),
  { src: null, alt: "", label: "" },
);
</script>

<template>
  <img v-if="src" :src="src" :alt="alt" class="image-slot block size-full object-cover" />
  <div
    v-else
    aria-hidden="true"
    class="image-slot flex size-full items-center justify-center bg-[repeating-linear-gradient(45deg,var(--color-stripe-v2-a)_0_14px,var(--color-stripe-v2-b)_14px_28px)]"
  >
    <span v-if="label" class="font-mono text-[0.78rem] text-text-muted">{{ label }}</span>
  </div>
</template>
