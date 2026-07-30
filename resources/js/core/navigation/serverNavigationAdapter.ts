import type { RuntimeSurface } from "@/core/runtime/frontendContext";
import type { AccessRequirement } from "@/core/access/accessControl";
import type {
    NavigationItem,
    NavigationTarget,
} from "@/core/navigation/navigationRegistry";
import type { UrlQuery } from "@/core/url/urlBuilder";

type InternalSurface =
    | "platform-public"
    | "platform-auth"
    | "platform-admin"
    | "tenant-public"
    | "tenant-auth"
    | "tenant-admin"
    | "tenant-preview";

const RUNTIME_SURFACES: readonly RuntimeSurface[] = [
    "platform",
    "tenant-public",
    "tenant-admin",
    "tenant-auth",
    "tenant-preview",
];

const TARGET_SURFACES = new Set<InternalSurface>([
    "platform-public",
    "platform-auth",
    "platform-admin",
    "tenant-public",
    "tenant-auth",
    "tenant-admin",
    "tenant-preview",
]);

function isInternalSurface(value: string): value is InternalSurface {
    return TARGET_SURFACES.has(value as InternalSurface);
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}

function parseQueryValue(
    value: unknown,
):
    | string
    | number
    | boolean
    | readonly (string | number | boolean)[]
    | undefined {
    if (
        typeof value === "string" ||
        typeof value === "number" ||
        typeof value === "boolean"
    ) {
        return value;
    }

    if (Array.isArray(value)) {
        const values = value.filter(
            (entry): entry is string | number | boolean =>
                typeof entry === "string" ||
                typeof entry === "number" ||
                typeof entry === "boolean",
        );

        return values.length > 0 ? values : undefined;
    }

    return undefined;
}

function parseQuery(value: unknown): UrlQuery | undefined {
    if (!isRecord(value)) {
        return undefined;
    }

    const query: UrlQuery = {};

    for (const [key, queryValue] of Object.entries(value)) {
        const parsedValue = parseQueryValue(queryValue);

        if (parsedValue !== undefined) {
            query[key] = parsedValue;
        }
    }

    return Object.keys(query).length > 0 ? query : undefined;
}

function parseTarget(value: unknown): NavigationTarget | null {
    if (!isRecord(value)) {
        return null;
    }

    const kind = value.kind;

    if (kind === "external") {
        if (typeof value.href !== "string" || value.href.trim() === "") {
            return null;
        }

        return {
            kind: "external",
            href: value.href.trim(),
        };
    }

    if (kind === "internal") {
        if (
            typeof value.surface !== "string" ||
            !isInternalSurface(value.surface)
        ) {
            return null;
        }

        if (typeof value.path !== "string" || value.path.trim() === "") {
            return null;
        }

        return {
            kind: "internal",
            surface: value.surface,
            path: value.path.trim(),
            options: isRecord(value.options)
                ? {
                      query: parseQuery(value.options.query),
                      hash:
                          typeof value.options.hash === "string"
                              ? value.options.hash
                              : undefined,
                  }
                : undefined,
        };
    }

    return null;
}

function parseItems(value: unknown): readonly NavigationItem[] {
    if (!Array.isArray(value)) {
        return [];
    }

    const navigationItems: NavigationItem[] = [];

    for (const item of value) {
        if (!isRecord(item)) {
            continue;
        }

        if (
            typeof item.id !== "string" ||
            item.id.trim() === "" ||
            typeof item.label !== "string" ||
            item.label.trim() === ""
        ) {
            continue;
        }

        const target = parseTarget(item.target);

        if (!target) {
            continue;
        }

        const access = parseAccessRequirement(item.access);

        navigationItems.push({
            id: item.id.trim(),
            label: item.label.trim(),
            target,
            access,
            children: parseItems(item.children),
        });
    }

    return navigationItems;
}

function parseStringList(value: unknown): readonly string[] | undefined {
    if (!Array.isArray(value)) {
        return undefined;
    }

    const normalizedValues = value
        .filter((entry): entry is string => typeof entry === "string")
        .map((entry) => entry.trim())
        .filter((entry) => entry.length > 0);

    return normalizedValues.length > 0 ? normalizedValues : undefined;
}

function parseAccessRequirement(value: unknown): AccessRequirement | undefined {
    if (!isRecord(value)) {
        return undefined;
    }

    return {
        requiresAuthentication:
            typeof value.requiresAuthentication === "boolean"
                ? value.requiresAuthentication
                : undefined,
        allPermissions: parseStringList(value.allPermissions),
        anyPermissions: parseStringList(value.anyPermissions),
        allModules: parseStringList(value.allModules),
        anyModules: parseStringList(value.anyModules),
        allCapabilities: parseStringList(value.allCapabilities),
        anyCapabilities: parseStringList(value.anyCapabilities),
        allRoles: parseStringList(value.allRoles),
        anyRoles: parseStringList(value.anyRoles),
    };
}

export interface AdaptedNavigationContract {
    surface: RuntimeSurface;
    primary: readonly NavigationItem[];
}

export function adaptServerNavigationContract(
    value: unknown,
): AdaptedNavigationContract | null {
    if (!isRecord(value)) {
        return null;
    }

    if (
        typeof value.surface !== "string" ||
        !RUNTIME_SURFACES.includes(value.surface as RuntimeSurface)
    ) {
        return null;
    }

    return {
        surface: value.surface as RuntimeSurface,
        primary: parseItems(value.primary),
    };
}
