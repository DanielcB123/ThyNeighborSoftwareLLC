import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { createAccessContext } from "@/core/access/accessControl";
import type { InertiaSharedProps } from "@/core/runtime/frontendContext";
import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";

export function useAccessContext() {
    const page = usePage<InertiaSharedProps>();
    const frontendRuntime = useFrontendRuntime();

    return computed(() =>
        createAccessContext({
            isAuthenticated: page.props.auth.user !== null,
            permissions: page.props.auth.access?.permissions ?? [],
            modules:
                page.props.auth.access?.modules ??
                frontendRuntime.value.tenant?.enabledModules ??
                [],
            capabilities:
                page.props.auth.access?.capabilities ??
                frontendRuntime.value.tenant?.enabledCapabilities ??
                [],
        }),
    );
}
