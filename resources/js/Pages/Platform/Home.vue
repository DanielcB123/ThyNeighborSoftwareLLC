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
        "Enterprise-ready software delivery for every stage of growth",
    summary:
        props.pageMeta?.summary ??
        "WeBuildYouThrive combines platform architecture, implementation services, and long-term operational support.",
    headTitle: props.pageMeta?.headTitle ?? "Platform",
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
