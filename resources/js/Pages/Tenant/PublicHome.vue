<script setup lang="ts">
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";
import BlockRenderer from "@/core/blocks/BlockRenderer.vue";
import type { RawContentBlock } from "@/core/blocks/types";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

interface TenantPageCopy {
    readonly headTitle?: string;
    readonly title?: string;
    readonly summary?: string;
}

const props = defineProps<{
    blocks: readonly RawContentBlock[];
    page?: TenantPageCopy;
}>();

const pageTitle = computed(
    () =>
        props.page?.title ??
        "Customer-ready tenant websites with conversion-focused service journeys",
);

const pageSummary = computed(
    () =>
        props.page?.summary ??
        "This tenant experience combines local-brand flexibility with hardened runtime boundaries, capability-aware navigation, and repeatable performance standards.",
);
</script>

<template>
    <Head :title="props.page?.headTitle ?? pageTitle" />
    <SurfaceShell
        :page-title="pageTitle"
        :page-summary="pageSummary"
    >
        <BlockRenderer :blocks="props.blocks" />
    </SurfaceShell>
</template>
