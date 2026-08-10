<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum DiscoverySessionStatus: string
{
    case Draft = 'draft';
    case ProjectDiscoveryInProgress = 'project_discovery_in_progress';
    case MeetingRequested = 'meeting_requested';
    case Submitted = 'submitted';
    case UnderInternalReview = 'under_internal_review';
    case ClarificationRequested = 'clarification_requested';
    case ClientRevisionInProgress = 'client_revision_in_progress';
    case ApprovedForHandoff = 'approved_for_handoff';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Archived = 'archived';
}
