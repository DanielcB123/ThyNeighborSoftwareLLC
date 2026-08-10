<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum LeadStatus: string
{
    case New = 'new';
    case InReview = 'in_review';
    case Qualified = 'qualified';
    case Disqualified = 'disqualified';
    case WorkspaceActive = 'workspace_active';
    case DiscoveryComplete = 'discovery_complete';
}
