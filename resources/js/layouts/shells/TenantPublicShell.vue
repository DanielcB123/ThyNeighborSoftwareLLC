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
const requestServiceUrl = computed(() => urlBuilder.tenantPublic("/request-service"));
</script>

<template>
    <div
        class="wb-shell-surface"
        data-surface="tenant-public"
        :style="tenantThemeStyle"
    >
        <header class="border-b border-[var(--color-border)] bg-transparent">
            <div class="wb-shell-grid flex flex-wrap items-center justify-between gap-5 py-6">
                <a :href="homeUrl" class="grid gap-1 no-underline">
                    <span class="wb-label text-[var(--color-text)]">{{ tenantName }}</span>
                    <span class="wb-micro-label text-[var(--color-text-muted)]">
                        Tenant Website Surface
                    </span>
                </a>
                <NavigationMenu
                    :items="navigationItems"
                    aria-label="Tenant public website navigation"
                />
                <a :href="requestServiceUrl" class="wb-button wb-button--primary">
                    Request Service
                </a>
            </div>
        </header>
        <main id="app-main-content" class="wb-shell-grid wb-shell-section">
            <header class="grid gap-4 py-3">
                <p class="wb-shell-kicker">Tenant Public Surface</p>
                <h1 class="wb-display-m max-w-4xl text-balance">
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
            <div class="wb-shell-grid flex flex-wrap items-center justify-between gap-4">
                <p class="wb-body-s text-[var(--color-text-muted)]">
                    Powered by tenant-aware runtime contracts and adaptive service workflows.
                </p>
                <a :href="requestServiceUrl" class="wb-button wb-button--accent">
                    Schedule Visit
                </a>
            </div>
        </footer>
    </div>
</template>
