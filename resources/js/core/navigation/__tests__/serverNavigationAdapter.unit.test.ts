import { describe, expect, it } from "vitest";
import { adaptServerNavigationContract } from "@/core/navigation/serverNavigationAdapter";

describe("adaptServerNavigationContract", () => {
    it("normalizes valid server navigation payloads", () => {
        const adapted = adaptServerNavigationContract({
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
                {
                    id: "docs",
                    label: "Documentation",
                    target: {
                        kind: "external",
                        href: "https://docs.webuildyouthrive.com",
                    },
                },
            ],
        });

        expect(adapted).not.toBeNull();
        expect(adapted?.surface).toBe("tenant-admin");
        expect(adapted?.primary).toHaveLength(2);
    });

    it("drops malformed items and rejects invalid payload surfaces", () => {
        const malformed = adaptServerNavigationContract({
            surface: "tenant-public",
            primary: [
                {
                    id: "missing-target",
                    label: "Missing Target",
                },
                {
                    id: "valid",
                    label: "Valid",
                    target: {
                        kind: "internal",
                        surface: "tenant-public",
                        path: "/",
                    },
                },
            ],
        });

        expect(malformed?.primary).toHaveLength(1);

        const invalidSurface = adaptServerNavigationContract({
            surface: "unknown-surface",
            primary: [],
        });

        expect(invalidSurface).toBeNull();
    });
});
