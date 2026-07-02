<script setup lang="ts">
interface NavLink {
  label: string;
  href: string;
  external?: boolean;
}

interface FooterColumn {
  title: string;
  links: NavLink[];
}

withDefaults(
  defineProps<{
    logoUrl?: string;
    tagline?: string;
    columns?: FooterColumn[];
    orgName?: string;
    a11yContactHref?: string;
  }>(),
  {
    logoUrl: "",
    tagline: "Organizing across Hidalgo, Cameron, Willacy, and Starr counties.",
    columns: () => [
      {
        title: "About",
        links: [
          { label: "About the Chapter", href: "/about/#chapter" },
          { label: "Mission & History", href: "/about/#mission" },
          { label: "Counties We Serve", href: "/about/#counties" },
          { label: "Bylaws & Code of Conduct", href: "/about/#bylaws" },
          { label: "FAQ", href: "/about/#faq" },
        ],
      },
      {
        title: "Get involved",
        links: [
          {
            label: "Join DSA",
            href: "https://act.dsausa.org/donate/membership",
            external: true,
          },
          { label: "Event Calendar", href: "/calendar/" },
          { label: "Committees", href: "/get-involved/#committees" },
          { label: "Communication Channels", href: "/get-involved/#channels" },
        ],
      },
      {
        title: "Resources",
        links: [
          { label: "Documents & Minutes", href: "/bylaws-code-of-conduct/#documents" },
          { label: "Resolutions", href: "/bylaws-code-of-conduct/" },
          { label: "Education Library", href: "/bylaws-code-of-conduct/" },
          { label: "Grievance Contact", href: "/bylaws-code-of-conduct/#grievance" },
        ],
      },
      {
        title: "Contact",
        links: [
          { label: "Email", href: "mailto:hello@example.org" },
          {
            label: "Instagram",
            href: "https://www.instagram.com/dsa_rgv/",
            external: true,
          },
        ],
      },
    ],
    orgName: "Rio Grande Valley Democratic Socialists of America",
    a11yContactHref: "mailto:hello@example.org",
  },
);
</script>

<template>
  <footer
    class="site-footer border-t border-hairline bg-white pt-16"
    data-tone="cream"
  >
    <div
      class="mx-auto grid max-w-[1140px] gap-10 px-6 pb-14 sm:[grid-template-columns:minmax(240px,1.2fr)_repeat(auto-fit,minmax(160px,1fr))]"
    >
      <div class="flex flex-col gap-4">
        <img
          :src="logoUrl"
          alt="Rio Grande Valley Democratic Socialists of America"
          class="block h-auto w-[200px]"
          width="931"
          height="358"
        />
        <p class="m-0 max-w-[32ch] text-[0.95rem] leading-relaxed text-text-muted">
          {{ tagline }}
        </p>
      </div>

      <nav
        v-for="col in columns"
        :key="col.title"
        :aria-label="col.title"
        class="flex flex-col gap-2.5"
      >
        <div
          class="font-display text-[0.9rem] font-bold uppercase tracking-[0.06em] text-text-muted"
        >
          {{ col.title }}
        </div>
        <a
          v-for="link in col.links"
          :key="link.label"
          :href="link.href"
          :target="link.external ? '_blank' : undefined"
          :rel="link.external ? 'noopener' : undefined"
          class="text-[0.95rem] text-ink no-underline hover:text-red hover:underline hover:underline-offset-[3px]"
        >
          {{ link.label }}
        </a>
      </nav>
    </div>

    <div data-tone="ink" class="bg-ink px-6 py-[18px] text-muted-on-ink">
      <div
        class="mx-auto flex max-w-[1140px] flex-wrap justify-between gap-4 text-[0.85rem]"
      >
        <span>{{ orgName }}</span>
        <span>
          Built to be accessible —
          <a :href="a11yContactHref" class="font-bold text-white hover:text-pink">
            tell us how we can do better
          </a>
        </span>
      </div>
    </div>
  </footer>
</template>
