import { useFrontendRuntime } from "@/core/runtime/useFrontendRuntime";
import { createUrlBuilder, type UrlBuilder } from "@/core/url/urlBuilder";

export function useUrlBuilder(): UrlBuilder {
    const frontendRuntime = useFrontendRuntime();
    const resolveBuilder = (): UrlBuilder =>
        createUrlBuilder({
            platform: frontendRuntime.value.platformUrls,
            tenant: frontendRuntime.value.tenant?.urls ?? null,
        });

    return {
        platformPublic(path, options) {
            return resolveBuilder().platformPublic(path, options);
        },
        platformAuth(path, options) {
            return resolveBuilder().platformAuth(path, options);
        },
        platformAdmin(path, options) {
            return resolveBuilder().platformAdmin(path, options);
        },
        tenantPublic(path, options) {
            return resolveBuilder().tenantPublic(path, options);
        },
        tenantAuth(path, options) {
            return resolveBuilder().tenantAuth(path, options);
        },
        tenantAdmin(path, options) {
            return resolveBuilder().tenantAdmin(path, options);
        },
        tenantPreview(path, options) {
            return resolveBuilder().tenantPreview(path, options);
        },
        external(url) {
            return resolveBuilder().external(url);
        },
    };
}
