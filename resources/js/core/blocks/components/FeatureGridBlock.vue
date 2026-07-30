<script setup lang="ts">
import type { FeatureGridBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: FeatureGridBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-section-frame">
        <h2 class="wb-heading-2 text-balance">
            {{ data.heading }}
        </h2>
        <p class="wb-body-m mt-4 max-w-3xl text-pretty text-[var(--color-text-muted)]">
            {{ data.intro }}
        </p>
        <ul class="mt-8 grid gap-4 md:grid-cols-2">
            <li
                v-for="feature in data.features"
                :key="feature.title"
                class="wb-feature-card"
            >
                <div class="wb-feature-card__media" aria-hidden="true">
                    <span class="wb-feature-card__texture" />
                </div>
                <p v-if="feature.label" class="wb-eyebrow mt-4">
                    {{ feature.label }}
                </p>
                <h3 class="wb-heading-3 mt-3">
                    {{ feature.title }}
                </h3>
                <p class="wb-body-s mt-2 text-pretty text-[var(--color-text-muted)]">
                    {{ feature.description }}
                </p>
            </li>
        </ul>
    </section>
</template>
