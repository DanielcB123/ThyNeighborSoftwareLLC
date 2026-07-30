<?php

namespace Tests\Feature\Shared;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasPublicIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_id_is_generated_for_new_users(): void
    {
        $user = User::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $user->public_id);
    }

    public function test_client_supplied_public_id_is_ignored_when_creating_new_users(): void
    {
        $providedPublicId = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

        $user = new User();
        $user->forceFill([
            'name' => 'Architecture Test User',
            'email' => 'architecture-test@example.com',
            'password' => 'password',
            'public_id' => $providedPublicId,
        ]);
        $user->save();

        $this->assertNotSame($providedPublicId, $user->public_id);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $user->public_id);
    }

    public function test_public_id_is_used_for_route_binding(): void
    {
        $this->assertSame('public_id', (new User())->getRouteKeyName());
    }
}
