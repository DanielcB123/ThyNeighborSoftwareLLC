<script setup lang="ts">
import type { FeatureGridBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: FeatureGridBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card p-8 lg:p-10">
        <h2 class="text-3xl font-semibold text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <p class="mt-3 max-w-3xl text-base leading-7 text-[var(--wb-color-text-muted)]">
            {{ data.intro }}
        </p>
        <ul class="mt-7 grid gap-4 md:grid-cols-2">
            <li
                v-for="feature in data.features"
                :key="feature.title"
                class="group rounded-[var(--wb-radius-sm)] border border-[var(--wb-color-border)] bg-[var(--wb-color-surface-muted)] p-5 transition hover:-translate-y-0.5 hover:border-[var(--wb-color-border-strong)]"
            >
                <p v-if="feature.label" class="wb-pill mb-2">
                    {{ feature.label }}
                </p>
                <h3 class="text-lg font-semibold text-[var(--wb-color-text)]">
                    {{ feature.title }}
                </h3>
                <p class="mt-2 text-sm leading-6 text-[var(--wb-color-text-muted)]">
                    {{ feature.description }}
                </p>
            </li>
        </ul>
    </section>
</template>
