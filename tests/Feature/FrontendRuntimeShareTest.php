<?php

declare(strict_types=1);

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendRuntimeShareTest extends TestCase
{
    public function test_frontend_runtime_contract_is_shared_for_platform_requests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('frontendRuntime.surface', 'platform')
                ->where('frontendRuntime.platformUrls.publicBaseUrl', config('frontend.platform.public_url'))
                ->where('frontendRuntime.platformUrls.authBaseUrl', config('frontend.platform.auth_url'))
                ->where('frontendRuntime.platformUrls.adminBaseUrl', config('frontend.platform.admin_url'))
                ->where('frontendRuntime.tenant', null)
            );
    }
}
