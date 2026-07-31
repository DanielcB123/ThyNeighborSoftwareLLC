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
        page-summary="Live operational telemetry across dispatch, customer demand, and billing health."
    >
        <section class="grid gap-5 lg:grid-cols-3">
            <article
                v-for="widget in widgets"
                :key="widget.id"
                class="wb-card p-6"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-semibold text-[var(--wb-color-text)]">
                        {{ widget.title }}
                    </h2>
                    <span
                        class="rounded-[var(--wb-radius-xs)] px-2 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.09em]"
                        :class="{
                            'bg-[var(--wb-color-surface-muted)] text-[var(--wb-color-text-muted)]':
                                widget.state === 'loading',
                            'bg-[#fff1dc] text-[var(--wb-color-warning)]':
                                widget.state === 'empty',
                            'bg-[#e8faef] text-[var(--wb-color-success)]':
                                widget.state === 'data',
                        }"
                    >
                        {{ widget.state }}
                    </span>
                </div>
                <p class="mt-3 text-sm leading-6 text-[var(--wb-color-text-muted)]">
                    {{ widget.helperText }}
                </p>
                <p
                    class="mt-6 text-3xl font-semibold"
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
