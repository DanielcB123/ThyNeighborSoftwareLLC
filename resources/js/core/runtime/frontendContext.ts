export type RuntimeSurface = 'platform' | 'tenant-public' | 'tenant-admin' | 'tenant-auth';

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
    urls: TenantUrlContext;
}

export interface FrontendRuntimeContext {
    surface: RuntimeSurface;
    platformUrls: PlatformUrlContext;
    tenant: TenantContext | null;
}

export interface InertiaSharedProps {
    auth: {
        user: unknown | null;
    };
    frontendRuntime: FrontendRuntimeContext;
}
