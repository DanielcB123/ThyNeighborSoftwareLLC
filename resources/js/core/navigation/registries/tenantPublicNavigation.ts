import type { NavigationItem } from "@/core/navigation/navigationRegistry";

export function getTenantPublicNavigationItems(): readonly NavigationItem[] {
    return [
        {
            id: "home",
            label: "Home",
            target: {
                kind: "internal",
                surface: "tenant-public",
                path: "/",
            },
        },
        {
            id: "services",
            label: "Services",
            target: {
                kind: "internal",
                surface: "tenant-public",
                path: "/services",
            },
        },
        {
            id: "request-service",
            label: "Request Service",
            target: {
                kind: "internal",
                surface: "tenant-public",
                path: "/request-service",
            },
        },
        {
            id: "tenant-login",
            label: "Customer Portal",
            target: {
                kind: "internal",
                surface: "tenant-auth",
                path: "/login",
            },
        },
    ];
}
