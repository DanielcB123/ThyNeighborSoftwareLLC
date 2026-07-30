<?php

declare(strict_types=1);

return [
    'key' => 'enterprise',
    'slug' => 'atlas-field-services',
    'display_name' => 'Atlas Field Services',
    'public_id' => '01K1A4ENTERPRISETENANT0003',
    'domain' => (string) config('demo.domains.enterprise_business', 'atlasfieldservices.test'),
    'database_name' => 'wbyt_local_atlas_field_services',
    'locale' => 'en',
    'timezone' => 'America/Chicago',
    'users' => [
        [
            'tenant' => 'enterprise',
            'name' => 'Morgan Enterprise Admin',
            'email' => 'morgan.admin@atlasfieldservices.test',
            'role' => 'enterprise-admin',
            'scope' => 'enterprise',
            'mfa_status' => 'required',
            'access_limitations' => 'Limited to demo integrations and sandbox exports.',
        ],
        [
            'tenant' => 'enterprise',
            'name' => 'Jordan Finance Lead',
            'email' => 'jordan.finance@atlasfieldservices.test',
            'role' => 'finance-lead',
            'scope' => 'enterprise',
            'mfa_status' => 'required',
            'access_limitations' => 'May not alter tax-provider secrets.',
        ],
    ],
];
