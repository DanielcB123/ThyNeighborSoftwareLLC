<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$demoTenantFrontendContext = static function (string $surface): array {
    return [
        'surface' => $surface,
        'publicId' => '01J2W4G8K8ABP2YYKHSDDJK3ZT',
        'slug' => 'smith-plumbing-hvac',
        'displayName' => 'Smith Plumbing & HVAC',
        'locale' => 'en',
        'timezone' => 'America/Chicago',
        'enabledModules' => ['dispatch', 'crm'],
        'enabledCapabilities' => ['billing'],
        'theme' => [
            'tokens' => [
                '--wb-color-primary' => '#0f766e',
                '--wb-color-secondary' => '#0ea5e9',
                '--wb-color-bg' => '#ecfeff',
                '--wb-color-surface-muted' => '#cffafe',
            ],
            'logoUrl' => null,
            'faviconUrl' => null,
        ],
        'urls' => [
            'primaryBaseUrl' => 'https://smithplumbing.com',
            'authBaseUrl' => 'https://smithplumbing.com',
            'adminBaseUrl' => 'https://smithplumbing.com/admin',
            'previewBaseUrl' => 'https://smithplumbing.com/preview',
        ],
    ];
};

Route::get('/', function () {
    return Inertia::render('Platform/Home', [
        'blocks' => [
            [
                'id' => 'platform-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Platform + Services',
                    'heading' => 'Delivering custom software platforms built for tenant scale',
                    'supportingText' => 'From strategy through implementation and operations, WeBuildYouThrive helps small businesses and enterprises run secure, maintainable web systems.',
                    'primaryActionLabel' => 'Request A Technical Discovery Session',
                    'primaryActionPath' => '/contact',
                    'secondaryActionLabel' => 'Review Service Capabilities',
                    'secondaryActionPath' => '/services',
                ],
            ],
            [
                'id' => 'platform-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Implementation systems built for future growth',
                    'paragraphs' => [
                        'We build frontend architectures that support shared capabilities while preserving each tenant brand and operating model.',
                        'Our approach keeps common functionality maintainable while still allowing controlled tenant-specific extensions where needed.',
                    ],
                ],
            ],
            [
                'id' => 'platform-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'What clients rely on',
                    'intro' => 'Production execution supported by architecture, quality controls, and long-term maintainability.',
                    'features' => [
                        [
                            'title' => 'Tenant-safe runtime contracts',
                            'description' => 'Shared frontend and backend contracts prevent domain leakage and cross-tenant assumptions.',
                            'label' => 'Architecture',
                        ],
                        [
                            'title' => 'Role and capability-aware UI',
                            'description' => 'Navigation and dashboard experiences are filtered through explicit access requirements.',
                            'label' => 'Security',
                        ],
                        [
                            'title' => 'Composable page blocks',
                            'description' => 'Typed content blocks allow rapid iteration without losing schema safety.',
                            'label' => 'Content',
                        ],
                        [
                            'title' => 'Theming and design tokens',
                            'description' => 'Tenant websites keep distinct brand identity while sharing the same platform core.',
                            'label' => 'Experience',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'platform-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need a system that can scale with your organization?',
                    'description' => 'We design and implement tenant-aware platforms for businesses that need reliability, security, and operational clarity.',
                    'actionLabel' => 'Talk to our engineering team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.home');

Route::get('/services', function () {
    return Inertia::render('Platform/Home', [
        'blocks' => [
            [
                'id' => 'services-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Service Portfolio',
                    'heading' => 'Engineering, frontend architecture, and enterprise operations support',
                    'supportingText' => 'Our delivery model supports platform modernization, tenant-ready frontend systems, and long-term release operations.',
                    'primaryActionLabel' => 'Book a technical planning session',
                    'primaryActionPath' => '/contact',
                    'secondaryActionLabel' => 'Review supported industries',
                    'secondaryActionPath' => '/industries',
                ],
            ],
            [
                'id' => 'services-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need a targeted roadmap for your platform?',
                    'description' => 'We provide phased implementation plans and execution support tailored to your architecture and business operations.',
                    'actionLabel' => 'Contact our team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.services');

Route::get('/industries', function () {
    return Inertia::render('Platform/Home', [
        'blocks' => [
            [
                'id' => 'industries-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Industry Applications',
                    'heading' => 'Operational software for service businesses, healthcare groups, and enterprise organizations',
                    'supportingText' => 'We adapt shared platform foundations to industry-specific workflows without tenant-name conditionals and one-off code paths.',
                    'primaryActionLabel' => 'Discuss your industry requirements',
                    'primaryActionPath' => '/contact',
                ],
            ],
            [
                'id' => 'industries-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Deployment patterns we support',
                    'intro' => 'From single-location operations to nationwide organizations with thousands of users.',
                    'features' => [
                        [
                            'title' => 'Field-service dispatch operations',
                            'description' => 'Scheduling, routing, and status visibility for service teams.',
                        ],
                        [
                            'title' => 'Multi-location administrative control',
                            'description' => 'Tenant-level segmentation with role-based module access.',
                        ],
                        [
                            'title' => 'Customer-facing portals',
                            'description' => 'Tenant-branded experiences for requests, communication, and account management.',
                        ],
                        [
                            'title' => 'Dashboard-based operations',
                            'description' => 'Capability-aware widgets for finance, customer workflows, and dispatch teams.',
                        ],
                    ],
                ],
            ],
        ],
    ]);
})->name('platform.industries');

Route::get('/contact', function () {
    return Inertia::render('Platform/Home', [
        'blocks' => [
            [
                'id' => 'contact-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Contact',
                    'heading' => 'Tell us what you need to build or modernize',
                    'supportingText' => 'Share your platform goals, tenant needs, and constraints. Our team will respond with a technical discovery path.',
                    'primaryActionLabel' => 'Email hello@webuildyouthrive.com',
                    'primaryActionPath' => 'mailto:hello@webuildyouthrive.com',
                ],
            ],
            [
                'id' => 'contact-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'What to include in your message',
                    'paragraphs' => [
                        'Describe the application surfaces you need: public website, tenant admin, portal, or operational dashboards.',
                        'List security, compliance, and integration requirements so we can shape the implementation plan correctly.',
                    ],
                ],
            ],
        ],
    ]);
})->name('platform.contact');

Route::get('/tenant-site', function () use ($demoTenantFrontendContext) {
    request()->attributes->set(
        'frontendTenantContext',
        $demoTenantFrontendContext('tenant-public')
    );
    request()->attributes->set('frontendNavigationConfig', [
        'tenant-public' => [
            [
                'id' => 'home',
                'label' => 'Home',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-public',
                    'path' => '/',
                ],
            ],
            [
                'id' => 'services',
                'label' => 'Services',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-public',
                    'path' => '/services',
                ],
            ],
            [
                'id' => 'maintenance-plan',
                'label' => 'Maintenance Plans',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-public',
                    'path' => '/maintenance-plan',
                ],
                'access' => [
                    'anyModules' => ['crm'],
                ],
            ],
            [
                'id' => 'financing',
                'label' => 'Financing',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-public',
                    'path' => '/financing',
                ],
                'access' => [
                    'anyCapabilities' => ['financing'],
                ],
            ],
            [
                'id' => 'customer-portal',
                'label' => 'Customer Portal',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-auth',
                    'path' => '/login',
                ],
            ],
        ],
    ]);

    return Inertia::render('Tenant/PublicHome', [
        'blocks' => [
            [
                'id' => 'tenant-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Serving Houston and surrounding communities',
                    'heading' => 'Fast-response plumbing and HVAC service from certified technicians',
                    'supportingText' => 'Book installations, repairs, and emergency service appointments with transparent communication from dispatch to completion.',
                    'primaryActionLabel' => 'Request Service',
                    'primaryActionPath' => '/request-service',
                    'secondaryActionLabel' => 'Explore Services',
                    'secondaryActionPath' => '/services',
                ],
            ],
            [
                'id' => 'tenant-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Built for homeowners, property managers, and facilities teams',
                    'paragraphs' => [
                        'Our technicians are background-checked, insured, and equipped to resolve both routine maintenance and urgent service issues.',
                        'Every service request includes digital updates so customers can track scheduling, arrival windows, and completed work.',
                    ],
                ],
            ],
            [
                'id' => 'tenant-features',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Why customers choose us',
                    'intro' => 'Operational consistency backed by digital workflows and experienced field teams.',
                    'features' => [
                        [
                            'title' => '24/7 emergency coverage',
                            'description' => 'After-hours dispatch prioritization for active leaks, outages, and safety incidents.',
                            'label' => 'Emergency',
                        ],
                        [
                            'title' => 'Preventive maintenance programs',
                            'description' => 'Recurring inspections that reduce breakdowns and extend equipment lifespan.',
                            'label' => 'Maintenance',
                        ],
                        [
                            'title' => 'Transparent estimates',
                            'description' => 'Clear scope, pricing, and approval steps before on-site work begins.',
                            'label' => 'Trust',
                        ],
                        [
                            'title' => 'Commercial service coordination',
                            'description' => 'Support for property portfolios, warehouses, and multi-location operations.',
                            'label' => 'Commercial',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'tenant-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need service today?',
                    'description' => 'Submit a request in under two minutes and receive a scheduling confirmation from our dispatch team.',
                    'actionLabel' => 'Start a service request',
                    'actionPath' => '/request-service',
                ],
            ],
        ],
    ]);
})->name('tenant.public.home');

