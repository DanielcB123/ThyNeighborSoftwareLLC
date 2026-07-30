export interface AccessContext {
    isAuthenticated: boolean;
    permissions: ReadonlySet<string>;
    modules: ReadonlySet<string>;
    capabilities: ReadonlySet<string>;
}

export interface AccessRequirement {
    requiresAuthentication?: boolean;
    allPermissions?: readonly string[];
    anyPermissions?: readonly string[];
    allModules?: readonly string[];
    anyModules?: readonly string[];
    allCapabilities?: readonly string[];
    anyCapabilities?: readonly string[];
}

function includesAll(values: ReadonlySet<string>, required: readonly string[] | undefined): boolean {
    if (!required || required.length === 0) {
        return true;
    }

    return required.every((entry) => values.has(entry));
}

function includesAny(values: ReadonlySet<string>, required: readonly string[] | undefined): boolean {
    if (!required || required.length === 0) {
        return true;
    }

    return required.some((entry) => values.has(entry));
}

export function canAccess(requirement: AccessRequirement | undefined, context: AccessContext): boolean {
    if (!requirement) {
        return true;
    }

    if (requirement.requiresAuthentication && !context.isAuthenticated) {
        return false;
    }

    return (
        includesAll(context.permissions, requirement.allPermissions) &&
        includesAny(context.permissions, requirement.anyPermissions) &&
        includesAll(context.modules, requirement.allModules) &&
        includesAny(context.modules, requirement.anyModules) &&
        includesAll(context.capabilities, requirement.allCapabilities) &&
        includesAny(context.capabilities, requirement.anyCapabilities)
    );
}

export function createAccessContext(input: {
    isAuthenticated: boolean;
    permissions?: readonly string[];
    modules?: readonly string[];
    capabilities?: readonly string[];
}): AccessContext {
    return {
        isAuthenticated: input.isAuthenticated,
        permissions: new Set(input.permissions ?? []),
        modules: new Set(input.modules ?? []),
        capabilities: new Set(input.capabilities ?? []),
    };
}
