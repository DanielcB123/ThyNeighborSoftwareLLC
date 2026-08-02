<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum InquirySubmissionStatus: string
{
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case ConvertedToLead = 'converted_to_lead';
    case Archived = 'archived';
}
