import { describe, expect, it } from "vitest";
import { resolvePageBlocks } from "@/core/blocks/blockRegistry";
import type { RawContentBlock } from "@/core/blocks/types";

describe("resolvePageBlocks", () => {
    it("resolves known block definitions and falls back for unknown blocks", () => {
        const blocks: readonly RawContentBlock[] = [
            {
                id: "hero-1",
                type: "hero",
                schemaVersion: 1,
                data: {
                    eyebrow: "Scale",
                    heading: "Tenant-safe architectures",
                    supportingText: "Designed for multi-domain operations.",
                    primaryActionLabel: "Get Started",
                    primaryActionPath: "/start",
                },
            },
            {
                id: "unknown-1",
                type: "custom-legacy",
                schemaVersion: 1,
                data: {},
            },
        ];

        const resolved = resolvePageBlocks(blocks);

        expect(resolved[0].isKnown).toBe(true);
        expect(resolved[0].type).toBe("hero");
        expect(resolved[1].isKnown).toBe(false);

        if (!resolved[1].isKnown) {
            expect(resolved[1].reason).toContain("not registered");
        }
    });

    it("falls back safely when block schema is invalid", () => {
        const blocks: readonly RawContentBlock[] = [
            {
                id: "cta-invalid",
                type: "cta-section",
                schemaVersion: 1,
                data: {
                    heading: "Missing action metadata",
                },
            },
        ];

        const resolved = resolvePageBlocks(blocks);

        expect(resolved).toHaveLength(1);
        expect(resolved[0].isKnown).toBe(false);

        if (!resolved[0].isKnown) {
            expect(resolved[0].reason).toContain("schema");
        }
    });
});
