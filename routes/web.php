<?php

use App\Http\Controllers\ProfileController;
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
                'eyebrow' => 'Customer-ready tenant frontend',
                'heading' => $tenantTagline,
                'supportingText' => 'This domain is tenant-resolved from the central registry and rendered through the tenant runtime surface.',
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
                'heading' => 'Built with tenant-safe runtime boundaries',
                'paragraphs' => [
                    'Tenant domains resolve through central registry records before frontend rendering proceeds.',
                    'Shared platform capabilities stay isolated while tenant-specific identity and navigation remain scoped.',
                ],
            ],
        ],
        [
            'id' => 'tenant-features',
            'type' => 'feature-grid',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'What this seeded tenant demonstrates',
                'intro' => 'Domain resolution, tenant runtime classification, and frontend composition contracts.',
                'features' => [
                    [
                        'title' => 'Central registry-backed domain routing',
                        'description' => 'Tenant hosts are looked up through central tenant-domain metadata.',
                        'label' => 'Tenancy',
                    ],
                    [
                        'title' => 'Capability-aware navigation',
                        'description' => 'Navigation items are filtered by tenant modules and capabilities.',
                        'label' => 'Access',
                    ],
                    [
                        'title' => 'Shared block rendering',
                        'description' => 'Tenant public pages use typed blocks while preserving runtime isolation.',
                        'label' => 'Frontend',
                    ],
                    [
                        'title' => 'Deterministic resolution cache',
                        'description' => 'Positive and negative domain resolution cache entries are explicitly tracked.',
                        'label' => 'Reliability',
                    ],
                ],
            ],
        ],
        [
            'id' => 'tenant-cta',
            'type' => 'cta-section',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'Need to validate another seeded tenant domain?',
                'description' => 'Switch to another demo tenant host and verify runtime surface, navigation, and content shell behavior.',
                'actionLabel' => 'Open Tenant Dashboard',
                'actionPath' => '/dashboard',
            ],
        ],
    ];
};

/**
 * @var array<int, array<string, string>>
 */
$platformFeaturedProjects = [
    [
        'id' => 'meridian-medical-network',
        'title' => 'Meridian Medical Network Experience Platform',
        'sector' => 'Healthcare group operations',
        'summary' => 'Unified acquisition website, patient portal handoff, and role-aware operations console for six regional locations.',
        'impact' => 'Delivered a cohesive patient journey while preserving strict compliance and location-level operational autonomy.',
        'spotlight' => 'Portal engagement climbed after replacing fragmented intake forms and disconnected scheduling paths.',
        'accentFrom' => '#7b8bff',
        'accentTo' => '#3dddcf',
    ],
    [
        'id' => 'northgate-logistics-command',
        'title' => 'Northgate Logistics Dispatch Command Surface',
        'sector' => 'Multi-location field services',
        'summary' => 'Rebuilt dispatch lifecycle with customer-facing ETA flows, technician route orchestration, and billing visibility.',
        'impact' => 'Frontline teams gained shared real-time state without giving up branch-specific controls.',
        'spotlight' => 'Reduced handoff errors by replacing spreadsheet status loops with role-scoped live dashboards.',
        'accentFrom' => '#806bff',
        'accentTo' => '#20d2ff',
    ],
    [
        'id' => 'summit-finance-analytics',
        'title' => 'Summit Finance Reporting and Revenue Intelligence Suite',
        'sector' => 'Enterprise finance operations',
        'summary' => 'Connected billing streams, payout reconciliation, and executive KPI views into a single tenant-safe reporting system.',
        'impact' => 'Leadership finally got one trusted operating view across departments and partner entities.',
        'spotlight' => 'Eliminated slow monthly reconciliation drills by automating exceptions and approval routing.',
        'accentFrom' => '#9f74ff',
        'accentTo' => '#5ae3a6',
    ],
    [
        'id' => 'atlas-home-services-relaunch',
        'title' => 'Atlas Home Services Public Brand Relaunch',
        'sector' => 'Service brand marketing',
        'summary' => 'Premium project-first website experience with conversion pathways tied directly to schedule and estimate workflows.',
        'impact' => 'Positioned the brand as premium while keeping conversion journeys explicit and measurable.',
        'spotlight' => 'Boosted qualified lead quality by aligning visual storytelling with operational service zones.',
        'accentFrom' => '#5f7eff',
        'accentTo' => '#50f0d8',
    ],
];

/**
 * @var array<int, array<string, mixed>>
 */
$platformCapabilityTracks = [
    [
        'id' => 'experience-direction',
        'label' => '01',
        'title' => 'Experience direction and visual systems',
        'details' => [
            'Editorial hierarchy, component rhythm, and resilient interaction grammar.',
            'Image-led layout systems calibrated for desktop, tablet, and mobile pacing.',
            'Design tokens and scalable implementation standards for long-term maintainability.',
        ],
    ],
    [
        'id' => 'frontend-engineering',
        'label' => '02',
        'title' => 'Frontend architecture and immersive interaction engineering',
        'details' => [
            'Typed Vue implementation with clear ownership boundaries and predictable state.',
            'Selective 3D/WebGL enhancements with reduced-motion and accessibility safeguards.',
            'Performance-aware asset and animation strategies tuned for real-world devices.',
        ],
    ],
    [
        'id' => 'backend-operations',
        'label' => '03',
        'title' => 'Backend systems, security posture, and runtime operations',
        'details' => [
            'Role and capability-aware navigation contracts driven from backend policy rules.',
            'Tenant-safe architecture patterns for multi-location or multi-brand platforms.',
            'Operational observability, release controls, and long-term support workflows.',
        ],
    ],
];

