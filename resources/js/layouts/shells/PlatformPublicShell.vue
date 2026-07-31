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
</script>

<template>
    <div class="wb-shell-surface" data-surface="platform">
        <header class="border-b border-[var(--wb-color-border)] bg-white/72 backdrop-blur">
            <div
                class="mx-auto flex w-full max-w-[var(--wb-grid-max)] flex-wrap items-center justify-between gap-5 px-4 py-5 sm:px-6 lg:px-8"
            >
                <div class="flex flex-col gap-1">
                    <a
                        :href="homeUrl"
                        class="font-display text-xl font-semibold tracking-[0.05em] text-[var(--wb-color-text)]"
                    >
                        WeBuildYouThrive
                    </a>
                    <p class="hidden text-xs uppercase tracking-[0.14em] text-[var(--wb-color-text-muted)] md:block">
                        Platform engineering studio
                    </p>
                </div>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Platform primary navigation"
                />
            </div>
        </header>
        <main class="mx-auto w-full max-w-[var(--wb-grid-max)] px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
            <header class="mb-10">
                <h1 class="max-w-5xl font-display text-4xl font-semibold leading-[1.05] text-[var(--wb-color-text)] md:text-5xl">
                    {{ props.pageTitle }}
                </h1>
                <p
                    v-if="props.pageSummary"
                    class="mt-4 max-w-4xl text-base leading-7 text-[var(--wb-color-text-muted)]"
                >
                    {{ props.pageSummary }}
                </p>
            </header>
            <slot />
        </main>
        <footer class="border-t border-[var(--wb-color-border)] bg-white/72 py-6">
            <div
                class="mx-auto flex w-full max-w-[var(--wb-grid-max)] flex-wrap items-center justify-between gap-4 px-4 text-sm text-[var(--wb-color-text-muted)] sm:px-6 lg:px-8"
            >
                <p>© {{ new Date().getFullYear() }} WeBuildYouThrive.</p>
                <span class="wb-pill">Platform Public Surface</span>
            </div>
        </footer>
    </div>
</template>
