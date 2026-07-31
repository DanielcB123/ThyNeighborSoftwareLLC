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
        class="wb-section-frame border-l-4 border-l-[var(--color-warning)] p-6"
    >
        <h2 class="wb-heading-3">
            Unsupported content block
        </h2>
        <p class="wb-body-s mt-2 text-[var(--color-text-muted)]">
            Block type <code class="font-mono">{{ blockType }}</code> could not be
            rendered because {{ reason }}.
        </p>
    </section>
</template>
