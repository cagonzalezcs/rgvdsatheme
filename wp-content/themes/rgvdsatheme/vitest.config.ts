import { defineConfig } from "vitest/config";

/* Standalone config — vite.config.js's plugins (live-reload, vite-for-wp,
 * dev-server https) are WP-runtime concerns that have no place in unit
 * tests, and the wordpress/ WorDBless install must never be scanned. */
export default defineConfig({
  test: {
    include: ["tests/**/*.test.ts"],
  },
});
