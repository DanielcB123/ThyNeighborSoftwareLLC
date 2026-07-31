<script setup lang="ts">
import { computed } from "vue";
import {
    resolvePageBlocks,
    type RenderableBlock,
} from "@/core/blocks/blockRegistry";
import type { RawContentBlock } from "@/core/blocks/types";

const props = defineProps<{
    blocks: readonly RawContentBlock[];
}>();

const resolvedBlocks = computed<readonly RenderableBlock[]>(() =>
    resolvePageBlocks(props.blocks),
);
</script>

<template>
    <div class="space-y-12 md:space-y-16">
        <component
            :is="block.component"
            v-for="block in resolvedBlocks"
            :key="block.id"
            :block-id="block.id"
            :data="block.isKnown ? block.data : undefined"
            :block-type="block.type"
            :reason="
                block.isKnown
                    ? undefined
                    : block.reason
            "
        />
    </div>
</template>
