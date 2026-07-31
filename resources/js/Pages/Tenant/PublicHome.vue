<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import BlockRenderer from "@/core/blocks/BlockRenderer.vue";
import type { RawContentBlock } from "@/core/blocks/types";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

const props = defineProps<{
    blocks: readonly RawContentBlock[];
    pageMeta?: {
        title: string;
        summary?: string;
        headTitle?: string;
    };
}>();

const resolvedPageMeta = computed(() => ({
    title:
        props.pageMeta?.title ??
        "Customer-ready web experiences powered by tenant-owned domains",
    summary:
        props.pageMeta?.summary ??
        "This tenant website uses typed block rendering, tenant theming, and capability-safe runtime contracts.",
    headTitle: props.pageMeta?.headTitle ?? "Tenant Public",
}));
</script>

<template>
    <Head :title="resolvedPageMeta.headTitle" />
    <SurfaceShell
        :page-title="resolvedPageMeta.title"
        :page-summary="resolvedPageMeta.summary"
    >
        <BlockRenderer :blocks="blocks" />
    </SurfaceShell>
</template>
