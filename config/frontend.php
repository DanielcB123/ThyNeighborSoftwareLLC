<?php

declare(strict_types=1);

$appUrl = rtrim((string) env('APP_URL', 'http://localhost'), '/');

return [
    'platform' => [
        'public_url' => rtrim((string) env('FRONTEND_PLATFORM_PUBLIC_URL', $appUrl), '/'),
        'auth_url' => rtrim((string) env('FRONTEND_PLATFORM_AUTH_URL', $appUrl), '/'),
        'admin_url' => rtrim((string) env('FRONTEND_PLATFORM_ADMIN_URL', $appUrl.'/admin'), '/'),
    ],
    'tenant' => [
        'default_locale' => (string) env('FRONTEND_TENANT_DEFAULT_LOCALE', config('app.locale', 'en')),
        'default_timezone' => (string) env('FRONTEND_TENANT_DEFAULT_TIMEZONE', 'UTC'),
    ],
];
