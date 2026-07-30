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
        class="wb-card border-l-4 border-l-[var(--wb-color-secondary)] p-8"
    >
        <h2 class="text-2xl font-semibold text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <p class="mt-3 text-base text-[var(--wb-color-text-muted)]">
            {{ data.description }}
        </p>
        <a
            :href="data.actionPath"
            class="mt-5 inline-flex items-center rounded-md bg-[var(--wb-color-secondary)] px-5 py-3 text-sm font-semibold text-white"
        >
            {{ data.actionLabel }}
        </a>
    </section>
</template>
