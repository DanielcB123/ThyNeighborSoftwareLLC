<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingMeetingController;
use Illuminate\Http\Request;
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

/**
 * @param  array<string, mixed>|null  $tenantContext
 * @return array<int, array<string, mixed>>
 */
$demoTenantPublicBlocks = static function (?array $tenantContext = null): array {
    $displayName = trim((string) ($tenantContext['displayName'] ?? 'Your Service Team'));
    $tenantTagline = $displayName !== ''
        ? sprintf('Welcome to %s', $displayName)
        : 'Welcome to your tenant site';

    return [
        [
            'id' => 'tenant-hero',
            'type' => 'hero',
            'schemaVersion' => 1,
            'data' => [
                'eyebrow' => 'Tenant Experience Demo',
                'heading' => $tenantTagline,
                'supportingText' => 'High-conversion public websites can stay fully tenant-branded while still running on hardened shared runtime contracts.',
                'primaryActionLabel' => 'Request A Service Visit',
                'primaryActionPath' => '/request-service',
                'secondaryActionLabel' => 'View Service Programs',
                'secondaryActionPath' => '/services',
            ],
        ],
        [
            'id' => 'tenant-rich-text',
            'type' => 'rich-text',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'Built for local trust and central reliability',
                'paragraphs' => [
                    'Tenant domains are resolved through central registry records before public rendering begins, preventing cross-tenant ambiguity and preserving deterministic routing.',
                    'Shared capabilities remain centralized while each tenant keeps its own identity layer, content tone, and service conversion path.',
                ],
            ],
        ],
        [
            'id' => 'tenant-features',
            'type' => 'feature-grid',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'What this tenant surface proves',
                'intro' => 'Every card reflects production-grade contracts behind the visual experience.',
                'features' => [
                    [
                        'title' => 'Registry-backed domain resolution',
                        'description' => 'Tenant hosts are resolved using central domain metadata with explicit positive/negative caching controls.',
                        'label' => 'Domain Layer',
                    ],
                    [
                        'title' => 'Capability-aware conversion pathways',
                        'description' => 'Navigation and calls to action can be tailored by enabled modules, capabilities, and audience context.',
                        'label' => 'Navigation',
                    ],
                    [
                        'title' => 'Typed cinematic content blocks',
                        'description' => 'Public pages use typed composable blocks so creative upgrades do not break data and runtime contracts.',
                        'label' => 'Frontend',
                    ],
                    [
                        'title' => 'Operationally safe extensibility',
                        'description' => 'Tenant-specific theming and surface behavior extend safely without scattering conditional logic throughout the codebase.',
                        'label' => 'Operations',
                    ],
                ],
            ],
        ],
        [
            'id' => 'tenant-cta',
            'type' => 'cta-section',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'Need this level of quality on your own domain?',
                'description' => 'WeBuildYouThrive builds tenant-ready customer websites that convert while preserving platform consistency and security boundaries.',
                'actionLabel' => 'Open Tenant Operations Dashboard',
                'actionPath' => '/dashboard',
            ],
        ],
    ];
};

