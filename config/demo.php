<?php

declare(strict_types=1);

use App\Demo\Enums\DemoDatasetVersion;

return [
    'enabled' => env('DEMO_SEEDING_ENABLED', false),

    'password' => env(
        'DEMO_USER_PASSWORD',
        'DemoPassword!2026'
    ),

    'reference_date' => env(
        'DEMO_DATA_REFERENCE_DATE',
        now()->startOfMonth()->toDateString()
    ),

    'dataset_version' => DemoDatasetVersion::CURRENT,

    'profile' => env('DEMO_DATA_PROFILE', 'standard'),

    'allowed_environments' => [
        'local',
        'testing',
    ],

    'allowed_database_patterns' => [
        '/^webuildyouthrive_central$/',
        '/^wbyt_local_[a-z0-9_]+$/',
        '/^wbyt_test_[a-z0-9_]+$/',
    ],

    'domains' => [
        'platform' => 'webuildyouthrive.test',
        'small_business' => 'coastalcomfortplumbing.test',
        'regional_business' => 'carolinabeautycollective.test',
        'enterprise_business' => 'atlasfieldservices.test',
    ],
];