/**
 * @var array<int, array<string, mixed>>
 */
$platformSurfaces = [
    [
        'id' => 'surface-public',
        'surface' => 'Public experience',
        'title' => 'High-conviction brand websites that convert without feeling generic',
        'description' => 'Narrative-rich marketing surfaces designed to communicate craft, trust, and differentiation in the first minute.',
        'outcomes' => [
            'Project-first storytelling with clear offer architecture',
            'Structured lead paths and contact funnels tied to business goals',
            'Motion and media systems that support readability, not distraction',
        ],
    ],
    [
        'id' => 'surface-portal',
        'surface' => 'Customer and partner portals',
        'title' => 'Role-aware portals for requests, account visibility, and communication',
        'description' => 'Secure and usable interfaces that move customer workflows out of inbox chaos and into predictable systems.',
        'outcomes' => [
            'Authenticated user journeys with explicit access boundaries',
            'Tenant-branded UX layers without copy-pasted code branches',
            'Workflow continuity from public touchpoint to logged-in operations',
        ],
    ],
    [
        'id' => 'surface-operations',
        'surface' => 'Operations and reporting',
        'title' => 'Operational dashboards that leadership and frontline teams can trust',
        'description' => 'Decision-making surfaces that make status, performance, and financial signals immediately actionable.',
        'outcomes' => [
            'Capability-filtered modules for teams with different responsibilities',
            'Cross-surface consistency between dispatch, CRM, billing, and reporting',
            'Production quality controls for reliability under ongoing iteration',
        ],
    ],
];

/**
 * @var array<int, array<string, string>>
 */
$platformDeliveryPhases = [
    [
        'id' => 'phase-1',
        'phase' => 'Phase 01',
        'title' => 'Discovery and system framing',
        'details' => 'We define business outcomes, identify surface boundaries, and map conversion and operational constraints before aesthetics are finalized.',
    ],
    [
        'id' => 'phase-2',
        'phase' => 'Phase 02',
        'title' => 'Experience direction and prototyping',
        'details' => 'We establish visual language, interaction behavior, and content hierarchy through production-grade prototypes instead of static placeholders.',
    ],
    [
        'id' => 'phase-3',
        'phase' => 'Phase 03',
        'title' => 'Engineering and integration',
        'details' => 'We implement frontend and backend contracts together so immersive presentation and operational logic stay aligned from day one.',
    ],
    [
        'id' => 'phase-4',
        'phase' => 'Phase 04',
        'title' => 'Launch hardening and growth',
        'details' => 'We tune performance, analytics, accessibility, and release workflows to support confident growth after launch.',
    ],
];

/**
 * @var array<string, array<string, mixed>>
 */
