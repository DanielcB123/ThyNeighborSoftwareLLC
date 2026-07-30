<script setup lang="ts">
import { computed } from "vue";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { usePrimaryNavigation } from "@/core/navigation/usePrimaryNavigation";
import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";
import { resolveTenantThemeStyle } from "@/core/theme/themeRuntime";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const frontendRuntime = useFrontendRuntime();
const urlBuilder = useUrlBuilder();
const navigationItems = usePrimaryNavigation();

const tenantName = computed(
    () => frontendRuntime.value.tenant?.displayName ?? "Tenant Experience",
);
const tenantThemeStyle = computed(() =>
    resolveTenantThemeStyle(frontendRuntime.value.tenant?.theme),
);
const homeUrl = computed(() => urlBuilder.tenantPublic("/"));
</script>

<template>
    <div
        class="wb-shell-surface"
        data-surface="tenant-public"
        :style="tenantThemeStyle"
    >
        <header class="border-b border-[var(--wb-color-border)] bg-[var(--wb-color-surface)]">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-4 py-6 sm:px-6 lg:px-8"
            >
                <a :href="homeUrl" class="text-lg font-semibold text-[var(--wb-color-text)]">
                    {{ tenantName }}
                </a>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Tenant public website navigation"
                />
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8">
                <p class="wb-pill mb-3">Tenant Public Surface</p>
                <h1 class="text-3xl font-bold text-[var(--wb-color-text)]">
                    {{ props.pageTitle }}
                </h1>
                <p
                    v-if="props.pageSummary"
                    class="mt-2 text-sm text-[var(--wb-color-text-muted)]"
                >
                    {{ props.pageSummary }}
                </p>
            </header>
            <slot />
        </main>
    </div>
</template>
