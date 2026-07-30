<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { NavigationMenu } from "@/core/navigation/components/NavigationMenu";
import { useAccessContext } from "@/core/access/useAccessContext";
import { resolveNavigation } from "@/core/navigation/navigationRegistry";
import { getTenantAdminNavigationItems } from "@/core/navigation/registries/tenantAdminNavigation";
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
const accessContext = useAccessContext();
const urlBuilder = useUrlBuilder();

const tenantName = computed(
    () => frontendRuntime.value.tenant?.displayName ?? "Tenant Administration",
);
const tenantThemeStyle = computed(() =>
    resolveTenantThemeStyle(frontendRuntime.value.tenant?.theme),
);
const dashboardUrl = computed(() => urlBuilder.tenantAdmin("/dashboard"));

const navigationItems = computed(() =>
    resolveNavigation(
        getTenantAdminNavigationItems(),
        accessContext.value,
        urlBuilder,
    ),
);

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
        <header class="border-b border-[var(--wb-color-border)] bg-[var(--wb-color-surface)]">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-4 py-6 sm:px-6 lg:px-8"
            >
                <div class="flex flex-col">
                    <a
                        :href="dashboardUrl"
                        class="text-lg font-semibold text-[var(--wb-color-text)]"
                    >
                        {{ tenantName }}
                    </a>
                    <span class="text-sm text-[var(--wb-color-text-muted)]">
                        Signed in as {{ signedInUserName }}
                    </span>
                </div>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Tenant administration navigation"
                />
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8">
                <p class="wb-pill mb-3">Tenant Administration Surface</p>
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
