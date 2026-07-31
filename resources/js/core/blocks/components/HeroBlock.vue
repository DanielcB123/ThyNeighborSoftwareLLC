<script setup lang="ts">
import type { HeroBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: HeroBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card wb-card--hero p-8 md:p-12 lg:p-14">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-end">
            <div>
                <p class="wb-pill mb-5">
                    {{ data.eyebrow }}
                </p>
                <h2 class="text-balance text-4xl font-semibold leading-[1.05] text-[var(--wb-color-text)] md:text-5xl lg:text-6xl">
                    {{ data.heading }}
                </h2>
                <p class="mt-6 max-w-3xl text-base leading-7 text-[var(--wb-color-text-muted)] md:text-lg">
                    {{ data.supportingText }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        :href="data.primaryActionPath"
                        class="inline-flex items-center rounded-[var(--wb-radius-sm)] bg-[var(--wb-color-primary)] px-5 py-3 text-sm font-semibold tracking-[0.02em] text-[var(--wb-color-primary-contrast)] transition hover:-translate-y-0.5 hover:shadow-[var(--wb-shadow-focus)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--wb-color-primary)]"
                    >
                        {{ data.primaryActionLabel }}
                    </a>
                    <a
                        v-if="data.secondaryActionLabel && data.secondaryActionPath"
                        :href="data.secondaryActionPath"
                        class="inline-flex items-center rounded-[var(--wb-radius-sm)] border border-[var(--wb-color-border-strong)] bg-[var(--wb-color-surface)] px-5 py-3 text-sm font-semibold text-[var(--wb-color-text)] transition hover:-translate-y-0.5 hover:bg-[var(--wb-color-surface-muted)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--wb-color-secondary)]"
                    >
                        {{ data.secondaryActionLabel }}
                    </a>
                </div>
            </div>
            <aside
                v-if="data.highlights?.length"
                class="rounded-[var(--wb-radius-md)] border border-[var(--wb-color-border)] bg-white/80 p-6"
            >
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--wb-color-text-muted)]">
                    Operating Lens
                </h3>
                <ul class="mt-4 space-y-3">
                    <li
                        v-for="highlight in data.highlights"
                        :key="highlight"
                        class="flex gap-2 text-sm text-[var(--wb-color-text)]"
                    >
                        <span class="mt-1 block size-2 shrink-0 rounded-full bg-[var(--wb-color-secondary)]" />
                        <span>{{ highlight }}</span>
                    </li>
                </ul>
            </aside>
        </div>
    </section>
</template>
