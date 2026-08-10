<?php

declare(strict_types=1);

namespace App\Enums\Onboarding;

enum LeadProjectType: string
{
    case NewWebsite = 'new-website';
    case WebsiteRedesign = 'website-redesign';
    case WebsiteBusinessFeatures = 'website-business-features';
    case CustomWebApplication = 'custom-web-application';
    case MobileApplication = 'mobile-application';
    case InternalBusinessSystem = 'internal-business-system';
    case MultiLocationEnterprisePlatform = 'multi-location-enterprise-platform';
    case NotSureYet = 'not-sure-yet';
}
