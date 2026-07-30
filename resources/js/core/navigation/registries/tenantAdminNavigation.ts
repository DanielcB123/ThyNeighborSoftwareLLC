import type { NavigationItem } from "@/core/navigation/navigationRegistry";

export function getTenantAdminNavigationItems(): readonly NavigationItem[] {
    return [
        {
            id: "overview",
            label: "Overview",
            target: {
                kind: "internal",
                surface: "tenant-admin",
                path: "/dashboard",
            },
            access: {
                requiresAuthentication: true,
            },
        },
        {
            id: "customers",
            label: "Customers",
            target: {
                kind: "internal",
                surface: "tenant-admin",
                path: "/customers",
            },
            access: {
                requiresAuthentication: true,
                allPermissions: ["customers.view"],
            },
        },
        {
            id: "dispatch",
            label: "Dispatch",
            target: {
                kind: "internal",
                surface: "tenant-admin",
                path: "/dispatch",
            },
            access: {
                requiresAuthentication: true,
                allModules: ["dispatch"],
                allPermissions: ["dispatch.view"],
            },
        },
        {
            id: "billing",
            label: "Billing",
            target: {
                kind: "internal",
                surface: "tenant-admin",
                path: "/billing",
            },
            access: {
                requiresAuthentication: true,
                allCapabilities: ["billing"],
            },
        },
    ];
}
