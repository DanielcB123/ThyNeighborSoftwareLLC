<script setup lang="ts">
import type { FeatureGridBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: FeatureGridBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card p-8">
        <h2 class="text-2xl font-semibold text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <p class="mt-3 text-base text-[var(--wb-color-text-muted)]">
            {{ data.intro }}
        </p>
        <ul class="mt-6 grid gap-4 md:grid-cols-2">
            <li
                v-for="feature in data.features"
                :key="feature.title"
                class="rounded-md border border-[var(--wb-color-border)] bg-[var(--wb-color-surface-muted)] p-4"
            >
                <p v-if="feature.label" class="wb-pill mb-2">
                    {{ feature.label }}
                </p>
                <h3 class="text-base font-semibold text-[var(--wb-color-text)]">
                    {{ feature.title }}
                </h3>
                <p class="mt-2 text-sm text-[var(--wb-color-text-muted)]">
                    {{ feature.description }}
                </p>
            </li>
        </ul>
    </section>
</template>
