<script setup lang="ts">
import { computed } from "vue";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { useAccessContext } from "@/core/access/useAccessContext";
import { resolveNavigation } from "@/core/navigation/navigationRegistry";
import { getPlatformPublicNavigationItems } from "@/core/navigation/registries/platformPublicNavigation";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const accessContext = useAccessContext();
const urlBuilder = useUrlBuilder();

const homeUrl = computed(() => urlBuilder.platformPublic("/"));

const navigationItems = computed(() =>
    resolveNavigation(
        getPlatformPublicNavigationItems(),
        accessContext.value,
        urlBuilder,
    ),
);
</script>

<template>
    <div class="wb-shell-surface" data-surface="platform">
        <header class="border-b bg-white">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-4 py-6 sm:px-6 lg:px-8"
            >
                <a :href="homeUrl" class="text-lg font-semibold text-slate-900">
                    WeBuildYouThrive
                </a>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Platform primary navigation"
                />
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">
                    {{ props.pageTitle }}
                </h1>
                <p v-if="props.pageSummary" class="mt-2 text-sm text-slate-600">
                    {{ props.pageSummary }}
                </p>
            </header>
            <slot />
        </main>
        <footer class="border-t bg-white py-6">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 text-sm text-slate-600 sm:px-6 lg:px-8"
            >
                <p>© {{ new Date().getFullYear() }} WeBuildYouThrive.</p>
                <span class="wb-pill">Platform Public Surface</span>
            </div>
        </footer>
    </div>
</template>
