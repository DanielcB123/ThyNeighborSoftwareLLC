import type { InertiaSharedProps } from "@/core/runtime/frontendContext";

declare module "@inertiajs/core" {
    interface PageProps extends InertiaSharedProps {}
}
