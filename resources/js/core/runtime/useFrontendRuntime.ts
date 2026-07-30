import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import type {
    FrontendRuntimeContext,
    InertiaSharedProps,
} from "@/core/runtime/frontendContext";

export function useFrontendRuntime() {
    const page = usePage<InertiaSharedProps>();

    return computed<FrontendRuntimeContext>(() => page.props.frontendRuntime);
}
