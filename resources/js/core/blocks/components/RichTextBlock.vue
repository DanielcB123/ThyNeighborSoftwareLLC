<script setup lang="ts">
import type { RichTextBlockData } from "@/core/blocks/types";

defineProps<{
    blockId: string;
    data: RichTextBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-section-frame wb-rich-text">
        <h2 class="wb-heading-2 max-w-3xl text-balance">
            {{ data.heading }}
        </h2>
        <div class="mt-7 grid gap-6 md:grid-cols-2">
            <p
                v-for="paragraph in data.paragraphs"
                :key="paragraph"
                class="wb-body-m text-pretty text-[var(--color-text-muted)]"
            >
                {{ paragraph }}
            </p>
        </div>
    </section>
</template>
