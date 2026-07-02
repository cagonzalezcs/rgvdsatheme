<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { AspectRatio } from "@/components/ui/aspect-ratio";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { Kbd, KbdGroup } from "@/components/ui/kbd";
import { Label } from "@/components/ui/label";
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";
import { Progress } from "@/components/ui/progress";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Separator } from "@/components/ui/separator";
import { Skeleton } from "@/components/ui/skeleton";
import { Slider } from "@/components/ui/slider";
import { Switch } from "@/components/ui/switch";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { Toggle } from "@/components/ui/toggle";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";

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

const sampleEvents = [
  { event: "Brake Light Clinic", date: "Jul 12", location: "McAllen" },
  { event: "ABCs of Socialism", date: "Jul 19", location: "Edinburg" },
  { event: "General Meeting", date: "Jul 26", location: "Harlingen" },
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

    <section :id="'sg-alert'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Alert
      </h2>
      <div class="max-w-xl space-y-6">
        <Alert>
          <AlertTitle>General meeting moved</AlertTitle>
          <AlertDescription>
            July's general meeting is now at the McAllen public library, room B.
          </AlertDescription>
        </Alert>
        <Alert variant="destructive">
          <AlertTitle>Action cancelled</AlertTitle>
          <AlertDescription>
            Saturday's brake light clinic is cancelled due to weather.
          </AlertDescription>
        </Alert>
      </div>
    </section>

    <section :id="'sg-aspect-ratio'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Aspect ratio
      </h2>
      <div class="max-w-md">
        <AspectRatio
          :ratio="16 / 9"
          class="flex items-center justify-center border-2 border-ink bg-[repeating-linear-gradient(45deg,var(--color-stripe-a),var(--color-stripe-a)_12px,var(--color-stripe-b)_12px,var(--color-stripe-b)_24px)]"
        >
          <span class="font-display text-sm font-extrabold uppercase">16 : 9</span>
        </AspectRatio>
      </div>
    </section>

    <section :id="'sg-avatar'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Avatar
      </h2>
      <div class="flex items-center gap-6">
        <Avatar class="size-12">
          <AvatarImage src="https://github.com/shadcn.png" alt="Member avatar" />
          <AvatarFallback>RG</AvatarFallback>
        </Avatar>
        <Avatar class="size-12">
          <AvatarFallback>VD</AvatarFallback>
        </Avatar>
      </div>
    </section>

    <section :id="'sg-breadcrumb'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Breadcrumb
      </h2>
      <Breadcrumb>
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink href="#">Home</BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbLink href="#">Events</BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Brake Light Clinic</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>
    </section>

    <section :id="'sg-checkbox'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Checkbox
      </h2>
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <Checkbox id="sg-check-1" />
          <Label for="sg-check-1">Get chapter emails</Label>
        </div>
        <div class="flex items-center gap-3">
          <Checkbox id="sg-check-2" :default-value="true" />
          <Label for="sg-check-2">Agreed to code of conduct</Label>
        </div>
        <div class="flex items-center gap-3">
          <Checkbox id="sg-check-3" disabled />
          <Label for="sg-check-3" class="opacity-50">Dues autopay (coming soon)</Label>
        </div>
      </div>
    </section>

    <section :id="'sg-kbd'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Kbd
      </h2>
      <div class="flex items-center gap-8">
        <Kbd>Esc</Kbd>
        <KbdGroup>
          <Kbd>Ctrl</Kbd>
          <Kbd>K</Kbd>
        </KbdGroup>
      </div>
    </section>

    <section :id="'sg-pagination'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Pagination
      </h2>
      <Pagination v-slot="{ page }" :items-per-page="10" :total="50" :default-page="2">
        <PaginationContent v-slot="{ items }">
          <PaginationPrevious />
          <template v-for="(item, index) in items" :key="index">
            <PaginationItem
              v-if="item.type === 'page'"
              :value="item.value"
              :is-active="item.value === page"
            >
              {{ item.value }}
            </PaginationItem>
            <PaginationEllipsis v-else :index="index" />
          </template>
          <PaginationNext />
        </PaginationContent>
      </Pagination>
    </section>

    <section :id="'sg-progress'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Progress
      </h2>
      <div class="max-w-md space-y-2">
        <p class="text-xs font-extrabold uppercase tracking-[0.12em]">Dues drive — 40%</p>
        <Progress :model-value="40" />
      </div>
    </section>

    <section :id="'sg-radio-group'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Radio group
      </h2>
      <RadioGroup default-value="mcallen">
        <div class="flex items-center gap-3">
          <RadioGroupItem id="sg-radio-1" value="mcallen" />
          <Label for="sg-radio-1">McAllen</Label>
        </div>
        <div class="flex items-center gap-3">
          <RadioGroupItem id="sg-radio-2" value="brownsville" />
          <Label for="sg-radio-2">Brownsville</Label>
        </div>
        <div class="flex items-center gap-3">
          <RadioGroupItem id="sg-radio-3" value="edinburg" />
          <Label for="sg-radio-3">Edinburg</Label>
        </div>
      </RadioGroup>
    </section>

    <section :id="'sg-skeleton'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Skeleton
      </h2>
      <div class="flex max-w-sm items-start gap-4">
        <Skeleton class="size-12 shrink-0" />
        <div class="w-full space-y-3">
          <Skeleton class="h-4 w-3/4" />
          <Skeleton class="h-24 w-full" />
        </div>
      </div>
    </section>

    <section :id="'sg-slider'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Slider
      </h2>
      <div class="max-w-md">
        <Slider :default-value="[40]" :max="100" :step="5" aria-label="Pledge amount" />
      </div>
    </section>

    <section :id="'sg-switch'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Switch
      </h2>
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <Switch id="sg-switch-1" />
          <Label for="sg-switch-1">Event reminders</Label>
        </div>
        <div class="flex items-center gap-3">
          <Switch id="sg-switch-2" :default-value="true" />
          <Label for="sg-switch-2">Newsletter</Label>
        </div>
      </div>
    </section>

    <section :id="'sg-table'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Table
      </h2>
      <Table class="border-2 border-ink bg-white">
        <TableHeader>
          <TableRow>
            <TableHead>Event</TableHead>
            <TableHead>Date</TableHead>
            <TableHead>Location</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="row in sampleEvents" :key="row.event">
            <TableCell class="font-medium">{{ row.event }}</TableCell>
            <TableCell>{{ row.date }}</TableCell>
            <TableCell>{{ row.location }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </section>

    <section :id="'sg-textarea'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Textarea
      </h2>
      <div class="max-w-sm space-y-3">
        <Label for="sg-message" class="font-display font-bold uppercase">Message</Label>
        <Textarea id="sg-message" placeholder="Tell us about your workplace…" />
      </div>
    </section>

    <section :id="'sg-toggle'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Toggle
      </h2>
      <div class="flex items-center gap-6">
        <Toggle aria-label="Toggle off example">Off</Toggle>
        <Toggle :default-value="true" aria-label="Toggle pressed example">Pressed</Toggle>
      </div>
    </section>

    <section :id="'sg-toggle-group'" class="mb-16">
      <h2 class="mb-6 border-b-[3px] border-ink pb-2 font-display text-2xl font-extrabold uppercase">
        Toggle group
      </h2>
      <ToggleGroup type="single" default-value="month" variant="outline">
        <ToggleGroupItem value="month">Month</ToggleGroupItem>
        <ToggleGroupItem value="week">Week</ToggleGroupItem>
        <ToggleGroupItem value="list">List</ToggleGroupItem>
      </ToggleGroup>
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
