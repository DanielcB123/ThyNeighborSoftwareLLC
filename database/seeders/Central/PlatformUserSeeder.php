<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Demo\Data\DemoConfigurationData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $configuration = DemoConfigurationData::fromConfig();

        User::query()->updateOrCreate(
            ['email' => 'platform.owner@webuildyouthrive.test'],
            [
                'name' => 'Platform Owner',
                'password' => Hash::make($configuration->password),
                'email_verified_at' => now()->utc(),
            ]
        );
    }
}
