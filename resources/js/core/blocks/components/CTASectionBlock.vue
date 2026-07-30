<script setup lang="ts">
import type { CTASectionBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: CTASectionBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-section-frame wb-cta-band">
        <h2 class="wb-heading-2 text-balance">
            {{ data.heading }}
        </h2>
        <p class="wb-body-m mt-3 max-w-3xl text-pretty text-[var(--color-text-muted)]">
            {{ data.description }}
        </p>
        <a
            :href="data.actionPath"
            class="wb-button wb-button--accent mt-7"
        >
            {{ data.actionLabel }}
        </a>
    </section>
</template>
