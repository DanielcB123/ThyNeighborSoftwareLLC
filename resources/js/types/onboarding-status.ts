export const ONBOARDING_SESSION_STATUSES = {
    DRAFT: "draft",
    PROJECT_DISCOVERY_IN_PROGRESS: "project_discovery_in_progress",
    MEETING_REQUESTED: "meeting_requested",
    SUBMITTED: "submitted",
    UNDER_INTERNAL_REVIEW: "under_internal_review",
    CLARIFICATION_REQUESTED: "clarification_requested",
    CLIENT_REVISION_IN_PROGRESS: "client_revision_in_progress",
    APPROVED_FOR_HANDOFF: "approved_for_handoff",
    REJECTED: "rejected",
    WITHDRAWN: "withdrawn",
    ARCHIVED: "archived",
} as const;

export type OnboardingSessionStatus =
    (typeof ONBOARDING_SESSION_STATUSES)[keyof typeof ONBOARDING_SESSION_STATUSES];

export function isClientEditableOnboardingStatus(
    status: OnboardingSessionStatus | string,
): boolean {
    return (
        status === ONBOARDING_SESSION_STATUSES.DRAFT ||
        status === ONBOARDING_SESSION_STATUSES.PROJECT_DISCOVERY_IN_PROGRESS ||
        status === ONBOARDING_SESSION_STATUSES.CLARIFICATION_REQUESTED ||
        status === ONBOARDING_SESSION_STATUSES.CLIENT_REVISION_IN_PROGRESS
    );
}
