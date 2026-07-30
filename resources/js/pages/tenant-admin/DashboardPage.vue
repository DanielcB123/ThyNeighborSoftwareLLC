<script setup lang="ts">
import { computed } from "vue";
import {
    resolveDashboardWidgets,
    type DashboardSnapshot,
} from "@/core/dashboard/widgetRegistry";
import { useAccessContext } from "@/core/access/useAccessContext";
import SurfaceShell from "@/layouts/shells/SurfaceShell.vue";

const props = defineProps<{
    dashboardSnapshot: Partial<DashboardSnapshot>;
}>();

const accessContext = useAccessContext();

const normalizedSnapshot = computed<DashboardSnapshot>(() => ({
    pendingDispatches: props.dashboardSnapshot.pendingDispatches ?? null,
    activeServiceRequests: props.dashboardSnapshot.activeServiceRequests ?? null,
    monthlyRevenueCents: props.dashboardSnapshot.monthlyRevenueCents ?? null,
}));

const widgets = computed(() =>
    resolveDashboardWidgets(normalizedSnapshot.value, accessContext.value),
);
</script>

<template>
    <SurfaceShell
        page-title="Operations Dashboard"
        page-summary="Capability-aware operational metrics for tenant administrators."
    >
        <section class="grid gap-5 lg:grid-cols-3">
            <article
                v-for="widget in widgets"
                :key="widget.id"
                class="wb-card p-6"
            >
                <h2 class="text-lg font-semibold text-[var(--wb-color-text)]">
                    {{ widget.title }}
                </h2>
                <p class="mt-2 text-sm text-[var(--wb-color-text-muted)]">
                    {{ widget.helperText }}
                </p>
                <p
                    class="mt-5 text-2xl font-semibold"
                    :class="{
                        'text-[var(--wb-color-text-muted)]':
                            widget.state === 'loading',
                        'text-[var(--wb-color-warning)]':
                            widget.state === 'empty',
                        'text-[var(--wb-color-success)]':
                            widget.state === 'data',
                    }"
                >
                    {{ widget.valueLabel }}
                </p>
            </article>
        </section>
    </SurfaceShell>
</template>
