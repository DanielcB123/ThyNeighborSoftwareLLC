<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum InquirySpamDisposition: string
{
    case NotChecked = 'not_checked';
    case Legitimate = 'legitimate';
    case Spam = 'spam';
    case Blocked = 'blocked';
}
