<script setup lang="ts">
import { computed } from "vue";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { usePrimaryNavigation } from "@/core/navigation/usePrimaryNavigation";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const urlBuilder = useUrlBuilder();
const navigationItems = usePrimaryNavigation();

const homeUrl = computed(() => urlBuilder.platformPublic("/"));
const strategyCallUrl = computed(() => urlBuilder.platformPublic("/contact"));
</script>

<template>
    <div class="wb-shell-surface" data-surface="platform">
        <header class="border-b border-[var(--color-border)] bg-transparent">
            <div class="wb-shell-grid flex flex-wrap items-center justify-between gap-5 py-6">
                <a :href="homeUrl" class="grid gap-1 no-underline">
                    <span class="wb-label text-[var(--color-text)]">WeBuildYouThrive</span>
                    <span class="wb-micro-label text-[var(--color-text-muted)]">
                        Cinematic Digital Studio + Platform Engineering
                    </span>
                </a>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Platform primary navigation"
                />
                <a :href="strategyCallUrl" class="wb-button wb-button--primary">
                    Book Strategy Call
                </a>
            </div>
        </header>
        <main id="app-main-content" class="wb-shell-grid wb-shell-section">
            <header class="grid gap-5 py-3 md:gap-6">
                <p class="wb-shell-kicker">Public Frontend Surface</p>
                <h1 class="wb-display-xl max-w-5xl text-balance">
                    {{ props.pageTitle }}
                </h1>
                <p
                    v-if="props.pageSummary"
                    class="wb-body-l max-w-3xl text-pretty text-[var(--color-text-muted)]"
                >
                    {{ props.pageSummary }}
                </p>
            </header>
            <section class="mt-8">
                <slot />
            </section>
        </main>
        <footer class="border-t border-[var(--color-border)] py-8">
            <div class="wb-shell-grid flex flex-wrap items-center justify-between gap-5">
                <p class="wb-body-s text-[var(--color-text-muted)]">
                    © {{ new Date().getFullYear() }} WeBuildYouThrive. Built for measurable growth.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a :href="homeUrl" class="wb-button wb-button--ghost">Explore Work</a>
                    <a :href="strategyCallUrl" class="wb-button wb-button--accent">
                        Start Discovery
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
