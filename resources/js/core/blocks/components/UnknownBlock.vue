<script setup lang="ts">
defineProps<{
    blockId: string;
    blockType: string;
    reason: string;
}>();
</script>

<template>
    <section
        :id="`block-${blockId}`"
        class="wb-card border-l-4 border-l-[var(--wb-color-warning)] bg-[var(--wb-color-surface)] p-6"
    >
        <h2 class="text-lg font-semibold text-[var(--wb-color-text)]">
            Unsupported content block
        </h2>
        <p class="mt-2 text-sm text-[var(--wb-color-text-muted)]">
            Block type <code class="font-mono">{{ blockType }}</code> could not be
            rendered because {{ reason }}.
        </p>
    </section>
</template>
