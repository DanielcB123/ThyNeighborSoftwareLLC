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
        $cookieToken = $request->cookie('start_project_session');
        $resolvedToken = is_string($sessionToken) && trim($sessionToken) !== ''
            ? $sessionToken
            : (is_string($cookieToken) ? $cookieToken : null);

        $session = $prospectOnboardingService->startOrResumeSession(
            $resolvedToken,
            $request->ip(),
            $request->userAgent(),
        );

        cookie()->queue(
            cookie(
                'start_project_session',
                $session->access_token,
                60 * 24 * 30,
                null,
                null,
                false,
                true,
                false,
                'lax'
            )
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
