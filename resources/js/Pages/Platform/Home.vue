<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import BlockRenderer from "@/core/blocks/BlockRenderer.vue";
import type { RawContentBlock } from "@/core/blocks/types";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

defineProps<{
    blocks: readonly RawContentBlock[];
}>();
</script>

<template>
    <Head title="Platform Home" />
    <SurfaceShell
        page-title="Enterprise-ready software delivery for every stage of growth"
        page-summary="WeBuildYouThrive combines platform architecture, implementation services, and long-term operational support."
    >
        <BlockRenderer :blocks="blocks" />
    </SurfaceShell>
</template>
