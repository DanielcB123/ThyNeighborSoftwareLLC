<?php

declare(strict_types=1);

return [
    'key' => 'small',
    'weight' => 20,
    'slug' => 'coastal-comfort-plumbing',
    'display_name' => 'Coastal Comfort Plumbing',
    'public_id' => '01K1A4SMALLBIZTENANT000001',
    'domain' => (string) config('demo.domains.small_business', 'coastalcomfortplumbing.test'),
    'database_name' => 'wbyt_local_coastal_comfort_plumbing',
    'vertical' => 'home-services',
    'locale' => 'en',
    'timezone' => 'America/New_York',
    'organization_units' => [
        ['key' => 'hq', 'name' => 'Headquarters', 'scope' => 'company', 'parent' => null],
        ['key' => 'ops', 'name' => 'Operations', 'scope' => 'department', 'parent' => 'hq'],
    ],
    'location_prefix' => 'CCP',
    'lead_sources' => ['website', 'referral', 'google-local-services', 'service-van-qr'],
    'expense_vendors' => ['Fuel Depot', 'Pipe Supply Co', 'Dispatch SaaS', 'Fleet Parts Outlet'],
    'users' => [
        [
            'tenant' => 'small',
            'name' => 'Sam Owner',
            'email' => 'sam.owner@coastalcomfortplumbing.test',
            'role' => 'owner',
            'scope' => 'tenant',
            'mfa_required' => true,
            'mfa_status' => 'optional',
            'access_limitations' => 'Demo data only; no external integrations.',
        ],
        [
            'tenant' => 'small',
            'name' => 'Pat Dispatcher',
            'email' => 'pat.dispatcher@coastalcomfortplumbing.test',
            'role' => 'dispatcher',
            'scope' => 'location',
            'mfa_required' => false,
            'mfa_status' => 'disabled',
            'access_limitations' => 'Can manage jobs but cannot access billing settings.',
        ],
        [
            'tenant' => 'small',
            'name' => 'Casey Technician Lead',
            'email' => 'casey.tech@coastalcomfortplumbing.test',
            'role' => 'technician-lead',
            'scope' => 'location',
            'mfa_required' => false,
            'mfa_status' => 'disabled',
            'access_limitations' => 'Cannot access billing exports.',
        ],
    ],
];
