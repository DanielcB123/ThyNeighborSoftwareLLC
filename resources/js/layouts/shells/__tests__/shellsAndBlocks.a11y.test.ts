import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { axe } from "vitest-axe";
import { defineComponent, h } from "vue";
import PlatformPublicShell from "@/layouts/shells/PlatformPublicShell.vue";
import BlockRenderer from "@/core/blocks/BlockRenderer.vue";

const mockPageProps = {
    auth: {
        user: null,
        access: null,
    },
    frontendRuntime: {
        surface: "platform" as const,
        platformUrls: {
            publicBaseUrl: "https://webuildyouthrive.com",
            authBaseUrl: "https://accounts.webuildyouthrive.com",
            adminBaseUrl: "https://admin.webuildyouthrive.com",
        },
        tenant: null,
    },
};

vi.mock("@inertiajs/vue3", () => ({
    usePage: () => ({
        props: mockPageProps,
    }),
}));

describe("shell and block accessibility", () => {
    it("renders accessible nav and content block markup", async () => {
        const AccessibilityHost = defineComponent({
            setup() {
                return () =>
                    h(
                        PlatformPublicShell,
                        {
                            pageTitle: "Platform",
                        },
                        {
                            default: () =>
                                h(BlockRenderer, {
                                    blocks: [
                                        {
                                            id: "hero",
                                            type: "hero",
                                            schemaVersion: 1,
                                            data: {
                                                eyebrow: "Foundation",
                                                heading:
                                                    "Scale-ready frontend architecture",
                                                supportingText:
                                                    "Composable blocks and typed routes.",
                                                primaryActionLabel: "Explore",
                                                primaryActionPath: "/services",
                                            },
                                        },
                                    ],
                                }),
                        },
                    );
            },
        });

        const wrapper = mount(AccessibilityHost);
        const results = await axe(wrapper.element);

        expect(results.violations).toHaveLength(0);
    });
});
