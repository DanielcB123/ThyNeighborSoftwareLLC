import { describe, expect, it } from "vitest";
import { resolveShellBySurface } from "@/layouts/shells/shellResolver";

describe("resolveShellBySurface", () => {
    it("maps platform and tenant surfaces to their shell identifiers", () => {
        expect(resolveShellBySurface("platform")).toBe("platform-public-shell");
        expect(resolveShellBySurface("tenant-public")).toBe(
            "tenant-public-shell",
        );
        expect(resolveShellBySurface("tenant-auth")).toBe("tenant-public-shell");
        expect(resolveShellBySurface("tenant-admin")).toBe("tenant-admin-shell");
        expect(resolveShellBySurface("tenant-preview")).toBe(
            "tenant-public-shell",
        );
    });
});
