import { describe, expect, it } from "vitest";
import { createUrlBuilder } from "@/core/url/urlBuilder";

const platform = {
    publicBaseUrl: "https://webuildyouthrive.com",
    authBaseUrl: "https://accounts.webuildyouthrive.com",
    adminBaseUrl: "https://admin.webuildyouthrive.com",
} as const;

const tenant = {
    primaryBaseUrl: "https://smithplumbing.com",
    authBaseUrl: "https://smithplumbing.com",
    adminBaseUrl: "https://smithplumbing.com/admin",
    previewBaseUrl: "https://smithplumbing.com/preview",
} as const;

describe("createUrlBuilder", () => {
    it("builds platform URLs by application surface", () => {
        const builder = createUrlBuilder({ platform, tenant: null });

        expect(builder.platformPublic("/services")).toBe(
            "https://webuildyouthrive.com/services",
        );
        expect(builder.platformAuth("/login")).toBe(
            "https://accounts.webuildyouthrive.com/login",
        );
        expect(builder.platformAdmin("/tenants")).toBe(
            "https://admin.webuildyouthrive.com/tenants",
        );
    });

    it("builds tenant URLs with query and hash fragments", () => {
        const builder = createUrlBuilder({ platform, tenant });

        expect(
            builder.tenantPublic("/request-service", {
                query: {
                    location: "houston",
                    emergency: true,
                    department: ["hvac", "electrical"],
                },
                hash: "top",
            }),
        ).toBe(
            "https://smithplumbing.com/request-service?location=houston&emergency=true&department=hvac&department=electrical#top",
        );
    });

    it("throws when tenant URLs are requested without tenant context", () => {
        const builder = createUrlBuilder({ platform, tenant: null });

        expect(() => builder.tenantAdmin("/customers")).toThrow(
            "Tenant URL context is required for tenant surface URLs.",
        );
    });

    it("rejects unsafe path traversal and invalid external URLs", () => {
        const builder = createUrlBuilder({ platform, tenant });

        expect(() => builder.platformPublic("/../admin")).toThrow(
            "Path cannot include dot-segment traversal.",
        );
        expect(() => builder.external("javascript:alert(1)")).toThrow(
            "External URL must be an absolute http/https URL.",
        );
    });

    it("preserves configured path prefixes for admin and preview surfaces", () => {
        const builder = createUrlBuilder({
            platform: {
                ...platform,
                adminBaseUrl: "https://webuildyouthrive.com/platform-admin",
            },
            tenant: {
                ...tenant,
                adminBaseUrl: "https://smithplumbing.com/admin",
                previewBaseUrl: "https://smithplumbing.com/preview",
            },
        });

        expect(builder.platformAdmin("/accounts")).toBe(
            "https://webuildyouthrive.com/platform-admin/accounts",
        );
        expect(builder.tenantAdmin("/customers")).toBe(
            "https://smithplumbing.com/admin/customers",
        );
        expect(builder.tenantPreview("/landing-page")).toBe(
            "https://smithplumbing.com/preview/landing-page",
        );
    });

    it("rejects paths that contain inline query or hash values", () => {
        const builder = createUrlBuilder({ platform, tenant });

        expect(() => builder.platformPublic("/services?type=hvac")).toThrow(
            "Path cannot include query strings or hash fragments. Use options.query/options.hash instead.",
        );
        expect(() => builder.platformPublic("/services#contact")).toThrow(
            "Path cannot include query strings or hash fragments. Use options.query/options.hash instead.",
        );
    });
});
