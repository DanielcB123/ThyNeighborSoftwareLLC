import { canAccess } from '@/core/access/accessControl';
import type { AccessContext, AccessRequirement } from '@/core/access/accessControl';
import type { BuildUrlOptions, UrlBuilder } from '@/core/url/urlBuilder';

type InternalTargetSurface =
    | 'platform-public'
    | 'platform-auth'
    | 'platform-admin'
    | 'tenant-public'
    | 'tenant-auth'
    | 'tenant-admin'
    | 'tenant-preview';

interface InternalTarget {
    kind: 'internal';
    surface: InternalTargetSurface;
    path: string;
    options?: BuildUrlOptions;
}

interface ExternalTarget {
    kind: 'external';
    href: string;
}

export type NavigationTarget = InternalTarget | ExternalTarget;

export interface NavigationItem {
    id: string;
    label: string;
    target: NavigationTarget;
    access?: AccessRequirement;
    children?: readonly NavigationItem[];
}

export interface ResolvedNavigationItem {
    id: string;
    label: string;
    href: string;
    children: readonly ResolvedNavigationItem[];
}

function resolveTarget(target: NavigationTarget, urlBuilder: UrlBuilder): string {
    if (target.kind === 'external') {
        return urlBuilder.external(target.href);
    }

    switch (target.surface) {
        case 'platform-public':
            return urlBuilder.platformPublic(target.path, target.options);
        case 'platform-auth':
            return urlBuilder.platformAuth(target.path, target.options);
        case 'platform-admin':
            return urlBuilder.platformAdmin(target.path, target.options);
        case 'tenant-public':
            return urlBuilder.tenantPublic(target.path, target.options);
        case 'tenant-auth':
            return urlBuilder.tenantAuth(target.path, target.options);
        case 'tenant-admin':
            return urlBuilder.tenantAdmin(target.path, target.options);
        case 'tenant-preview':
            return urlBuilder.tenantPreview(target.path, target.options);
        default: {
            const neverSurface: never = target.surface;
            throw new Error(`Unsupported navigation surface: ${String(neverSurface)}`);
        }
    }
}

export function resolveNavigation(
    items: readonly NavigationItem[],
    accessContext: AccessContext,
    urlBuilder: UrlBuilder,
): readonly ResolvedNavigationItem[] {
    return items
        .filter((item) => canAccess(item.access, accessContext))
        .map((item) => ({
            id: item.id,
            label: item.label,
            href: resolveTarget(item.target, urlBuilder),
            children: resolveNavigation(item.children ?? [], accessContext, urlBuilder),
        }));
}