Route::get('/dashboard', function () use ($demoTenantFrontendContext) {
    request()->attributes->set(
        'frontendTenantContext',
        $demoTenantFrontendContext('tenant-admin')
    );
    request()->attributes->set('frontendAccessContext', [
        'permissions' => ['dispatch.view', 'customers.view', 'billing.view'],
        'modules' => ['dispatch', 'crm'],
        'capabilities' => ['billing'],
        'roles' => ['owner'],
    ]);
    request()->attributes->set('frontendNavigationConfig', [
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
                'id' => 'customers',
                'label' => 'Customers',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-admin',
                    'path' => '/customers',
                ],
                'access' => [
                    'requiresAuthentication' => true,
                    'allPermissions' => ['customers.view'],
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
                    'allPermissions' => ['dispatch.view'],
                    'allModules' => ['dispatch'],
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
                    'allPermissions' => ['billing.view'],
                    'allCapabilities' => ['billing'],
                    'anyRoles' => ['owner', 'finance-manager'],
                ],
            ],
            [
                'id' => 'security-center',
                'label' => 'Security Center',
                'target' => [
                    'kind' => 'internal',
                    'surface' => 'tenant-admin',
                    'path' => '/security',
                ],
                'access' => [
                    'requiresAuthentication' => true,
                    'allGates' => ['tenant.security.view'],
                ],
            ],
        ],
    ]);

    return Inertia::render('Tenant/AdminDashboard', [
        'dashboardSnapshot' => [
            'pendingDispatches' => 14,
            'activeServiceRequests' => 0,
            'monthlyRevenueCents' => null,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
