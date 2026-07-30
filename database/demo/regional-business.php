<?php

declare(strict_types=1);

return [
    'key' => 'regional',
    'slug' => 'carolina-beauty-collective',
    'display_name' => 'Carolina Beauty Collective',
    'public_id' => '01K1A4REGIONALTENANT000002',
    'domain' => (string) config('demo.domains.regional_business', 'carolinabeautycollective.test'),
    'database_name' => 'wbyt_local_carolina_beauty_collective',
    'locale' => 'en',
    'timezone' => 'America/New_York',
    'users' => [
        [
            'tenant' => 'regional',
            'name' => 'Riley Regional Director',
            'email' => 'riley.director@carolinabeautycollective.test',
            'role' => 'regional-admin',
            'scope' => 'region',
            'mfa_status' => 'optional',
            'access_limitations' => 'Cannot modify platform billing.',
        ],
        [
            'tenant' => 'regional',
            'name' => 'Avery Manager',
            'email' => 'avery.manager@carolinabeautycollective.test',
            'role' => 'location-manager',
            'scope' => 'location',
            'mfa_status' => 'disabled',
            'access_limitations' => 'No enterprise export permissions.',
        ],
    ],
];
