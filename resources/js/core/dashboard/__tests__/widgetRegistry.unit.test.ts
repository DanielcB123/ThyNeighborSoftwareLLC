import { describe, expect, it } from "vitest";
import { createAccessContext } from "@/core/access/accessControl";
import { resolveDashboardWidgets } from "@/core/dashboard/widgetRegistry";

describe("resolveDashboardWidgets", () => {
    it("filters widgets by module, capability, and permission requirements", () => {
        const accessContext = createAccessContext({
            isAuthenticated: true,
            permissions: ["dispatch.view", "customers.view"],
            modules: ["dispatch", "crm"],
            capabilities: [],
        });

        const widgets = resolveDashboardWidgets(
            {
                pendingDispatches: 7,
                activeServiceRequests: 3,
                monthlyRevenueCents: 1023400,
            },
            accessContext,
        );

        expect(widgets.map((widget) => widget.id)).toEqual([
            "dispatch-queue",
            "service-requests",
        ]);
    });

    it("returns loading and empty states for incomplete snapshots", () => {
        const accessContext = createAccessContext({
            isAuthenticated: true,
            permissions: ["dispatch.view", "customers.view", "billing.view"],
            modules: ["dispatch", "crm"],
            capabilities: ["billing"],
        });

        const widgets = resolveDashboardWidgets(
            {
                pendingDispatches: null,
                activeServiceRequests: 0,
                monthlyRevenueCents: null,
            },
            accessContext,
        );

        expect(widgets).toEqual(
            expect.arrayContaining([
                expect.objectContaining({
                    id: "dispatch-queue",
                    state: "loading",
                }),
                expect.objectContaining({
                    id: "service-requests",
                    state: "empty",
                }),
                expect.objectContaining({
                    id: "monthly-revenue",
                    state: "loading",
                }),
            ]),
        );
    });
});
