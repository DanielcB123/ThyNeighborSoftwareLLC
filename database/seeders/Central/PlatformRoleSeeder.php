<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PlatformRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissionConfig = require database_path('demo/permissions.php');
        $roles = is_array($permissionConfig['platform_roles'] ?? null)
            ? $permissionConfig['platform_roles']
            : [];

        $timestamp = now()->utc();
        $roleRecords = [];
        $permissionPivotRecords = [];

        foreach ($roles as $role) {
            if (! is_array($role)) {
                continue;
            }

            $roleKey = trim((string) ($role['key'] ?? ''));
            if ($roleKey === '') {
                continue;
            }

            $roleRecords[] = [
                'role_key' => $roleKey,
                'display_name' => (string) ($role['name'] ?? $roleKey),
                'scope' => (string) ($role['scope'] ?? 'platform'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach (($role['permissions'] ?? []) as $permissionKey) {
                $permissionPivotRecords[] = [
                    'role_key' => $roleKey,
                    'permission_key' => (string) $permissionKey,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        if ($roleRecords !== []) {
            DB::connection('central')
                ->table('platform_roles')
                ->upsert(
                    $roleRecords,
                    ['role_key'],
                    ['display_name', 'scope', 'updated_at']
                );
        }

        if ($permissionPivotRecords !== []) {
            DB::connection('central')
                ->table('platform_role_permissions')
                ->upsert(
                    $permissionPivotRecords,
                    ['role_key', 'permission_key'],
                    ['updated_at']
                );
        }
    }
}
