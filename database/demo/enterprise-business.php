<?php

declare(strict_types=1);

return [
    'key' => 'enterprise',
    'weight' => 50,
    'slug' => 'atlas-field-services',
    'display_name' => 'Atlas Field Services',
    'public_id' => '01K1A4ENTERPRISETENANT0003',
    'domain' => (string) config('demo.domains.enterprise_business', 'atlasfieldservices.test'),
    'database_name' => 'wbyt_local_atlas_field_services',
    'vertical' => 'field-service-enterprise',
    'locale' => 'en',
    'timezone' => 'America/Chicago',
    'organization_units' => [
        ['key' => 'global', 'name' => 'Global HQ', 'scope' => 'company', 'parent' => null],
        ['key' => 'east', 'name' => 'East Division', 'scope' => 'division', 'parent' => 'global'],
        ['key' => 'central', 'name' => 'Central Division', 'scope' => 'division', 'parent' => 'global'],
        ['key' => 'west', 'name' => 'West Division', 'scope' => 'division', 'parent' => 'global'],
    ],
    'location_prefix' => 'AFS',
    'lead_sources' => ['website', 'enterprise-rfp', 'partner-channel', 'industry-event', 'sales-outbound'],
    'expense_vendors' => ['National Fleet Services', 'Enterprise Insurance Co', 'Industrial Supply Group', 'Cloud Operations Vendor'],
    'users' => [
        [
            'tenant' => 'enterprise',
            'name' => 'Morgan Enterprise Admin',
            'email' => 'morgan.admin@atlasfieldservices.test',
            'role' => 'enterprise-admin',
            'scope' => 'enterprise',
            'mfa_required' => true,
            'mfa_status' => 'required',
            'access_limitations' => 'Limited to demo integrations and sandbox exports.',
        ],
        [
            'tenant' => 'enterprise',
            'name' => 'Jordan Finance Lead',
            'email' => 'jordan.finance@atlasfieldservices.test',
            'role' => 'finance-lead',
            'scope' => 'enterprise',
            'mfa_required' => true,
            'mfa_status' => 'required',
            'access_limitations' => 'May not alter tax-provider secrets.',
        ],
        [
            'tenant' => 'enterprise',
            'name' => 'Taylor Operations VP',
            'email' => 'taylor.ops@atlasfieldservices.test',
            'role' => 'operations-vp',
            'scope' => 'enterprise',
            'mfa_required' => true,
            'mfa_status' => 'required',
            'access_limitations' => 'No platform-superadmin privileges.',
        ],
    ],
];
