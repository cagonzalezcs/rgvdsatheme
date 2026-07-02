<script setup lang="ts">
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";

const brandColors = [
  { name: "brand-red", hex: "#E9252E", class: "bg-brand-red" },
  { name: "brand-red-deep", hex: "#9E0B13", class: "bg-brand-red-deep" },
  { name: "cream", hex: "#FAF4EA", class: "bg-cream" },
  { name: "ink", hex: "#1C1917", class: "bg-ink" },
  { name: "ink-soft", hex: "#3A352F", class: "bg-ink-soft" },
  { name: "muted-on-ink", hex: "#CFC8BD", class: "bg-muted-on-ink" },
  { name: "muted-on-cream", hex: "#5C544A", class: "bg-muted-on-cream" },
  { name: "muted-2", hex: "#6B6257", class: "bg-muted-2" },
  { name: "border-muted", hex: "#B7AC9B", class: "bg-border-muted" },
  { name: "divider-cream", hex: "#E3D9C6", class: "bg-divider-cream" },
  { name: "cell-outmonth", hex: "#F0E7D6", class: "bg-cell-outmonth" },
];

const categoryColors = [
  { name: "chapter — Chapter-Wide", class: "bg-cat-chapter" },
  { name: "poled — Political Education", class: "bg-cat-poled" },
  { name: "mutual — Mutual Aid", class: "bg-cat-mutual" },
  { name: "labor — Labor", class: "bg-cat-labor" },
  { name: "electoral — Electoral", class: "bg-cat-electoral" },
  { name: "social — Social", class: "bg-cat-social" },
];

const shadows = [
  { name: "shadow-brutal (5px ink)", class: "shadow-brutal" },
  { name: "shadow-brutal-sm (3px ink)", class: "shadow-brutal-sm" },
  { name: "shadow-brutal-md (6px ink)", class: "shadow-brutal-md" },
  { name: "shadow-brutal-red (8px red)", class: "shadow-brutal-red" },
  { name: "shadow-brutal-lg (10px ink)", class: "shadow-brutal-lg" },
];
</script>

