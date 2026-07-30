import { canAccess, type AccessContext } from "@/core/access/accessControl";
import type { AccessRequirement } from "@/core/access/accessControl";

export interface DashboardSnapshot {
    pendingDispatches: number | null;
    activeServiceRequests: number | null;
    monthlyRevenueCents: number | null;
}

export type DashboardWidgetState = "loading" | "empty" | "data";

export interface DashboardWidget {
    id: string;
    title: string;
    helperText: string;
    state: DashboardWidgetState;
    valueLabel: string;
    access?: AccessRequirement;
}

interface DashboardWidgetDefinition {
    id: string;
    title: string;
    helperText: string;
    access?: AccessRequirement;
    resolveValueLabel: (snapshot: DashboardSnapshot) => string;
    resolveState: (snapshot: DashboardSnapshot) => DashboardWidgetState;
}

function resolveCountState(count: number | null): DashboardWidgetState {
    if (count === null) {
        return "loading";
    }

    if (count === 0) {
        return "empty";
    }

    return "data";
}

function formatCurrency(cents: number): string {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(cents / 100);
}

const widgetDefinitions: readonly DashboardWidgetDefinition[] = [
    {
        id: "dispatch-queue",
        title: "Dispatch Queue",
        helperText: "Open field jobs waiting for technician assignment.",
        access: {
            requiresAuthentication: true,
            allModules: ["dispatch"],
            allPermissions: ["dispatch.view"],
        },
        resolveValueLabel: (snapshot) => {
            if (snapshot.pendingDispatches === null) {
                return "Syncing queue...";
            }

            return `${snapshot.pendingDispatches} jobs pending`;
        },
        resolveState: (snapshot) => resolveCountState(snapshot.pendingDispatches),
    },
    {
        id: "service-requests",
        title: "Service Requests",
        helperText: "Customer requests submitted in the current operating window.",
        access: {
            requiresAuthentication: true,
            allModules: ["crm"],
            allPermissions: ["customers.view"],
        },
        resolveValueLabel: (snapshot) => {
            if (snapshot.activeServiceRequests === null) {
                return "Collecting request metrics...";
            }

            return `${snapshot.activeServiceRequests} active requests`;
        },
        resolveState: (snapshot) => resolveCountState(snapshot.activeServiceRequests),
    },
    {
        id: "monthly-revenue",
        title: "Monthly Revenue",
        helperText: "Recognized tenant revenue for the current month.",
        access: {
            requiresAuthentication: true,
            allCapabilities: ["billing"],
            allPermissions: ["billing.view"],
        },
        resolveValueLabel: (snapshot) => {
            if (snapshot.monthlyRevenueCents === null) {
                return "Awaiting finance sync...";
            }

            return formatCurrency(snapshot.monthlyRevenueCents);
        },
        resolveState: (snapshot) =>
            snapshot.monthlyRevenueCents === null
                ? "loading"
                : snapshot.monthlyRevenueCents === 0
                  ? "empty"
                  : "data",
    },
];

export function resolveDashboardWidgets(
    snapshot: DashboardSnapshot,
    accessContext: AccessContext,
): readonly DashboardWidget[] {
    return widgetDefinitions
        .filter((widgetDefinition) =>
            canAccess(widgetDefinition.access, accessContext),
        )
        .map((widgetDefinition) => ({
            id: widgetDefinition.id,
            title: widgetDefinition.title,
            helperText: widgetDefinition.helperText,
            access: widgetDefinition.access,
            state: widgetDefinition.resolveState(snapshot),
            valueLabel: widgetDefinition.resolveValueLabel(snapshot),
        }));
}
