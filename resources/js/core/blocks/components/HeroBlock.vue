<script setup lang="ts">
import type { HeroBlockData } from "@/core/blocks/types";
import CinematicOrbScene from "@/core/experience/CinematicOrbScene.vue";

defineProps<{
    blockId: string;
    data: HeroBlockData;
}>();
</script>

<template>
    <section :id="`block-${blockId}`" class="wb-hero-grid">
        <div class="wb-hero-copy">
            <p class="wb-eyebrow">
                {{ data.eyebrow }}
            </p>
            <h2 class="wb-display-l mt-5 text-balance">
                {{ data.heading }}
            </h2>
            <p class="wb-body-l mt-6 max-w-3xl text-pretty text-[var(--color-text-muted)]">
                {{ data.supportingText }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a :href="data.primaryActionPath" class="wb-button wb-button--primary">
                    {{ data.primaryActionLabel }}
                </a>
                <a
                    v-if="data.secondaryActionLabel && data.secondaryActionPath"
                    :href="data.secondaryActionPath"
                    class="wb-button wb-button--ghost"
                >
                    {{ data.secondaryActionLabel }}
                </a>
            </div>
        </div>
        <div class="wb-hero-scene">
            <CinematicOrbScene />
            <div class="wb-hero-scene-note">
                <span class="wb-dot" />
                Realtime 3D interaction optimized for constrained performance budgets
            </div>
        </div>
    </section>
</template>
