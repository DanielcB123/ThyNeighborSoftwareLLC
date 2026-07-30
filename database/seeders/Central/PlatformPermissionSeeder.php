<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PlatformPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = require database_path('demo/permissions.php');
        $records = [];
        $timestamp = now()->utc();

        foreach (($permissions['platform_permissions'] ?? []) as $permission) {
            if (! is_array($permission)) {
                continue;
            }

            $key = trim((string) ($permission['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $records[] = [
                'permission_key' => $key,
                'display_name' => (string) ($permission['name'] ?? $key),
                'description' => (string) ($permission['description'] ?? ''),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($records === []) {
            return;
        }

        DB::connection('central')
            ->table('platform_permissions')
            ->upsert(
                $records,
                ['permission_key'],
                ['display_name', 'description', 'updated_at']
            );
    }
}
