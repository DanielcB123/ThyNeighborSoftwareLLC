<script setup lang="ts">
import type { RichTextBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: RichTextBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card p-8">
        <h2 class="text-2xl font-semibold text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <div class="mt-4 space-y-4">
            <p
                v-for="paragraph in data.paragraphs"
                :key="paragraph"
                class="text-base leading-7 text-[var(--wb-color-text-muted)]"
            >
                {{ paragraph }}
            </p>
        </div>
    </section>
</template>
