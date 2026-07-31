<script setup lang="ts">
import type { RichTextBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: RichTextBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-card p-8 lg:p-10">
        <h2 class="text-3xl font-semibold text-[var(--wb-color-text)]">
            {{ data.heading }}
        </h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <p
                v-for="paragraph in data.paragraphs"
                :key="paragraph"
                class="rounded-[var(--wb-radius-sm)] border border-[var(--wb-color-border)] bg-white/70 p-4 text-base leading-7 text-[var(--wb-color-text-muted)]"
            >
                {{ paragraph }}
            </p>
        </div>
    </section>
</template>
