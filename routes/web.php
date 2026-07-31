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
                '--wb-color-primary' => '#0f6a62',
                '--wb-color-secondary' => '#8a4d2c',
                '--wb-color-bg' => '#ece9e2',
                '--wb-color-surface-muted' => '#ede9de',
                '--wb-color-border' => '#cfc7b6',
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
                'eyebrow' => 'Local service command center',
                'heading' => $tenantTagline,
                'supportingText' => 'Book urgent support, review maintenance options, and track your service relationship through a tenant-owned digital experience.',
                'primaryActionLabel' => 'Request Service',
                'primaryActionPath' => '/request-service',
                'secondaryActionLabel' => 'View Service Coverage',
                'secondaryActionPath' => '/services',
                'highlights' => [
                    'Fast scheduling for diagnostics, repairs, and installations',
                    'Clarity-first communication with transparent arrival windows',
                    'Tenant-scoped runtime and branded experience controls',
                ],
            ],
        ],
        [
            'id' => 'tenant-experience-stage',
            'type' => 'experience-stage',
            'schemaVersion' => 1,
            'data' => [
                'eyebrow' => 'Live operations signal',
                'heading' => 'The service pipeline stays visible from first call through completed work',
                'supportingText' => 'This visual stage gives customers an immediate sense of coordinated activity while keeping the interface lightweight and accessible.',
                'stageLabel' => 'Service orchestration stream',
                'metrics' => [
                    [
                        'label' => 'Response Window',
                        'value' => '< 15 minutes',
                        'detail' => 'Average first human reply for priority requests',
                    ],
                    [
                        'label' => 'Coverage Radius',
                        'value' => '42 ZIPs',
                        'detail' => 'Dispatch-ready territory for this tenant',
                    ],
                    [
                        'label' => 'Follow-ups',
                        'value' => '100%',
                        'detail' => 'Post-visit communication completion last quarter',
                    ],
                ],
            ],
        ],
        [
            'id' => 'tenant-features',
            'type' => 'feature-grid',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'What customers can do on this tenant site',
                'intro' => 'Purpose-built actions for service reliability, preventive care, and account communication.',
                'features' => [
                    [
                        'title' => 'Emergency and routine service requests',
                        'description' => 'Customers can submit urgent and planned requests with complete context.',
                        'label' => 'Requests',
                    ],
                    [
                        'title' => 'Seasonal maintenance enrollment',
                        'description' => 'Plans are presented with straightforward scope and interval details.',
                        'label' => 'Plans',
                    ],
                    [
                        'title' => 'Transparent service communication',
                        'description' => 'Response expectations and next-step timelines stay visible at every touchpoint.',
                        'label' => 'Trust',
                    ],
                    [
                        'title' => 'Secure transition into the customer portal',
                        'description' => 'Portal access remains tenant-scoped with no cross-domain ambiguity.',
                        'label' => 'Security',
                    ],
                ],
            ],
        ],
        [
            'id' => 'tenant-cta',
            'type' => 'cta-section',
            'schemaVersion' => 1,
            'data' => [
                'heading' => 'Need support immediately?',
                'description' => 'Call or submit a service ticket now and our dispatch coordinators will route your request to the right technician.',
                'actionLabel' => 'Open Customer Portal',
                'actionPath' => '/login',
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
            'pageMeta' => [
                'headTitle' => 'Tenant Site',
                'title' => 'Service support designed around your neighborhood and schedule',
                'summary' => 'This tenant website combines fast calls to action, clear service language, and a customer portal path in one coherent experience.',
            ],
            'blocks' => $demoTenantPublicBlocks($tenantContext),
        ]);
    }

    return Inertia::render('Platform/Home', [
        'pageMeta' => [
            'headTitle' => 'WeBuildYouThrive',
            'title' => 'Cinematic digital systems for teams that run real operations',
            'summary' => 'We design and build tenant-aware products where editorial clarity meets technical depth, so customers understand value in seconds.',
        ],
        'blocks' => [
            [
                'id' => 'platform-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Editorial software direction',
                    'heading' => 'Build web products that feel alive, precise, and trustworthy',
                    'supportingText' => 'Our teams shape architecture, interaction systems, and frontend implementation together so business-critical software performs under pressure without losing visual impact.',
                    'primaryActionLabel' => 'Start a technical discovery',
                    'primaryActionPath' => '/contact',
                    'secondaryActionLabel' => 'Review service capabilities',
                    'secondaryActionPath' => '/services',
                    'highlights' => [
                        'Multi-surface runtime design for platform, tenant public, and tenant admin experiences',
                        'Design systems and component contracts built for long-term product velocity',
                        'Motion and interaction language designed for clarity, not gimmicks',
                    ],
                ],
            ],
            [
                'id' => 'platform-experience-stage',
                'type' => 'experience-stage',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Interaction substrate',
                    'heading' => 'A restrained shell with high-impact technical moments',
                    'supportingText' => 'The experience stage demonstrates how we encode operational data into motion-rich visuals without sacrificing accessibility, performance, or maintainability.',
                    'stageLabel' => 'Frontend activity graph',
                    'metrics' => [
                        [
                            'label' => 'Core Surfaces',
                            'value' => '5',
                            'detail' => 'Platform + tenant runtime contexts',
                        ],
                        [
                            'label' => 'Block Contracts',
                            'value' => '5',
                            'detail' => 'Typed render contracts with schema validation',
                        ],
                        [
                            'label' => 'QA Layers',
                            'value' => '4',
                            'detail' => 'Unit, component, browser, and accessibility checks',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'platform-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'What clients bring us in to solve',
                    'intro' => 'We focus on durable engineering systems that still deliver emotional impact in the interface.',
                    'features' => [
                        [
                            'title' => 'Fragmented frontend systems',
                            'description' => 'We replace inconsistent UI stacks with a coherent runtime-aware design architecture.',
                            'label' => 'System repair',
                        ],
                        [
                            'title' => 'Overbuilt software with weak UX outcomes',
                            'description' => 'We tighten scope around interactions that move customer confidence and operational speed.',
                            'label' => 'Product clarity',
                        ],
                        [
                            'title' => 'Tenant complexity that leaks into product code',
                            'description' => 'We define surface boundaries and contracts that keep shared code clean and safe.',
                            'label' => 'Multi-tenant discipline',
                        ],
                        [
                            'title' => 'Marketing pages that fail to convert technical buyers',
                            'description' => 'We craft copy, hierarchy, and interaction patterns that communicate capability fast.',
                            'label' => 'Conversion architecture',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'platform-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'How our delivery model works',
                    'paragraphs' => [
                        'A cross-functional pod aligns brand language, information architecture, and runtime constraints before visual exploration begins. That foundation prevents expensive rework later.',
                        'Frontend systems are composed from typed blocks, themed tokens, and shell contracts so teams can ship faster across multiple surfaces without breaking consistency.',
                    ],
                ],
            ],
            [
                'id' => 'platform-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Planning a redesign or platform modernization?',
                    'description' => 'Bring us your current stack, bottlenecks, and business goals. We will map the operating architecture and frontend direction together.',
                    'actionLabel' => 'Talk with our engineering team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.home');

Route::get('/services', function () {
    return Inertia::render('Platform/Home', [
        'pageMeta' => [
            'headTitle' => 'Services',
            'title' => 'Service lines spanning product strategy, frontend systems, and delivery operations',
            'summary' => 'Each engagement is structured around measurable business constraints, not generic deliverables.',
        ],
        'blocks' => [
            [
                'id' => 'services-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Service portfolio',
                    'heading' => 'From product narrative to production code, one operating rhythm',
                    'supportingText' => 'We run collaborative engagements that combine strategy, systems design, implementation, and operational hardening.',
                    'primaryActionLabel' => 'Book a planning workshop',
                    'primaryActionPath' => '/contact',
                    'secondaryActionLabel' => 'Review supported industries',
                    'secondaryActionPath' => '/industries',
                    'highlights' => [
                        'High-trust communication loops between design and engineering',
                        'Typed frontend contracts that reduce regression risk',
                        'Performance and accessibility treated as release criteria',
                    ],
                ],
            ],
            [
                'id' => 'services-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Core engagement tracks',
                    'intro' => 'Choose one track or combine multiple tracks for complex initiatives.',
                    'features' => [
                        [
                            'title' => 'Discovery and architecture',
                            'description' => 'Technical audits, interaction strategy, and architecture decisions tied to business outcomes.',
                            'label' => 'Phase 01',
                        ],
                        [
                            'title' => 'Cinematic frontend implementation',
                            'description' => 'Design-token systems, block libraries, and motion-aware interfaces engineered for maintainability.',
                            'label' => 'Phase 02',
                        ],
                        [
                            'title' => 'Tenant and access modeling',
                            'description' => 'Surface boundaries, permissions, capability matrices, and URL runtime integrity.',
                            'label' => 'Phase 03',
                        ],
                        [
                            'title' => 'Release and QA operations',
                            'description' => 'Automated quality checks, browser verification, and regression safety for rapid iteration.',
                            'label' => 'Phase 04',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'services-experience-stage',
                'type' => 'experience-stage',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Delivery telemetry',
                    'heading' => 'A delivery engine tuned for clarity, speed, and quality',
                    'supportingText' => 'We make work visible with concrete checkpoints so stakeholders always know what changed and why it matters.',
                    'stageLabel' => 'Engagement progression',
                    'metrics' => [
                        [
                            'label' => 'Architecture Gates',
                            'value' => '6',
                            'detail' => 'Risk checkpoints before code freeze',
                        ],
                        [
                            'label' => 'Review Cadence',
                            'value' => 'Weekly',
                            'detail' => 'Structured synthesis across design and engineering',
                        ],
                        [
                            'label' => 'Quality Signals',
                            'value' => 'CI + A11y',
                            'detail' => 'Automated checks integrated into implementation',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'services-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Need a targeted roadmap for your platform?',
                    'description' => 'Share your current application stack and we will respond with an actionable engagement path, scope controls, and risk notes.',
                    'actionLabel' => 'Contact our team',
                    'actionPath' => '/contact',
                ],
            ],
        ],
    ]);
})->name('platform.services');

Route::get('/industries', function () {
    return Inertia::render('Platform/Home', [
        'pageMeta' => [
            'headTitle' => 'Industries',
            'title' => 'Industry-informed systems for organizations with operational complexity',
            'summary' => 'We translate sector-specific workflows into coherent product architecture and customer experiences.',
        ],
        'blocks' => [
            [
                'id' => 'industries-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Industry applications',
                    'heading' => 'Operational software for service teams, healthcare networks, and enterprise groups',
                    'supportingText' => 'Shared architecture remains clean while each sector receives the workflows, language, and runtime controls it actually needs.',
                    'primaryActionLabel' => 'Discuss your industry requirements',
                    'primaryActionPath' => '/contact',
                    'highlights' => [
                        'Field operations with dispatch and scheduling pressure',
                        'Regulated environments where access policy must be explicit',
                        'Multi-location organizations with mixed digital maturity',
                    ],
                ],
            ],
            [
                'id' => 'industries-feature-grid',
                'type' => 'feature-grid',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Deployment patterns we support',
                    'intro' => 'From single-location operations to distributed organizations with thousands of users.',
                    'features' => [
                        [
                            'title' => 'Field-service dispatch operations',
                            'description' => 'Scheduling, routing, and service-status visibility for high-volume teams.',
                            'label' => 'Home services',
                        ],
                        [
                            'title' => 'Multi-location administrative control',
                            'description' => 'Tenant-level segmentation with role-based module and capability control.',
                            'label' => 'Franchise + regional',
                        ],
                        [
                            'title' => 'Customer-facing portals',
                            'description' => 'Tenant-branded experiences for requests, communication, and account workflows.',
                            'label' => 'Retention',
                        ],
                        [
                            'title' => 'Dashboard-based operations',
                            'description' => 'Capability-aware widgets for finance, customer workflows, and dispatch teams.',
                            'label' => 'Operations',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'industries-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'How we adapt by domain',
                    'paragraphs' => [
                        'We begin by mapping decision latency, compliance constraints, and user context for each role. That model drives both interaction design and architecture choices.',
                        'Instead of one-off feature branches by tenant name, we use explicit capabilities and runtime contracts so each industry variant remains maintainable as the platform grows.',
                    ],
                ],
            ],
        ],
    ]);
})->name('platform.industries');

Route::get('/contact', function () {
    return Inertia::render('Platform/Home', [
        'pageMeta' => [
            'headTitle' => 'Contact',
            'title' => 'Bring the challenge. We will shape the system.',
            'summary' => 'Share your goals, constraints, and current stack. We respond with concrete next steps, not generic sales language.',
        ],
        'blocks' => [
            [
                'id' => 'contact-hero',
                'type' => 'hero',
                'schemaVersion' => 1,
                'data' => [
                    'eyebrow' => 'Contact',
                    'heading' => 'Tell us what you need to build or modernize',
                    'supportingText' => 'Share platform goals, tenant requirements, and implementation constraints. We will reply with a discovery path tailored to your context.',
                    'primaryActionLabel' => 'Email hello@webuildyouthrive.com',
                    'primaryActionPath' => 'mailto:hello@webuildyouthrive.com',
                    'highlights' => [
                        'Current stack and deployment model',
                        'Desired business outcomes and risk constraints',
                        'Team composition and release cadence',
                    ],
                ],
            ],
            [
                'id' => 'contact-rich-text',
                'type' => 'rich-text',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'What to include in your message',
                    'paragraphs' => [
                        'Describe the surfaces you need: public website, tenant admin, customer portal, or operational dashboards.',
                        'List security, compliance, and integration requirements so our implementation recommendations map to your real constraints.',
                    ],
                ],
            ],
            [
                'id' => 'contact-cta',
                'type' => 'cta-section',
                'schemaVersion' => 1,
                'data' => [
                    'heading' => 'Prefer a structured kickoff?',
                    'description' => 'Request a discovery session and we will send an intake agenda covering technical architecture, UX scope, and release risk.',
                    'actionLabel' => 'Request discovery session',
                    'actionPath' => 'mailto:hello@webuildyouthrive.com?subject=Technical%20Discovery%20Session',
                ],
            ],
        ],
    ]);
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
        'pageMeta' => [
            'headTitle' => 'Tenant Site',
            'title' => 'Service support designed around your neighborhood and schedule',
            'summary' => 'This tenant website combines fast calls to action, clear service language, and a customer portal path in one coherent experience.',
        ],
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
            'activeServiceRequests' => 6,
            'monthlyRevenueCents' => 3827400,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
