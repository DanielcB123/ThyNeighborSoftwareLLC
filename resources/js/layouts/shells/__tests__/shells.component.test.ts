import { beforeEach, describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import PlatformPublicShell from "@/layouts/shells/PlatformPublicShell.vue";
import TenantAdminShell from "@/layouts/shells/TenantAdminShell.vue";
import TenantPublicShell from "@/layouts/shells/TenantPublicShell.vue";
import type { InertiaSharedProps } from "@/core/runtime/frontendContext";

let mockPageProps: InertiaSharedProps;

vi.mock("@inertiajs/vue3", () => ({
    usePage: () => ({
        props: mockPageProps,
    }),
}));

function createTenantContext() {
    return {
        publicId: "01J2W4G8K8ABP2YYKHSDDJK3ZT",
        slug: "smith-plumbing-hvac",
        displayName: "Smith Plumbing & HVAC",
        locale: "en",
        timezone: "America/Chicago",
        enabledModules: ["dispatch", "crm"],
        enabledCapabilities: ["billing"],
        theme: {
            tokens: {
                "--wb-color-primary": "#0f766e",
            },
            logoUrl: null,
            faviconUrl: null,
        },
        urls: {
            primaryBaseUrl: "https://smithplumbing.com",
            authBaseUrl: "https://smithplumbing.com",
            adminBaseUrl: "https://smithplumbing.com/admin",
            previewBaseUrl: "https://smithplumbing.com/preview",
        },
    };
}

beforeEach(() => {
    mockPageProps = {
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
            },
        },
        frontendRuntime: {
            surface: "platform",
            platformUrls: {
                publicBaseUrl: "https://webuildyouthrive.com",
                authBaseUrl: "https://accounts.webuildyouthrive.com",
                adminBaseUrl: "https://admin.webuildyouthrive.com",
            },
            tenant: null,
        },
    };
});

describe("surface shells", () => {
    it("renders platform shell navigation landmark", () => {
        const wrapper = mount(PlatformPublicShell, {
            props: {
                pageTitle: "Platform",
                pageSummary: "Platform summary.",
            },
        });

        expect(wrapper.get('nav[aria-label="Platform primary navigation"]')).toBeTruthy();
    });

    it("renders tenant public shell with tenant navigation landmark", () => {
        mockPageProps.frontendRuntime = {
            ...mockPageProps.frontendRuntime,
            surface: "tenant-public",
            tenant: createTenantContext(),
        };

        const wrapper = mount(TenantPublicShell, {
            props: {
                pageTitle: "Tenant Site",
            },
        });

        expect(
            wrapper.get('nav[aria-label="Tenant public website navigation"]'),
        ).toBeTruthy();
        expect(wrapper.text()).toContain("Smith Plumbing & HVAC");
    });

    it("renders tenant admin shell with administration navigation landmark", () => {
        mockPageProps.frontendRuntime = {
            ...mockPageProps.frontendRuntime,
            surface: "tenant-admin",
            tenant: createTenantContext(),
        };

        const wrapper = mount(TenantAdminShell, {
            props: {
                pageTitle: "Admin",
            },
        });

        expect(
            wrapper.get('nav[aria-label="Tenant administration navigation"]'),
        ).toBeTruthy();
        expect(wrapper.text()).toContain("Signed in as Taylor Admin");
    });
});
