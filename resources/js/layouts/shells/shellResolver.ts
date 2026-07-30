import type { RuntimeSurface } from "@/core/runtime/frontendContext";

export type ShellIdentifier =
    | "platform-public-shell"
    | "tenant-public-shell"
    | "tenant-admin-shell";

export function resolveShellBySurface(surface: RuntimeSurface): ShellIdentifier {
    if (surface === "tenant-admin") {
        return "tenant-admin-shell";
    }

    if (
        surface === "tenant-public" ||
        surface === "tenant-auth" ||
        surface === "tenant-preview"
    ) {
        return "tenant-public-shell";
    }

    return "platform-public-shell";
}
