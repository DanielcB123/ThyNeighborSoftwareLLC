<?php

declare(strict_types=1);

return [
    'key' => 'small',
    'slug' => 'coastal-comfort-plumbing',
    'display_name' => 'Coastal Comfort Plumbing',
    'public_id' => '01K1A4SMALLBIZTENANT000001',
    'domain' => (string) config('demo.domains.small_business', 'coastalcomfortplumbing.test'),
    'database_name' => 'wbyt_local_coastal_comfort_plumbing',
    'locale' => 'en',
    'timezone' => 'America/New_York',
    'users' => [
        [
            'tenant' => 'small',
            'name' => 'Sam Owner',
            'email' => 'sam.owner@coastalcomfortplumbing.test',
            'role' => 'owner',
            'scope' => 'tenant',
            'mfa_status' => 'optional',
            'access_limitations' => 'Demo data only; no external integrations.',
        ],
        [
            'tenant' => 'small',
            'name' => 'Pat Dispatcher',
            'email' => 'pat.dispatcher@coastalcomfortplumbing.test',
            'role' => 'dispatcher',
            'scope' => 'location',
            'mfa_status' => 'disabled',
            'access_limitations' => 'Can manage jobs but cannot access billing settings.',
        ],
    ],
];
