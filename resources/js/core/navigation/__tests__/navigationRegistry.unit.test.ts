import { describe, expect, it } from "vitest";
import { createAccessContext } from "@/core/access/accessControl";
import type { NavigationItem } from "@/core/navigation/navigationRegistry";
import { resolveNavigation } from "@/core/navigation/navigationRegistry";
import { createUrlBuilder } from "@/core/url/urlBuilder";

const builder = createUrlBuilder({
    platform: {
        publicBaseUrl: "https://webuildyouthrive.com",
        authBaseUrl: "https://accounts.webuildyouthrive.com",
        adminBaseUrl: "https://admin.webuildyouthrive.com",
    },
    tenant: {
        primaryBaseUrl: "https://coastalairandheat.com",
        authBaseUrl: "https://coastalairandheat.com",
        adminBaseUrl: "https://coastalairandheat.com/admin",
        previewBaseUrl: "https://coastalairandheat.com/preview",
    },
});

describe("resolveNavigation", () => {
    it("filters inaccessible entries by permissions, modules, and capabilities", () => {
        const navigation: readonly NavigationItem[] = [
            {
                id: "dashboard",
                label: "Dashboard",
                target: {
                    kind: "internal",
                    surface: "tenant-admin",
                    path: "/overview",
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
                access: { allCapabilities: ["billing"] },
            },
        ];

        const accessContext = createAccessContext({
            isAuthenticated: true,
            permissions: ["dispatch.view"],
            modules: ["dispatch"],
            capabilities: [],
        });

        const resolved = resolveNavigation(navigation, accessContext, builder);

        expect(resolved).toEqual([
            {
                id: "dashboard",
                label: "Dashboard",
                href: "https://coastalairandheat.com/overview",
                children: [],
            },
            {
                id: "dispatch",
                label: "Dispatch",
                href: "https://coastalairandheat.com/dispatch",
                children: [],
            },
        ]);
    });
});