Route::get('/', function (Request $request) use ($demoTenantPublicBlocks) {
    /** @var array<string, mixed>|null $tenantContext */
    $tenantContext = $request->attributes->get('frontendTenantContext');
    $runtimeSurface = (string) $request->attributes->get('tenantRuntimeSurface', 'platform');

    if (
        is_array($tenantContext) &&
        in_array($runtimeSurface, ['tenant_public', 'tenant_auth'], true)
    ) {
        return Inertia::render('Tenant/PublicHome', [
            'blocks' => $demoTenantPublicBlocks($tenantContext),
            'page' => [
                'headTitle' => $tenantContext['displayName'].' | Tenant Website',
                'title' => 'Service websites that look premium and convert with confidence',
                'summary' => 'This tenant domain demonstrates conversion-focused storytelling, runtime safety, and multi-surface continuity from public pages into customer and operations workflows.',
            ],
        ]);
    }

    return Inertia::render('Platform/Home', [
        'page' => [
            'headTitle' => 'WeBuildYouThrive | Cinematic Frontend + Platform Engineering',
            'title' => 'We design websites and software experiences buyers remember and teams can maintain',
            'summary' => 'Editorial visual confidence, conversion discipline, and production engineering rigor in one delivery partner.',
        ],
        'blocks' => [
            [
                'id' => 'platform-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Cinematic Frontend + Product Engineering',
                    'heading' => 'Public websites, customer portals, and operational platforms designed as one cohesive system',
                    'supportingText' => 'WeBuildYouThrive combines creative-direction-level frontend execution with tenant-safe architecture and operational software delivery.',
                    'primaryActionLabel' => 'Schedule A Technical Discovery Session',
                    'primaryActionPath' => '/onboarding',
                    'secondaryActionLabel' => 'Review Service Capabilities',
                    'secondaryActionPath' => '/services',
                ],
            ],
            [
                'id' => 'platform-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Creative ambition without operational debt',
                    'paragraphs' => [
                        'Most agencies stop at visuals and most engineering firms stop at functional interfaces. We intentionally operate at both levels: brand-defining presentation and platform-level reliability.',
                        'The result is a public experience that feels premium on first impression and remains maintainable across tenant variants, auth surfaces, and admin workflows.',
                    ],
                ],
            ],
            [
                'id' => 'platform-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Capabilities clients buy us for',
                    'intro' => 'Visual impact, engineering discipline, and measurable business outcomes all shipped in the same delivery model.',
                    'features' => [
                        [
                            'title' => 'Cinematic, high-trust public frontends',
                            'description' => 'Image-first editorial layouts, selective 3D storytelling, and polished interaction systems designed to drive qualified inquiries.',
                            'label' => 'Acquisition',
                        ],
                        [
                            'title' => 'Tenant-safe platform architecture',
                            'description' => 'Runtime boundaries, URL contracts, and capability-aware interfaces that hold up as tenant count and feature depth increase.',
                            'label' => 'Architecture',
                        ],
                        [
                            'title' => 'Composable content + design systems',
                            'description' => 'Typed block rendering and semantic tokens allow rapid iteration without sacrificing consistency or frontend safety.',
                            'label' => 'Design System',
                        ],
                        [
                            'title' => 'Long-term release reliability',
                            'description' => 'Tested frontend contracts, explicit access controls, and implementation patterns that remain readable under pressure.',
                            'label' => 'Operations',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'platform-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need your website to close trust gaps before the first sales call?',
                    'description' => 'Tell us your growth target, your system constraints, and where your current frontend loses confidence. We will map the technical and creative path to a better result.',
                    'actionLabel' => 'Talk to Our Engineering + Design Team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.home');

Route::get('/services', function () {
    return Inertia::render('Platform/Home', [
        'page' => [
            'headTitle' => 'Services | WeBuildYouThrive',
            'title' => 'From cinematic public frontends to mission-critical software operations',
            'summary' => 'Our service model spans strategy, UX architecture, implementation, quality assurance, and long-term optimization.',
        ],
        'blocks' => [
            [
                'id' => 'services-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Service Portfolio',
                    'heading' => 'Creative direction, frontend craft, and platform engineering under one accountable team',
                    'supportingText' => 'We architect and build customer acquisition surfaces, tenant experiences, and software operations systems that can scale without chaotic rewrites.',
                    'primaryActionLabel' => 'Book A Technical Planning Session',
                    'primaryActionPath' => '/onboarding',
                    'secondaryActionLabel' => 'Review Supported Industries',
                    'secondaryActionPath' => '/industries',
                ],
            ],
            [
                'id' => 'services-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Engagement tracks',
                    'intro' => 'Choose a focused track or combine them into a full modernization program.',
                    'features' => [
                        [
                            'title' => 'Cinematic Website Redesign',
                            'description' => 'Positioning, copy architecture, visual systems, interaction polish, and conversion-oriented launch execution.',
                            'label' => 'Brand + Conversion',
                        ],
                        [
                            'title' => 'Tenant Frontend Expansion',
                            'description' => 'Shared design system and runtime-safe tenant differentiation across public, auth, and admin surfaces.',
                            'label' => 'Multi-Tenant',
                        ],
                        [
                            'title' => 'Operational Product Engineering',
                            'description' => 'Dispatch, CRM, billing, reporting, and role-aware internal tools aligned to real workflows.',
                            'label' => 'Internal Software',
                        ],
                        [
                            'title' => 'Quality and Delivery Hardening',
                            'description' => 'Testing strategy, accessibility, performance budgets, and release-safe frontend contracts.',
                            'label' => 'Reliability',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'services-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need a targeted execution roadmap instead of another vague proposal?',
                    'description' => 'We deliver implementation-ready architecture, interface direction, and sequencing that your team can actually execute.',
                    'actionLabel' => 'Contact Our Team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.services');

Route::get('/industries', function () {
    return Inertia::render('Platform/Home', [
        'page' => [
            'headTitle' => 'Industries | WeBuildYouThrive',
            'title' => 'Industry-specific workflows built on shared platform foundations',
            'summary' => 'From field service operations to enterprise administrative networks, we adapt systems without duplicating architecture.',
        ],
        'blocks' => [
            [
                'id' => 'industries-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Industry Applications',
                    'heading' => 'Purpose-built experiences for service businesses, healthcare organizations, and enterprise teams',
                    'supportingText' => 'We align each implementation to real operating constraints while keeping core contracts reusable and maintainable.',
                    'primaryActionLabel' => 'Discuss Your Industry Requirements',
                    'primaryActionPath' => '/contact',
                ],
            ],
            [
                'id' => 'industries-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Deployment patterns we routinely support',
                    'intro' => 'Single-location teams, regional organizations, and nationwide operations with layered permissions and tenant boundaries.',
                    'features' => [
                        [
                            'title' => 'Field-service dispatch systems',
                            'description' => 'Scheduling, routing, and live operational visibility designed for high-volume service teams.',
                            'label' => 'Home Services',
                        ],
                        [
                            'title' => 'Multi-location administrative control',
                            'description' => 'Tenant-level segmentation with role- and capability-aware access policies.',
                            'label' => 'Franchise + Regional',
                        ],
                        [
                            'title' => 'Customer-facing portals',
                            'description' => 'Tenant-branded request, messaging, account, and billing workflows that feel cohesive with the public website.',
                            'label' => 'Customer Experience',
                        ],
                        [
                            'title' => 'Operations and finance dashboards',
                            'description' => 'Capability-aware widgets and controls for dispatch, customer workflows, and revenue operations.',
                            'label' => 'Internal Tools',
                        ],
                    ],
                ],
            ],
        ],
    ]);
})->name('platform.industries');

Route::get('/contact', function () {
    return Inertia::render('Platform/Home', [
        'page' => [
            'headTitle' => 'Contact | WeBuildYouThrive',
            'title' => 'Tell us what you need to build, modernize, or stabilize',
            'summary' => 'We respond with a clear technical discovery path that connects visual ambition to implementation reality.',
        ],
        'blocks' => [
            [
                'id' => 'contact-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Contact',
                    'heading' => 'Describe the website or platform your team needs next',
                    'supportingText' => 'Share your goals, constraints, and timeline pressure. We will respond with a scoped discovery conversation and a practical implementation path.',
                    'primaryActionLabel' => 'Email hello@webuildyouthrive.com',
                    'primaryActionPath' => 'mailto:hello@webuildyouthrive.com',
                    'secondaryActionLabel' => 'Explore Service Tracks',
                    'secondaryActionPath' => '/services',
                ],
            ],
            [
                'id' => 'contact-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'What to include so we can move fast',
                    'paragraphs' => [
                        'List the surfaces you need first: public website, tenant-facing portal, auth workflows, operations dashboards, or all of the above.',
                        'Include current blockers (design quality, conversion rate, legacy code risks, delivery constraints) so we can prioritize correctly.',
                    ],
                ],
            ],
        ],
    ]);
})->name('platform.contact');

Route::get('/onboarding', [OnboardingMeetingController::class, 'start'])->name('onboarding.start');
Route::get('/onboarding/{onboardingAppointment:public_id}', [OnboardingMeetingController::class, 'show'])->name('onboarding.show');
Route::put('/onboarding/{onboardingAppointment:public_id}', [OnboardingMeetingController::class, 'schedule'])->name('onboarding.schedule');
Route::delete('/onboarding/{onboardingAppointment:public_id}', [OnboardingMeetingController::class, 'cancel'])->name('onboarding.cancel');

Route::get('/tenant-site', function () use ($demoTenantFrontendContext, $demoTenantPublicBlocks) {
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
        'blocks' => $demoTenantPublicBlocks(
            request()->attributes->get('frontendTenantContext')
        ),
        'page' => [
            'headTitle' => 'Tenant Demo | Public Surface',
            'title' => 'Tenant-branded public experience demo',
            'summary' => 'A realistic tenant website surface with conversion pathways, scoped navigation, and platform-governed runtime behavior.',
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
