<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendNavigationShareTest extends TestCase
{
    public function test_tenant_public_navigation_reflects_tenant_module_and_capability_variations(): void
    {
        $this->get('/tenant-site')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.surface', 'tenant-public')
                ->has('navigation.primary', 4)
                ->where('navigation.primary.0.id', 'home')
                ->where('navigation.primary.1.id', 'services')
                ->where('navigation.primary.2.id', 'maintenance-plan')
                ->where('navigation.primary.3.id', 'customer-portal')
            );
    }

    public function test_dashboard_navigation_is_filtered_by_server_side_gate_checks(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.surface', 'tenant-admin')
                ->has('navigation.primary', 4)
                ->where('navigation.primary.0.id', 'overview')
                ->where('navigation.primary.1.id', 'customers')
                ->where('navigation.primary.2.id', 'dispatch')
                ->where('navigation.primary.3.id', 'billing')
            );
    }

    public function test_server_driven_navigation_contract_respects_roles_permissions_and_gates(): void
    {
        Gate::define('tenant.reports.view', fn (User $user) => $user->email === 'reports@example.com');

        $routePath = '/_test/navigation-contract';

        Route::middleware('web')->get($routePath, function (Request $request) {
            $request->attributes->set('frontendTenantContext', [
                'surface' => 'tenant-admin',
                'publicId' => '01J2W4G8K8ABP2YYKHSDDJK3ZT',
                'slug' => 'dynamic-contract-test',
                'displayName' => 'Dynamic Contract Test Tenant',
                'locale' => 'en',
                'timezone' => 'UTC',
                'enabledModules' => ['crm'],
                'enabledCapabilities' => ['billing'],
                'urls' => [
                    'primaryBaseUrl' => 'https://dynamic-contract.test',
                    'authBaseUrl' => 'https://dynamic-contract.test',
                    'adminBaseUrl' => 'https://dynamic-contract.test/admin',
                    'previewBaseUrl' => 'https://dynamic-contract.test/preview',
                ],
            ]);

            $request->attributes->set('frontendAccessContext', [
                'permissions' => ['billing.view'],
                'modules' => ['crm'],
                'capabilities' => ['billing'],
                'roles' => ['finance-manager'],
            ]);

            $request->attributes->set('frontendNavigationConfig', [
                'tenant-admin' => [
                    [
                        'id' => 'overview',
                        'label' => 'Overview',
                        'target' => [
                            'kind' => 'internal',
                            'surface' => 'tenant-admin',
                            'path' => '/dashboard',
                        ],
                        'access' => [
                            'requiresAuthentication' => true,
                        ],
                    ],
                    [
                        'id' => 'dispatch',
                        'label' => 'Dispatch',
                        'target' => [
                            'kind' => 'internal',
                            'surface' => 'tenant-admin',
                            'path' => '/dispatch',
                        ],
                        'access' => [
                            'requiresAuthentication' => true,
                            'allModules' => ['dispatch'],
                            'allPermissions' => ['dispatch.view'],
                        ],
                    ],
                    [
                        'id' => 'billing',
                        'label' => 'Billing',
                        'target' => [
                            'kind' => 'internal',
                            'surface' => 'tenant-admin',
                            'path' => '/billing',
                        ],
                        'access' => [
                            'requiresAuthentication' => true,
                            'allCapabilities' => ['billing'],
                            'allPermissions' => ['billing.view'],
                            'anyRoles' => ['finance-manager'],
                        ],
                    ],
                    [
                        'id' => 'reports',
                        'label' => 'Reports',
                        'target' => [
                            'kind' => 'internal',
                            'surface' => 'tenant-admin',
                            'path' => '/reports',
                        ],
                        'access' => [
                            'requiresAuthentication' => true,
                            'allGates' => ['tenant.reports.view'],
                        ],
                    ],
                ],
            ]);

            return Inertia::render('Tenant/AdminDashboard', [
                'dashboardSnapshot' => [
                    'pendingDispatches' => 1,
                    'activeServiceRequests' => 1,
                    'monthlyRevenueCents' => 1000,
                ],
            ]);
        });

        $allowedUser = User::factory()->create([
            'email' => 'reports@example.com',
        ]);

        $blockedUser = User::factory()->create([
            'email' => 'blocked@example.com',
        ]);

        $this->actingAs($allowedUser)
            ->get($routePath)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.surface', 'tenant-admin')
                ->has('navigation.primary', 3)
                ->where('navigation.primary.0.id', 'overview')
                ->where('navigation.primary.1.id', 'billing')
                ->where('navigation.primary.2.id', 'reports')
            );

        $this->actingAs($blockedUser)
            ->get($routePath)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.surface', 'tenant-admin')
                ->has('navigation.primary', 2)
                ->where('navigation.primary.0.id', 'overview')
                ->where('navigation.primary.1.id', 'billing')
            );
    }
}
