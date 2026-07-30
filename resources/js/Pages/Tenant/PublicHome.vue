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
    <Head title="Tenant Public Home" />
    <SurfaceShell
        page-title="Customer-ready web experiences powered by tenant-owned domains"
        page-summary="This tenant website uses typed block rendering, tenant theming, and capability-safe runtime contracts."
    >
        <BlockRenderer :blocks="blocks" />
    </SurfaceShell>
</template>
