<script setup lang="ts">
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";
import BlockRenderer from "@/core/blocks/BlockRenderer.vue";
import type { RawContentBlock } from "@/core/blocks/types";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

interface PageCopy {
    readonly headTitle?: string;
    readonly title?: string;
    readonly summary?: string;
}

const props = defineProps<{
    blocks: readonly RawContentBlock[];
    page?: PageCopy;
}>();

const pageTitle = computed(
    () => props.page?.title ?? "Web experiences and software systems built to outlast trends",
);

const pageSummary = computed(
    () =>
        props.page?.summary ??
        "WeBuildYouThrive combines cinematic frontend craft with disciplined software engineering for organizations that need reliability and velocity.",
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