$platformChapterScenarios = [
    'home' => [
        'meta' => [
            'title' => 'WeBuildYouThrive | Cinematic product and platform experiences',
            'description' => 'Project-first digital experiences engineered for growth, operations, and long-term maintainability.',
        ],
        'hero' => [
            'eyebrow' => 'Cinematic frontend and platform execution',
            'title' => 'We build digital experiences that feel unforgettable and run like serious software.',
            'summary' => 'Your public website should prove the quality of your backend systems, not hide them. We design and engineer both with one cohesive production standard.',
            'chapterLabel' => 'Current chapter',
            'chapterLead' => 'Home: immersive overview of how visual craft, frontend architecture, and operational software delivery work together.',
            'primaryAction' => [
                'label' => 'Start a discovery session',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'Explore service tracks',
                'path' => '/services',
            ],
        ],
        'closingCta' => [
            'heading' => 'If your current site feels generic, your prospects can feel it immediately.',
            'description' => 'We design and ship differentiated public experiences, secure customer portals, and operational systems under one production strategy.',
            'primaryAction' => [
                'label' => 'Book technical discovery',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'Review industry applications',
                'path' => '/industries',
            ],
        ],
    ],
    'services' => [
        'meta' => [
            'title' => 'WeBuildYouThrive Services | Creative direction, engineering, and platform delivery',
            'description' => 'Service tracks spanning experience direction, frontend engineering, and backend operations.',
        ],
        'hero' => [
            'eyebrow' => 'Service portfolio',
            'title' => 'From bold visual storytelling to enterprise runtime clarity.',
            'summary' => 'We combine creative direction and software engineering so your website, customer portal, and operational dashboards feel unified and dependable.',
            'chapterLabel' => 'Current chapter',
            'chapterLead' => 'Services: a structured view of capabilities you can engage independently or as one integrated delivery team.',
            'primaryAction' => [
                'label' => 'Request capability workshop',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'See industry patterns',
                'path' => '/industries',
            ],
        ],
        'closingCta' => [
            'heading' => 'Need a team that can handle design intensity and architecture depth?',
            'description' => 'We can lead strategy, execute implementation, and support long-term platform operations without handoff chaos.',
            'primaryAction' => [
                'label' => 'Talk with engineering leadership',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'Return to experience overview',
                'path' => '/',
            ],
        ],
    ],
    'industries' => [
        'meta' => [
            'title' => 'WeBuildYouThrive Industries | Sector-specific software and experience delivery',
            'description' => 'Applications for service businesses, healthcare groups, and enterprise multi-location operations.',
        ],
        'hero' => [
            'eyebrow' => 'Industry applications',
            'title' => 'Sector complexity translated into clear digital experiences and robust systems.',
            'summary' => 'We tailor visual identity, conversion pathways, and operations software to each market while protecting maintainability and governance.',
            'chapterLabel' => 'Current chapter',
            'chapterLead' => 'Industries: where we adapt common platform strengths to domain-specific workflows, constraints, and trust signals.',
            'primaryAction' => [
                'label' => 'Discuss your operating model',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'View service tracks',
                'path' => '/services',
            ],
        ],
        'closingCta' => [
            'heading' => 'Your market has patterns. Your digital experience should still feel singular.',
            'description' => 'We map domain constraints to intentional design and engineering decisions so differentiation does not compromise reliability.',
            'primaryAction' => [
                'label' => 'Plan an industry-focused build',
                'path' => '/contact',
            ],
            'secondaryAction' => [
                'label' => 'See full capability overview',
                'path' => '/',
            ],
        ],
    ],
    'contact' => [
        'meta' => [
            'title' => 'Contact WeBuildYouThrive | Start your next platform experience',
            'description' => 'Share your requirements for public experiences, customer portals, and internal operations software.',
        ],
        'hero' => [
            'eyebrow' => 'Let us build what others cannot',
            'title' => 'Bring us the website and system constraints that your current stack cannot satisfy.',
            'summary' => 'Tell us what needs to perform better: conversion quality, user trust, operational visibility, or release velocity. We will shape a production path that fits.',
            'chapterLabel' => 'Current chapter',
            'chapterLead' => 'Contact: practical next step for teams that need both a premium public experience and serious backend execution.',
            'primaryAction' => [
                'label' => 'Email hello@webuildyouthrive.com',
                'path' => 'mailto:hello@webuildyouthrive.com',
            ],
            'secondaryAction' => [
                'label' => 'Explore project narratives',
                'path' => '/',
            ],
        ],
        'closingCta' => [
            'heading' => 'A serious digital surface should sell the work before your sales call starts.',
            'description' => 'Send your goals and constraints. We will reply with a focused discovery path and implementation direction.',
            'primaryAction' => [
                'label' => 'Send project brief',
                'path' => 'mailto:hello@webuildyouthrive.com',
            ],
            'secondaryAction' => [
                'label' => 'Review service portfolio',
                'path' => '/services',
            ],
        ],
    ],
];

$renderPlatformExperience = static function (string $chapter) use (
    $platformChapterScenarios,
    $platformFeaturedProjects,
    $platformCapabilityTracks,
    $platformSurfaces,
    $platformDeliveryPhases
) {
    /** @var array<string, mixed> $scenario */
    $scenario = $platformChapterScenarios[$chapter] ?? $platformChapterScenarios['home'];

    return Inertia::render('Platform/CinematicExperience', [
        'chapter' => $chapter,
        'meta' => $scenario['meta'],
        'hero' => $scenario['hero'],
        'featuredProjects' => $platformFeaturedProjects,
        'capabilityTracks' => $platformCapabilityTracks,
        'platformSurfaces' => $platformSurfaces,
        'deliveryPhases' => $platformDeliveryPhases,
        'closingCta' => $scenario['closingCta'],
    ]);
};

Route::get('/', function (Request $request) use ($demoTenantPublicBlocks, $renderPlatformExperience) {
    /** @var array<string, mixed>|null $tenantContext */
    $tenantContext = $request->attributes->get('frontendTenantContext');
    $runtimeSurface = (string) $request->attributes->get('tenantRuntimeSurface', 'platform');

    if (
        is_array($tenantContext) &&
        in_array($runtimeSurface, ['tenant_public', 'tenant_auth'], true)
    ) {
        return Inertia::render('Tenant/PublicHome', [
            'blocks' => $demoTenantPublicBlocks($tenantContext),
        ]);
    }

    return $renderPlatformExperience('home');
})->name('platform.home');

Route::get('/services', function () use ($renderPlatformExperience) {
    return $renderPlatformExperience('services');
})->name('platform.services');

Route::get('/industries', function () use ($renderPlatformExperience) {
    return $renderPlatformExperience('industries');
})->name('platform.industries');

Route::get('/contact', function () use ($renderPlatformExperience) {
    return $renderPlatformExperience('contact');
})->name('platform.contact');

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
