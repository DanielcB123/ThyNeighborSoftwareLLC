import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import TenantAdminShell from "@/layouts/shells/TenantAdminShell.vue";

const mockPageProps = {
    auth: {
        user: {
            publicId: "01J2W4G8K8ABP2YYKHSDDJK3ZT",
            name: "Taylor Admin",
            email: "taylor@example.com",
        },
        access: {
            permissions: ["dispatch.view", "customers.view", "billing.view"],
            modules: ["dispatch", "crm"],
            capabilities: ["billing"],
            roles: ["owner"],
        },
    },
    frontendRuntime: {
        surface: "tenant-admin" as const,
        platformUrls: {
            publicBaseUrl: "https://webuildyouthrive.com",
            authBaseUrl: "https://accounts.webuildyouthrive.com",
            adminBaseUrl: "https://admin.webuildyouthrive.com",
        },
        tenant: {
            publicId: "01J2W4G8K8ABP2YYKHSDDJK3ZT",
            slug: "smith-plumbing-hvac",
            displayName: "Smith Plumbing & HVAC",
            locale: "en",
            timezone: "America/Chicago",
            enabledModules: ["dispatch", "crm"],
            enabledCapabilities: ["billing"],
            theme: {
                tokens: {},
                logoUrl: null,
                faviconUrl: null,
            },
            urls: {
                primaryBaseUrl: "https://smithplumbing.com",
                authBaseUrl: "https://smithplumbing.com",
                adminBaseUrl: "https://smithplumbing.com/admin",
                previewBaseUrl: "https://smithplumbing.com/preview",
            },
        },
    },
    navigation: {
        surface: "tenant-admin",
        primary: [
            {
                id: "overview",
                label: "Overview",
                target: {
                    kind: "internal",
                    surface: "tenant-admin",
                    path: "/dashboard",
                },
            },
        ],
    },
};

vi.mock("@inertiajs/vue3", () => ({
    usePage: () => ({
        props: mockPageProps,
    }),
}));

describe("tenant admin shell browser behavior", () => {
    it("keeps navigation links on the tenant admin domain and path prefix", () => {
        const wrapper = mount(TenantAdminShell, {
            props: {
                pageTitle: "Tenant Admin",
            },
        });

        const anchor = document.createElement("a");
        anchor.href = wrapper.get("a").attributes("href") ?? "";

        expect(anchor.hostname).toBe("smithplumbing.com");
        expect(anchor.pathname).toBe("/admin/dashboard");
    });
});
