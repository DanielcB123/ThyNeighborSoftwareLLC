<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { usePrimaryNavigation } from "@/core/navigation/usePrimaryNavigation";
import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";
import type { InertiaSharedProps } from "@/core/runtime/frontendContext";
import { resolveTenantThemeStyle } from "@/core/theme/themeRuntime";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const page = usePage<InertiaSharedProps>();
const frontendRuntime = useFrontendRuntime();
const urlBuilder = useUrlBuilder();
const navigationItems = usePrimaryNavigation();

const tenantName = computed(
    () => frontendRuntime.value.tenant?.displayName ?? "Tenant Administration",
);
const tenantThemeStyle = computed(() =>
    resolveTenantThemeStyle(frontendRuntime.value.tenant?.theme),
);
const dashboardUrl = computed(() => urlBuilder.tenantAdmin("/dashboard"));

const signedInUserName = computed(
    () => page.props.auth.user?.name ?? "Administrator",
);
</script>

<template>
    <div
        class="wb-shell-surface"
        data-surface="tenant-admin"
        :style="tenantThemeStyle"
    >
        <header class="border-b border-[var(--wb-color-border)] bg-[var(--wb-color-surface)] backdrop-blur">
            <div
                class="mx-auto flex w-full max-w-[var(--wb-grid-max)] flex-wrap items-center justify-between gap-5 px-4 py-5 sm:px-6 lg:px-8"
            >
                <div class="flex flex-col">
                    <a
                        :href="dashboardUrl"
                        class="font-display text-xl font-semibold tracking-[0.05em] text-[var(--wb-color-text)]"
                    >
                        {{ tenantName }}
                    </a>
                    <span class="text-xs uppercase tracking-[0.11em] text-[var(--wb-color-text-muted)]">
                        Signed in as {{ signedInUserName }}
                    </span>
                </div>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Tenant administration navigation"
                />
            </div>
        </header>
        <main class="mx-auto w-full max-w-[var(--wb-grid-max)] px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
            <header class="mb-10">
                <p class="wb-pill mb-3">Tenant Administration Surface</p>
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
