<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
final class DemoTenantUserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now()->utc(),
            'password' => Hash::make((string) config('demo.password', 'DemoPassword!2026')),
        ];
    }
}
