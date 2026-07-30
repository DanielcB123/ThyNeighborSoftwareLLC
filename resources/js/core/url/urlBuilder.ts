import type {
    PlatformUrlContext,
    TenantUrlContext,
} from "@/core/runtime/frontendContext";

export type QueryPrimitive = string | number | boolean;
export type QueryValue =
    QueryPrimitive | readonly QueryPrimitive[] | null | undefined;
export type UrlQuery = Record<string, QueryValue>;

type UrlBrand<TSurface extends string> = string & {
    readonly __surface: TSurface;
};

export type PlatformPublicUrl = UrlBrand<"platform-public">;
export type PlatformAuthUrl = UrlBrand<"platform-auth">;
export type PlatformAdminUrl = UrlBrand<"platform-admin">;
export type TenantPublicUrl = UrlBrand<"tenant-public">;
export type TenantAuthUrl = UrlBrand<"tenant-auth">;
export type TenantAdminUrl = UrlBrand<"tenant-admin">;
export type TenantPreviewUrl = UrlBrand<"tenant-preview">;
export type ExternalUrl = UrlBrand<"external">;

export interface BuildUrlOptions {
    query?: UrlQuery;
    hash?: string;
}

export interface UrlBuilder {
    platformPublic(path: string, options?: BuildUrlOptions): PlatformPublicUrl;
    platformAuth(path: string, options?: BuildUrlOptions): PlatformAuthUrl;
    platformAdmin(path: string, options?: BuildUrlOptions): PlatformAdminUrl;
    tenantPublic(path: string, options?: BuildUrlOptions): TenantPublicUrl;
    tenantAuth(path: string, options?: BuildUrlOptions): TenantAuthUrl;
    tenantAdmin(path: string, options?: BuildUrlOptions): TenantAdminUrl;
    tenantPreview(path: string, options?: BuildUrlOptions): TenantPreviewUrl;
    external(url: string): ExternalUrl;
}

export interface UrlBuilderContext {
    platform: PlatformUrlContext;
    tenant: TenantUrlContext | null;
}

const ABSOLUTE_HTTP_URL_PATTERN = /^https?:\/\/[^/\s?#]+(?:[/?#].*)?$/i;

function assertAbsoluteHttpUrl(value: string, label: string): void {
    if (!ABSOLUTE_HTTP_URL_PATTERN.test(value)) {
        throw new Error(`${label} must be an absolute http/https URL.`);
    }
}

function normalizePath(path: string): string {
    const trimmedPath = path.trim();

    if (trimmedPath.length === 0) {
        return "/";
    }

    if (!trimmedPath.startsWith("/")) {
        throw new Error('Path must start with "/".');
    }

    if (trimmedPath.includes("?") || trimmedPath.includes("#")) {
        throw new Error(
            "Path cannot include query strings or hash fragments. Use options.query/options.hash instead.",
        );
    }

    if (trimmedPath.includes("\\")) {
        throw new Error("Path cannot contain backslashes.");
    }

    if (
        trimmedPath.includes("/../") ||
        trimmedPath.endsWith("/..") ||
        trimmedPath.includes("/./") ||
        trimmedPath.endsWith("/.")
    ) {
        throw new Error("Path cannot include dot-segment traversal.");
    }

    return trimmedPath;
}

function createBaseUrlForSurface(baseUrl: string): URL {
    assertAbsoluteHttpUrl(baseUrl, "Base URL");
    const normalizedBaseUrl = new URL(baseUrl);

    normalizedBaseUrl.search = "";
    normalizedBaseUrl.hash = "";

    if (!normalizedBaseUrl.pathname.endsWith("/")) {
        normalizedBaseUrl.pathname = `${normalizedBaseUrl.pathname}/`;
    }

    return normalizedBaseUrl;
}

function normalizeHash(hash: string | undefined): string {
    if (!hash) {
        return "";
    }

    return hash.startsWith("#") ? hash : `#${hash}`;
}

function appendQuery(
    searchParams: URLSearchParams,
    query: UrlQuery | undefined,
): void {
    if (!query) {
        return;
    }

    for (const [key, value] of Object.entries(query)) {
        if (value === undefined || value === null) {
            continue;
        }

        if (Array.isArray(value)) {
            for (const entry of value) {
                searchParams.append(key, String(entry));
            }

            continue;
        }

        searchParams.set(key, String(value));
    }
}

function buildUrl<TSurface extends string>(
    baseUrl: string,
    path: string,
    options: BuildUrlOptions | undefined,
): UrlBrand<TSurface> {
    const normalizedPath = normalizePath(path);
    const normalizedBaseUrl = createBaseUrlForSurface(baseUrl);
    const relativePathSegment =
        normalizedPath === "/" ? "" : normalizedPath.slice(1);
    const url = new URL(relativePathSegment, normalizedBaseUrl);
    appendQuery(url.searchParams, options?.query);
    url.hash = normalizeHash(options?.hash);

    return url.toString() as UrlBrand<TSurface>;
}

function requireTenantContext(
    tenant: TenantUrlContext | null,
): TenantUrlContext {
    if (!tenant) {
        throw new Error(
            "Tenant URL context is required for tenant surface URLs.",
        );
    }

    return tenant;
}

export function createUrlBuilder(context: UrlBuilderContext): UrlBuilder {
    return {
        platformPublic(path, options) {
            return buildUrl<"platform-public">(
                context.platform.publicBaseUrl,
                path,
                options,
            );
        },

        platformAuth(path, options) {
            return buildUrl<"platform-auth">(
                context.platform.authBaseUrl,
                path,
                options,
            );
        },

        platformAdmin(path, options) {
            return buildUrl<"platform-admin">(
                context.platform.adminBaseUrl,
                path,
                options,
            );
        },

        tenantPublic(path, options) {
            const tenant = requireTenantContext(context.tenant);
            return buildUrl<"tenant-public">(
                tenant.primaryBaseUrl,
                path,
                options,
            );
        },

        tenantAuth(path, options) {
            const tenant = requireTenantContext(context.tenant);
            return buildUrl<"tenant-auth">(tenant.authBaseUrl, path, options);
        },

        tenantAdmin(path, options) {
            const tenant = requireTenantContext(context.tenant);
            return buildUrl<"tenant-admin">(tenant.adminBaseUrl, path, options);
        },

        tenantPreview(path, options) {
            const tenant = requireTenantContext(context.tenant);
            return buildUrl<"tenant-preview">(
                tenant.previewBaseUrl,
                path,
                options,
            );
        },

        external(url) {
            assertAbsoluteHttpUrl(url, "External URL");
            return url as ExternalUrl;
        },
    };
}
