<script setup lang="ts">
import type { HeroBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: HeroBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card p-8 md:p-12">
        <p class="wb-pill mb-4">
            {{ data.eyebrow }}
        </p>
        <h2 class="text-3xl font-bold leading-tight text-[var(--wb-color-text)] md:text-4xl">
            {{ data.heading }}
        </h2>
        <p class="mt-4 max-w-3xl text-base text-[var(--wb-color-text-muted)]">
            {{ data.supportingText }}
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a
                :href="data.primaryActionPath"
                class="inline-flex items-center rounded-md bg-[var(--wb-color-primary)] px-5 py-3 text-sm font-semibold text-[var(--wb-color-primary-contrast)]"
            >
                {{ data.primaryActionLabel }}
            </a>
            <a
                v-if="data.secondaryActionLabel && data.secondaryActionPath"
                :href="data.secondaryActionPath"
                class="inline-flex items-center rounded-md border border-[var(--wb-color-border)] bg-[var(--wb-color-surface)] px-5 py-3 text-sm font-semibold text-[var(--wb-color-text)]"
            >
                {{ data.secondaryActionLabel }}
            </a>
        </div>
    </section>
</template>
