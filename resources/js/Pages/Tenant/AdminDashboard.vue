<script setup lang="ts">
import DashboardPage from "@/pages/tenant-admin/DashboardPage.vue";
import { Head } from "@inertiajs/vue3";
import type { DashboardSnapshot } from "@/core/dashboard/widgetRegistry";

defineProps<{
    dashboardSnapshot: Partial<DashboardSnapshot>;
}>();
</script>

<template>
    <Head title="Tenant Admin Dashboard" />
    <DashboardPage :dashboard-snapshot="dashboardSnapshot" />
</template>
