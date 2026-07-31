<script setup lang="ts">
import type { CTASectionBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: CTASectionBlockData;
}>();
</script>

<template>
    <section
        :id="`block-${blockId}`"
        class="wb-card wb-card--cta p-8 lg:p-10"
    >
        <h2 class="text-3xl font-semibold leading-tight text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <p class="mt-3 max-w-3xl text-base leading-7 text-[var(--wb-color-text-muted)]">
            {{ data.description }}
        </p>
        <a
            :href="data.actionPath"
            class="mt-6 inline-flex items-center rounded-[var(--wb-radius-sm)] bg-[var(--wb-color-secondary)] px-5 py-3 text-sm font-semibold tracking-[0.02em] text-[var(--wb-color-primary-contrast)] transition hover:-translate-y-0.5 hover:opacity-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--wb-color-secondary)]"
        >
            {{ data.actionLabel }}
        </a>
    </section>
</template>
