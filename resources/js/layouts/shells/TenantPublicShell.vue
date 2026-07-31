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
        <header class="border-b border-[var(--wb-color-border)] bg-[var(--wb-color-surface)] backdrop-blur">
            <div
                class="mx-auto flex w-full max-w-[var(--wb-grid-max)] flex-wrap items-center justify-between gap-5 px-4 py-5 sm:px-6 lg:px-8"
            >
                <div class="flex flex-col gap-1">
                    <a
                        :href="homeUrl"
                        class="font-display text-xl font-semibold tracking-[0.05em] text-[var(--wb-color-text)]"
                    >
                        {{ tenantName }}
                    </a>
                    <p class="hidden text-xs uppercase tracking-[0.12em] text-[var(--wb-color-text-muted)] md:block">
                        Tenant public site
                    </p>
                </div>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Tenant public website navigation"
                />
            </div>
        </header>
        <main class="mx-auto w-full max-w-[var(--wb-grid-max)] px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
            <header class="mb-10">
                <p class="wb-pill mb-3">Tenant Public Surface</p>
                <h1 class="max-w-5xl font-display text-4xl font-semibold leading-[1.08] text-[var(--wb-color-text)] md:text-5xl">
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
    </div>
</template>
