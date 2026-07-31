<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Onboarding\Services\ProspectOnboardingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StartProjectController extends Controller
{
    public function __invoke(
        Request $request,
        ProspectOnboardingService $prospectOnboardingService
    ): Response {
        $sessionToken = $request->query('session');
        $session = $prospectOnboardingService->startOrResumeSession(
            is_string($sessionToken) ? $sessionToken : null,
            $request->ip(),
            $request->userAgent(),
        );

        return Inertia::render('Platform/StartProject', [
            'onboarding' => $prospectOnboardingService->sessionSnapshot($session),
            'options' => $prospectOnboardingService->onboardingOptions(),
            'page' => [
                'headTitle' => 'Start Your Project | WeBuildYouThrive',
                'title' => 'What are we helping you build?',
                'summary' => 'Give us the short version so we can prepare a focused discovery meeting around your business, goals, and constraints.',
            ],
        ]);
    }
}
