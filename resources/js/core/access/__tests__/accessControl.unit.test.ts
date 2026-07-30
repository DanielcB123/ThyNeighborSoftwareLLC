import { describe, expect, it } from "vitest";
import { canAccess, createAccessContext } from "@/core/access/accessControl";

describe("canAccess", () => {
    it("requires all configured checks to pass", () => {
        const context = createAccessContext({
            isAuthenticated: true,
            permissions: ["customers.view", "orders.manage"],
            modules: ["crm", "orders"],
            capabilities: ["appointments"],
            roles: ["owner"],
        });

        expect(
            canAccess(
                {
                    requiresAuthentication: true,
                    allPermissions: ["customers.view"],
                    anyPermissions: ["orders.manage", "orders.read"],
                    allModules: ["crm"],
                    anyCapabilities: ["appointments", "dispatch"],
                    anyRoles: ["owner", "manager"],
                },
                context,
            ),
        ).toBe(true);
    });

    it("returns false when one requirement fails", () => {
        const context = createAccessContext({
            isAuthenticated: true,
            permissions: ["customers.view"],
            modules: ["crm"],
            capabilities: [],
            roles: ["technician"],
        });

        expect(
            canAccess(
                {
                    allPermissions: ["customers.view", "orders.manage"],
                    anyRoles: ["owner", "finance-manager"],
                },
                context,
            ),
        ).toBe(false);
    });
});
