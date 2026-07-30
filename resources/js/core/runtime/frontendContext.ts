export type RuntimeSurface =
    | "platform"
    | "tenant-public"
    | "tenant-admin"
    | "tenant-auth"
    | "tenant-preview";

export type TenantRuntimeSurface =
    | "platform"
    | "tenant_public"
    | "tenant_admin"
    | "tenant_auth"
    | "tenant_api"
    | "unknown_host";

export interface PlatformUrlContext {
    publicBaseUrl: string;
    authBaseUrl: string;
    adminBaseUrl: string;
}

export interface TenantUrlContext {
    primaryBaseUrl: string;
    authBaseUrl: string;
    adminBaseUrl: string;
    previewBaseUrl: string;
}

export interface TenantContext {
    publicId: string;
    slug: string;
    displayName: string;
    locale: string;
    timezone: string;
    enabledModules: readonly string[];
    enabledCapabilities: readonly string[];
    theme?: TenantThemeContext | null;
    urls: TenantUrlContext;
}

export interface TenantThemeContext {
    tokens: Readonly<Record<string, string>>;
    logoUrl: string | null;
    faviconUrl: string | null;
}

export interface FrontendRuntimeContext {
    surface: RuntimeSurface;
    runtimeSurface?: TenantRuntimeSurface;
    platformUrls: PlatformUrlContext;
    tenant: TenantContext | null;
}

export interface AuthUser {
    publicId: string;
    name: string;
    email: string;
}

export interface AuthAccessContext {
    permissions: readonly string[];
    modules: readonly string[];
    capabilities: readonly string[];
    roles: readonly string[];
}

export interface FrontendNavigationContract {
    surface: RuntimeSurface;
    primary: readonly unknown[];
}

export interface InertiaSharedProps {
    [key: string]: unknown;
    auth: {
        user: AuthUser | null;
        access?: AuthAccessContext | null;
    };
    frontendRuntime: FrontendRuntimeContext;
    navigation?: FrontendNavigationContract | null;
}
