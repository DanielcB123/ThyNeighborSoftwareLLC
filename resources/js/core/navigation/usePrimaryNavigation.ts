import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useAccessContext } from "@/core/access/useAccessContext";
import { resolveNavigation } from "@/core/navigation/navigationRegistry";
import { getPlatformPublicNavigationItems } from "@/core/navigation/registries/platformPublicNavigation";
import { getTenantAdminNavigationItems } from "@/core/navigation/registries/tenantAdminNavigation";
import { getTenantPublicNavigationItems } from "@/core/navigation/registries/tenantPublicNavigation";
import {
    adaptServerNavigationContract,
    type AdaptedNavigationContract,
} from "@/core/navigation/serverNavigationAdapter";
import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";
import type {
    InertiaSharedProps,
    RuntimeSurface,
} from "@/core/runtime/frontendContext";
import { useUrlBuilder } from "@/core/url/urlBuilderPlugin";

function resolveFallbackNavigation(surface: RuntimeSurface) {
    if (surface === "tenant-admin") {
        return getTenantAdminNavigationItems();
    }

    if (
        surface === "tenant-public" ||
        surface === "tenant-auth" ||
        surface === "tenant-preview"
    ) {
        return getTenantPublicNavigationItems();
    }

    return getPlatformPublicNavigationItems();
}

export function usePrimaryNavigation() {
    const page = usePage<InertiaSharedProps>();
    const frontendRuntime = useFrontendRuntime();
    const accessContext = useAccessContext();
    const urlBuilder = useUrlBuilder();

    const adaptedContract = computed<AdaptedNavigationContract | null>(() =>
        adaptServerNavigationContract(page.props.navigation),
    );

    return computed(() => {
        const runtimeSurface = frontendRuntime.value.surface;
        const fallbackNavigation = resolveFallbackNavigation(runtimeSurface);
        const serverNavigation =
            adaptedContract.value &&
            adaptedContract.value.surface === runtimeSurface &&
            adaptedContract.value.primary.length > 0
                ? adaptedContract.value.primary
                : fallbackNavigation;

        return resolveNavigation(
            serverNavigation,
            accessContext.value,
            urlBuilder,
        );
    });
}
