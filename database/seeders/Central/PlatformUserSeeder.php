<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Demo\Data\DemoConfigurationData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $configuration = DemoConfigurationData::fromConfig();
        $timestamp = now()->utc();

        $users = [
            [
                'name' => 'Platform Owner',
                'email' => 'platform.owner@webuildyouthrive.test',
                'role' => 'platform-owner',
            ],
            [
                'name' => 'Platform Support',
                'email' => 'platform.support@webuildyouthrive.test',
                'role' => 'platform-support',
            ],
            [
                'name' => 'Platform Billing',
                'email' => 'platform.billing@webuildyouthrive.test',
                'role' => 'platform-billing',
            ],
        ];

        foreach ($users as $entry) {
            $user = User::query()->firstOrNew(['email' => $entry['email']]);

            if (! $user->exists && empty($user->public_id)) {
                $user->public_id = (string) Str::ulid();
            }

            $user->name = $entry['name'];
            $user->password = Hash::make($configuration->password);
            $user->email_verified_at = $timestamp;
            $user->save();

            DB::connection('central')
                ->table('platform_user_roles')
                ->upsert(
                    [[
                        'user_id' => $user->id,
                        'role_key' => $entry['role'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]],
                    ['user_id', 'role_key'],
                    ['updated_at']
                );
        }
    }
}
