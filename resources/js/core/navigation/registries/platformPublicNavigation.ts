import type { NavigationItem } from "@/core/navigation/navigationRegistry";

export function getPlatformPublicNavigationItems(): readonly NavigationItem[] {
    return [
        {
            id: "home",
            label: "Home",
            target: {
                kind: "internal",
                surface: "platform-public",
                path: "/",
            },
        },
        {
            id: "services",
            label: "Services",
            target: {
                kind: "internal",
                surface: "platform-public",
                path: "/services",
            },
        },
        {
            id: "industries",
            label: "Industries",
            target: {
                kind: "internal",
                surface: "platform-public",
                path: "/industries",
            },
        },
        {
            id: "client-login",
            label: "Client Login",
            target: {
                kind: "internal",
                surface: "platform-auth",
                path: "/login",
            },
        },
    ];
}