<template>
  <div class="styleguide mx-auto max-w-[1100px] px-6 py-16 font-sans text-foreground">
    <header class="mb-12">
      <p class="font-sans text-sm font-extrabold uppercase tracking-[0.18em] text-brand-red">
        RGV DSA Design System
      </p>
      <h1 class="font-display text-5xl font-black uppercase leading-[1.08] tracking-tight">
        Styleguide
      </h1>
    </header>

    <!-- Tokens: colors -->
    <section :id="'sg-colors'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Brand palette
      </h2>
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <div v-for="c in brandColors" :key="c.name" class="border-2 border-ink">
          <div :class="[c.class, 'h-16']" />
          <div class="border-t-2 border-ink bg-white px-2 py-1">
            <p class="text-xs font-bold">{{ c.name }}</p>
            <p class="font-mono text-xs text-muted-on-cream">{{ c.hex }}</p>
          </div>
        </div>
      </div>
      <h3 class="mb-4 mt-8 font-display text-lg font-extrabold uppercase">Event categories</h3>
      <div class="flex flex-wrap gap-3">
        <div
          v-for="c in categoryColors"
          :key="c.name"
          class="flex items-center gap-2 border-2 border-ink bg-white px-3 py-1.5"
        >
          <span :class="[c.class, 'inline-block size-3 border border-ink']" />
          <span class="text-xs font-bold uppercase">{{ c.name }}</span>
        </div>
      </div>
    </section>

    <!-- Tokens: type scale -->
    <section :id="'sg-type'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Type scale
      </h2>
      <div class="space-y-6">
        <p class="font-display text-[clamp(2.3rem,5.8vw,4.4rem)] font-black uppercase leading-[1.08]">
          Hero H1 — Montserrat 900
        </p>
        <p class="font-display text-[clamp(2rem,4.8vw,3.4rem)] font-black uppercase leading-[1.1]">
          Page H1 — Montserrat 900
        </p>
        <p class="font-display text-[clamp(1.8rem,4vw,2.9rem)] font-black uppercase">
          Section H2 — Montserrat 900
        </p>
        <p class="border-b-[3px] border-ink pb-2.5 font-display text-2xl font-extrabold uppercase">
          In-content H2 — Montserrat 800 w/ rule
        </p>
        <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-brand-red">
          Eyebrow / kicker — 800, tracked
        </p>
        <p class="max-w-[60ch] text-lg leading-relaxed">
          Body — Open Sans 400. The Rio Grande Valley deserves an economy and a government that
          put working people first. We organize across four counties.
        </p>
        <p class="font-display text-[0.95rem] font-semibold">Nav link — Montserrat 600</p>
        <p class="text-xs font-extrabold uppercase tracking-[0.12em]">Micro label — 800 tracked</p>
      </div>
    </section>

    <!-- Tokens: shadows -->
    <section :id="'sg-shadows'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Hard shadows
      </h2>
      <div class="flex flex-wrap gap-10">
        <div
          v-for="s in shadows"
          :key="s.name"
          :class="[s.class, 'border-[3px] border-ink bg-white px-6 py-4 text-sm font-bold']"
        >
          {{ s.name }}
        </div>
      </div>
    </section>

    <!-- Components -->
    <section :id="'sg-button'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Button
      </h2>
      <div class="flex flex-wrap items-center gap-6">
        <Button>Default</Button>
        <Button variant="secondary">Secondary</Button>
        <Button variant="destructive">Destructive</Button>
        <Button variant="outline">Outline</Button>
        <Button variant="ghost">Ghost</Button>
        <Button variant="link">Link</Button>
      </div>
      <div class="mt-6 flex flex-wrap items-center gap-6">
        <Button size="sm">Small</Button>
        <Button size="default">Default</Button>
        <Button size="lg">Large</Button>
        <Button disabled>Disabled</Button>
      </div>
    </section>

    <section :id="'sg-badge'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Badge
      </h2>
      <div class="flex flex-wrap items-center gap-4">
        <Badge>Default</Badge>
        <Badge variant="secondary">Secondary</Badge>
        <Badge variant="destructive">Destructive</Badge>
        <Badge variant="outline">Outline</Badge>
      </div>
    </section>

    <section :id="'sg-card'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Card
      </h2>
      <div class="grid gap-10 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle class="font-display uppercase">Plain card</CardTitle>
            <CardDescription>White fill, 3px ink border, no shadow.</CardDescription>
          </CardHeader>
          <CardContent>
            <p>Default card body content.</p>
          </CardContent>
          <CardFooter>
            <Button variant="outline">Action</Button>
          </CardFooter>
        </Card>
        <Card class="shadow-brutal-red">
          <CardHeader>
            <CardTitle class="font-display uppercase">Featured card</CardTitle>
            <CardDescription>8px red hard shadow for callouts.</CardDescription>
          </CardHeader>
          <CardContent>
            <p>Used for grievance callout, photo frames, and featured content.</p>
          </CardContent>
        </Card>
      </div>
    </section>

    <section :id="'sg-form'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Label + Input
      </h2>
      <div class="max-w-sm space-y-3">
        <Label for="sg-email" class="font-display font-bold uppercase">Email</Label>
        <Input id="sg-email" type="email" placeholder="you@example.org" />
      </div>
    </section>

    <section :id="'sg-separator'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Separator
      </h2>
      <p class="text-sm">Above</p>
      <Separator class="my-4" />
      <p class="text-sm">Below</p>
    </section>

    <!-- Tone bands (a11y high-contrast hooks) -->
    <section :id="'sg-tones'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Tone bands
      </h2>
      <div class="space-y-0 border-[3px] border-ink">
        <div data-tone="red" class="bg-brand-red px-6 py-8 text-cream">
          <p class="font-display font-extrabold uppercase">data-tone="red"</p>
        </div>
        <div data-tone="cream" class="bg-cream px-6 py-8 text-ink">
          <p class="font-display font-extrabold uppercase">data-tone="cream"</p>
        </div>
        <div data-tone="ink" class="bg-ink px-6 py-8 text-cream">
          <p class="font-display font-extrabold uppercase">data-tone="ink"</p>
        </div>
      </div>
    </section>
  </div>
</template>
