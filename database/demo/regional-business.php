<?php

declare(strict_types=1);

return [
    'key' => 'regional',
    'weight' => 30,
    'slug' => 'carolina-beauty-collective',
    'display_name' => 'Carolina Beauty Collective',
    'public_id' => '01K1A4REGIONALTENANT000002',
    'domain' => (string) config('demo.domains.regional_business', 'carolinabeautycollective.test'),
    'database_name' => 'wbyt_local_carolina_beauty_collective',
    'vertical' => 'beauty-and-wellness',
    'locale' => 'en',
    'timezone' => 'America/New_York',
    'organization_units' => [
        ['key' => 'corp', 'name' => 'Corporate', 'scope' => 'company', 'parent' => null],
        ['key' => 'north', 'name' => 'North Region', 'scope' => 'region', 'parent' => 'corp'],
        ['key' => 'south', 'name' => 'South Region', 'scope' => 'region', 'parent' => 'corp'],
    ],
    'location_prefix' => 'CBC',
    'lead_sources' => ['website', 'instagram', 'facebook', 'referral', 'walk-in'],
    'expense_vendors' => ['Salon Supply Plus', 'Regional Payroll', 'Creative Ads Agency', 'Premium Color Labs'],
    'users' => [
        [
            'tenant' => 'regional',
            'name' => 'Riley Regional Director',
            'email' => 'riley.director@carolinabeautycollective.test',
            'role' => 'regional-admin',
            'scope' => 'region',
            'mfa_required' => true,
            'mfa_status' => 'optional',
            'access_limitations' => 'Cannot modify platform billing.',
        ],
        [
            'tenant' => 'regional',
            'name' => 'Avery Manager',
            'email' => 'avery.manager@carolinabeautycollective.test',
            'role' => 'location-manager',
            'scope' => 'location',
            'mfa_required' => false,
            'mfa_status' => 'disabled',
            'access_limitations' => 'No enterprise export permissions.',
        ],
        [
            'tenant' => 'regional',
            'name' => 'Skyler Marketing Lead',
            'email' => 'skyler.marketing@carolinabeautycollective.test',
            'role' => 'marketing-manager',
            'scope' => 'region',
            'mfa_required' => false,
            'mfa_status' => 'optional',
            'access_limitations' => 'No payroll access.',
        ],
    ],
];
