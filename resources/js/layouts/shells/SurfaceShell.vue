<script setup lang="ts">
import { computed } from "vue";
import PlatformPublicShell from "@/layouts/shells/PlatformPublicShell.vue";
import TenantAdminShell from "@/layouts/shells/TenantAdminShell.vue";
import TenantPublicShell from "@/layouts/shells/TenantPublicShell.vue";
import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";
import { resolveShellBySurface } from "@/layouts/shells/shellResolver";

const props = defineProps<{
    pageTitle: string;
    pageSummary?: string;
}>();

const frontendRuntime = useFrontendRuntime();

const activeShell = computed(() => {
    const shellId = resolveShellBySurface(frontendRuntime.value.surface);

    if (shellId === "tenant-admin-shell") {
        return TenantAdminShell;
    }

    if (shellId === "tenant-public-shell") {
        return TenantPublicShell;
    }

    return PlatformPublicShell;
});
</script>

<template>
    <component
        :is="activeShell"
        :page-title="props.pageTitle"
        :page-summary="props.pageSummary"
    >
        <slot />
    </component>
</template>
