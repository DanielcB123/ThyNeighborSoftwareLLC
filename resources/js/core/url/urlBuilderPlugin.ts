import { inject, type App, type InjectionKey } from "vue";
import type { FrontendRuntimeContext } from "@/core/runtime/frontendContext";
import { createUrlBuilder, type UrlBuilder } from "@/core/url/urlBuilder";

const URL_BUILDER_KEY: InjectionKey<UrlBuilder> = Symbol("url-builder");

export function registerUrlBuilder(
    app: App,
    runtimeContext: FrontendRuntimeContext,
): void {
    const urlBuilder = createUrlBuilder({
        platform: runtimeContext.platformUrls,
        tenant: runtimeContext.tenant?.urls ?? null,
    });

    app.provide(URL_BUILDER_KEY, urlBuilder);
}

export function useUrlBuilder(): UrlBuilder {
    const builder = inject(URL_BUILDER_KEY);

    if (!builder) {
        throw new Error(
            "URL builder is not registered on the Vue application.",
        );
    }

    return builder;
}
